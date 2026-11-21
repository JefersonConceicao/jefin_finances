<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use DB;
use Carbon\Carbon;
use Illuminate\Support\Arr;
use App\Models\Lancamento;

class Despesa extends Model
{
    protected $table = 'despesas';
    protected $fillable = [
        'user_id',
        'nome_despesa',
        'despesa_tipo_id',
        'despesa_fixa_id',
        'valor_total',
        'data_vencimento',
        'ativo',
        'pago',
        'created_at',
        'updated_at'
    ];

    public function despesaTipo(){
        return $this->hasOne(TiposDespesa::class, 'id', 'despesa_tipo_id');
    }

    const ESCOPO_SO_ESTA = 'so_esta';
    const ESCOPO_SEGUINTES = 'seguintes';

    /**
     * Mês em que a Despesa conta: o do Vencimento; registros antigos sem Vencimento usam a data de cadastro (ADR-0002).
     */
    public function competencia(){
        return Carbon::parse($this->data_vencimento ?: $this->created_at)->startOfMonth();
    }

    public function despesaFixa(){
        return $this->belongsTo(DespesaFixa::class, 'despesa_fixa_id', 'id');
    }

    /**
     * Busca a Despesa só entre as do usuário: a de outro usuário é tratada como inexistente (404).
     */
    public function doUsuario($id, $user){
        return $this->where('user_id', $user->id)->findOrFail($id);
    }

    /**
     * A Despesa está paga se tem o pago marcado ou se já existe Lançamento do usuário para ela
     * (dados antigos podem ter Lançamento com pago = 0).
     */
    public static function estaPaga($id, $user){
        return static::where('id', $id)->where('user_id', $user->id)->where('pago', 1)->exists()
            || Lancamento::where('despesa_id', $id)->where('user_id', $user->id)->exists();
    }

    public function paga(){
        return $this->pago == 1
            || Lancamento::where('despesa_id', $this->id)->where('user_id', $this->user_id)->exists();
    }

    const MSG_SEGUINTES_NO_PASSADO = '"Esta e as seguintes" vale só a partir do mês atual';

    //"ESTA E AS SEGUINTES" (REAJUSTE OU ENCERRAMENTO) SÓ VALE NO MÊS ATUAL OU NUM MÊS FUTURO
    private function seguintesNoPassado(){
        return $this->competencia()->lt(Carbon::now()->startOfMonth());
    }

    public function getDespesas($request = [], $user){
        $conditions = [];
        $conditions[] = ['user_id', '=', $user->id];

        if(isset($request['nome_despesa']) && !empty($request['nome_despesa'])){
            $conditions[] = ['nome_despesa', 'LIKE', "%".$request['nome_despesa']."%"];
        }

        if(isset($request['despesa_tipo_id']) && !empty($request['despesa_tipo_id'])){
            $conditions[] = ['despesa_tipo_id', '=', $request['despesa_tipo_id']];
        }

        $query = $this
            ->where($conditions)
            ->with('despesaTipo');

        //COMPETÊNCIA: MÊS DO VENCIMENTO; REGISTROS ANTIGOS SEM VENCIMENTO USAM A DATA DE CADASTRO (ADR-0002)
        $competencia = DB::raw('COALESCE(data_vencimento, created_at)');

        if(empty($request['mes']) && !empty($request['ano'])){
            return $query
                ->whereYear($competencia, $request['ano'])
                ->orderBy('valor_total', 'DESC');
        }

        if(empty($request['ano']) && !empty($request['mes'])){
            return $query
                ->whereMonth($competencia, $request['mes'])
                ->orderBy('valor_total', 'DESC');
        }

        if(!empty($request['mes']) && !empty($request['ano'])){
            return $query
                ->whereMonth($competencia, $request['mes'])
                ->whereYear($competencia, $request['ano'])
                ->orderBy('valor_total', 'DESC');
        }

        return $query->orderBy('valor_total', 'DESC');
    }


    public function saveDespesa($request = [], $user){
        try{
            //A DESPESA NASCE A PAGAR: O pago SÓ MUDA PELO PAGAMENTO, QUE CRIA O LANÇAMENTO JUNTO
            $request = Arr::except($request, ['pago']);
            $request['user_id'] = $user->id;

            if(isset($request['valor_total']) && !empty($request['valor_total'])){
                $request['valor_total'] = setToDecimal($request['valor_total']);
            }

            //COMPETÊNCIA É O MÊS DO VENCIMENTO (ADR-0002); SEM DATA INFORMADA, VENCE HOJE
            $request['data_vencimento'] = !empty($request['data_vencimento'])
                ? Carbon::createFromFormat('d/m/Y', $request['data_vencimento'])->format('Y-m-d')
                : Carbon::today()->format('Y-m-d');
            unset($request['created_at']);

            $this->fill($request)->save();
            return [
                'error' => false,
                'msg' => 'Despesa adicionada!'
            ];
        }catch(\Exception $error){
            \Log::error($error->getMessage(), ['exception' => $error]);
            return [
                'error' => true,
                'msg' => 'Não foi possível salvar o registro, tente de novo'
            ];
        }
    }

    public function updateDespesa($id, array $request, $user){
        //FORA DO try: DESPESA DE OUTRO USUÁRIO VIRA 404, E NÃO UM ERRO GENÉRICO
        $despesa = $this->doUsuario($id, $user);

        $escopo = $request['escopo'] ?? self::ESCOPO_SO_ESTA;

        //SÓ ESTES CAMPOS MUDAM PELA EDIÇÃO: DONO, DATA DE CADASTRO, PAGAMENTO E DESPESA FIXA DE ORIGEM FICAM COMO ESTÃO
        $request = Arr::only($request, ['nome_despesa', 'despesa_tipo_id', 'valor_total', 'ativo', 'data_vencimento']);

        try{
            if(isset($request['valor_total']) && !empty($request['valor_total'])){
                $request['valor_total'] = setToDecimal($request['valor_total']);
            }

            //A DATA DE CADASTRO NÃO MUDA; ALTERAR O VENCIMENTO MUDA A COMPETÊNCIA
            $vencimento = Carbon::createFromFormat('!d/m/Y', $request['data_vencimento']);
            $request['data_vencimento'] = $vencimento->format('Y-m-d');

            if($escopo === self::ESCOPO_SEGUINTES && $despesa->despesaFixa && $despesa->seguintesNoPassado()){
                return ['error' => true, 'msg' => self::MSG_SEGUINTES_NO_PASSADO];
            }

            if($escopo === self::ESCOPO_SEGUINTES && $despesa->despesaFixa){
                //O REAJUSTE MUDA O DIA DE VENCIMENTO, NÃO O MÊS: MOVER A OCORRÊNCIA DE MÊS É "SÓ ESTA"
                if($vencimento->format('Y-m') !== $despesa->competencia()->format('Y-m')){
                    return ['error' => true, 'msg' => 'Para mudar o mês do Vencimento, use "Só esta"'];
                }

                DB::transaction(function() use ($despesa, $request, $vencimento){
                    //"ESTA E AS SEGUINTES" É O REAJUSTE DA DESPESA FIXA, A PARTIR DO MÊS DESTA OCORRÊNCIA
                    $despesa->despesaFixa->reajustar($despesa->competencia(), $request['valor_total'], $vencimento->day);

                    //O QUE NÃO É DO REAJUSTE (NOME, TIPO, ATIVO) VALE SÓ PARA ESTA OCORRÊNCIA
                    $despesa->refresh()->fill(Arr::only($request, ['nome_despesa', 'despesa_tipo_id', 'ativo']))->save();
                });
            }else{
                $despesa->fill($request)->save();
            }

            return [
                'error' => false,
                'msg' => 'Despesa alterada com sucesso!'
            ];
        }catch(\Exception $error){
            \Log::error($error->getMessage(), ['exception' => $error]);
            return [
                'error' => true,
                'msg' => 'Não foi possível alterar o registro, tente de novo.'
            ];
        }
    }

    /**
     * Exclui a Despesa e os Lançamentos do dono dela. Chamar dentro de uma transação.
     */
    public function excluirComLancamentos(){
        //TRAVA A DESPESA: UM PAGAMENTO AO MESMO TEMPO NÃO LANÇA DEPOIS DA LIMPEZA ABAIXO
        static::lockForUpdate()->findOrFail($this->id);
        //A DESPESA PAGA LEVA JUNTO OS LANÇAMENTOS DO DONO
        Lancamento::where('despesa_id', $this->id)->where('user_id', $this->user_id)->delete();
        //DADO ANTIGO: LANÇAMENTO DE OUTRO USUÁRIO NESTA DESPESA É DELE; SÓ SE DESLIGA DELA E VIRA "OUTRO LANÇAMENTO"
        Lancamento::where('despesa_id', $this->id)->update(['despesa_id' => null]);
        $this->delete();
    }

    public function deleteDespesa($id, $user, $escopo = self::ESCOPO_SO_ESTA){
        //FORA DO try: DESPESA DE OUTRO USUÁRIO VIRA 404, E NÃO UM ERRO GENÉRICO
        $despesa = $this->doUsuario($id, $user);

        if($escopo === self::ESCOPO_SEGUINTES && $despesa->despesaFixa && $despesa->seguintesNoPassado()){
            return ['error' => true, 'msg' => self::MSG_SEGUINTES_NO_PASSADO];
        }

        try{
            //OU SAI TUDO OU NADA
            DB::transaction(function() use ($despesa, $escopo){
                if($escopo === self::ESCOPO_SEGUINTES && $despesa->despesaFixa){
                    //"ESTA E AS SEGUINTES" É O ENCERRAMENTO DA DESPESA FIXA NO MÊS ANTERIOR A ESTA OCORRÊNCIA
                    $despesa->despesaFixa->encerrar($despesa->competencia()->subMonthNoOverflow());
                }else{
                    $despesa->excluirComLancamentos();
                }
            });

            return [
                'error' => false,
                'msg' => 'Registro excluído com sucesso!'
            ];
        }catch(\Exception $error){
            \Log::error($error->getMessage(), ['exception' => $error]);
            return [
                'error' => true,
                'msg' => 'Não foi possível excluír o registro, tente de novo'
            ];
        }
    }

    public function pagamentoDespesa($id, $user){
        //FORA DO try: DESPESA DE OUTRO USUÁRIO VIRA 404, E NÃO UM ERRO GENÉRICO
        $this->doUsuario($id, $user);

        try{
            //O pago E O LANÇAMENTO MUDAM JUNTOS: SE O LANÇAMENTO FALHAR, A DESPESA FICA COMO ESTAVA
            $pago = DB::transaction(function() use ($id, $user){
                //RELÊ COM TRAVA: DOIS CLIQUES AO MESMO TEMPO NÃO LANÇAM DOIS PAGAMENTOS
                $despesa = $this->where('user_id', $user->id)->lockForUpdate()->findOrFail($id);
                //SÓ LANÇAMENTOS DO PRÓPRIO USUÁRIO (DADOS ANTIGOS PODEM TER LANÇAMENTO DE OUTRO NESTA DESPESA)
                $lancamentos = Lancamento::where('despesa_id', $id)->where('user_id', $user->id);

                if($despesa->pago == 1){
                    $despesa->pago = 0;
                    $despesa->save();
                    $lancamentos->delete();
                    return false;
                }

                //DADO ANTIGO COM LANÇAMENTO E pago = 0: SÓ ACERTA O pago, SEM LANÇAR DE NOVO
                if($lancamentos->exists()){
                    $despesa->pago = 1;
                    $despesa->save();
                    return true;
                }

                //lancar MARCA O pago E LANÇA A EXCEÇÃO SE FALHAR, DESFAZENDO ESTA TRANSAÇÃO
                (new Lancamento)->lancar([
                    'data_lancamento' => date('Y-m-d H:i:s'),
                    'despesa_id' => $id,
                    'valor' => floatVal($despesa->valor_total),
                    'descricao' => $despesa->nome_despesa,
                ], $user);

                return true;
            });

            return [
                'error' => false,
                'msg' => $pago ? "Pagamento registrado" : "Remoção de Pagamento efetuado com sucesso!"
            ];
        }catch(\Exception $error){
            \Log::error($error->getMessage(), ['exception' => $error]);
            return [
                'error' => true,
                'msg' => 'Ocorreu um erro ao declarar/remover pagamento, tente de novo'
            ];
        }
    }
}

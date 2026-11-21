<?php

namespace App\Models;

use Carbon\Carbon;
use DB;
use Illuminate\Database\Eloquent\Model;
use App\Recorrencia\Vigencia;

class DespesaFixa extends Model
{
    protected $table = 'despesas_fixas';
    protected $fillable = [
        'user_id',
        'nome_despesa',
        'despesa_tipo_id',
        'valor',
        'ativo',
        'mes_inicio',
        'mes_fim',
        'dia_vencimento',
    ];

    protected $casts = [
        'mes_inicio' => 'date',
        'mes_fim' => 'date',
        'ocorrencias_ate' => 'date',
    ];

    public function despesaTipo(){
        return $this->hasOne(TiposDespesa::class, 'id', 'despesa_tipo_id');
    }

    public function despesas(){
        return $this->hasMany(Despesa::class, 'despesa_fixa_id', 'id');
    }

    public function getDespesasFixas($request = [], $user){
        $conditions = [];
        $conditions[] = ['user_id', '=', $user->id];

        if(isset($request['nome_despesa']) && !empty($request['nome_despesa'])){
            $conditions[] = ['nome_despesa', 'LIKE', "%".$request['nome_despesa']."%"];
        }

        if(isset($request['despesa_tipo_id']) && !empty($request['despesa_tipo_id'])){
            $conditions[] = ['despesa_tipo_id', '=', $request['despesa_tipo_id']];
        }

        if(isset($request['ativo']) && $request['ativo'] !== ''){
            $conditions[] = ['ativo', '=', $request['ativo']];
        }

        return $this
            ->where($conditions)
            ->with('despesaTipo')
            ->withCount('despesas')
            ->orderBy('ativo', 'DESC')
            ->orderBy('valor', 'DESC');
    }

    public function saveDespesaFixa($request = [], $user){
        try{
            //A FIXA E TODAS AS OCORRÊNCIAS DELA SÃO GRAVADAS JUNTAS: SE UMA FALHAR, NADA FICA
            DB::transaction(function() use ($request, $user){
                $inicio = Carbon::createFromFormat('!Y-m', $request['mes_inicio']);

                if(!empty($request['parcelas'])){
                    $fim = Vigencia::fimPorParcelas($inicio, (int) $request['parcelas']);
                }else{
                    $fim = !empty($request['mes_fim']) ? Carbon::createFromFormat('!Y-m', $request['mes_fim']) : null;
                }

                $this->fill([
                    'user_id' => $user->id,
                    'nome_despesa' => $request['nome_despesa'],
                    'valor' => setToDecimal($request['valor']),
                    'ativo' => $request['ativo'] ?? 1,
                    'despesa_tipo_id' => (new TiposDespesa)->idTipoFixaDoUsuario($user),
                    'mes_inicio' => $inicio->format('Y-m-d'),
                    'mes_fim' => $fim ? $fim->format('Y-m-d') : null,
                    'dia_vencimento' => (int) $request['dia_vencimento'],
                ])->save();

                $this->gravarOcorrencias();
            });

            return [
                'error' => false,
                'msg' => 'Despesa fixa adicionada!'
            ];
        }catch(\Exception $error){
            \Log::error($error->getMessage(), ['exception' => $error]);
            return [
                'error' => true,
                'msg' => 'Não foi possível salvar o registro, tente de novo'
            ];
        }
    }

    public function vigencia(){
        return new Vigencia($this->mes_inicio, $this->mes_fim, (int) $this->dia_vencimento);
    }

    /**
     * Grava as Ocorrências que faltam (ADR-0001): só meses depois da última gravada, nunca meses passados.
     * Chamar dentro de uma transação.
     */
    public function gravarOcorrencias(){
        $ocorrencias = $this->vigencia()->ocorrenciasAGravar(Carbon::now(), $this->ocorrencias_ate);

        foreach($ocorrencias as $ocorrencia){
            Despesa::create([
                'user_id' => $this->user_id,
                'nome_despesa' => $ocorrencia->nome($this->nome_despesa),
                'despesa_tipo_id' => $this->despesa_tipo_id,
                'despesa_fixa_id' => $this->id,
                'valor_total' => $this->valor,
                'data_vencimento' => $ocorrencia->vencimento->format('Y-m-d'),
                'ativo' => 1,
                'pago' => 0,
            ]);
        }

        if(!empty($ocorrencias)){
            $this->ocorrencias_ate = end($ocorrencias)->mes->format('Y-m-d');
            $this->save();
        }

        return count($ocorrencias);
    }

    /**
     * Ocorrências desta fixa com Competência no mês informado ou depois.
     * Competência é o mês do Vencimento; registros antigos sem Vencimento usam a data de cadastro (ADR-0002).
     */
    public function ocorrenciasAPartirDe(Carbon $mes){
        return $this->despesas()
            ->where(DB::raw('COALESCE(data_vencimento, created_at)'), '>=', $mes->copy()->startOfMonth()->format('Y-m-d'));
    }

    /**
     * Reajuste: o novo Valor Previsto e/ou dia de vencimento valem do mês informado em diante, sobrescrevendo
     * também as Ocorrências ajustadas à mão. As Ocorrências anteriores não mudam.
     */
    public function reajustar(Carbon $aPartirDe, $valor = null, $diaVencimento = null){
        DB::transaction(function() use ($aPartirDe, $valor, $diaVencimento){
            $fixa = static::lockForUpdate()->findOrFail($this->id);

            if($valor !== null){
                $fixa->valor = $valor;
            }
            if($diaVencimento !== null){
                $fixa->dia_vencimento = (int) $diaVencimento;
            }
            $fixa->save();

            $vigencia = $fixa->vigencia();
            foreach($fixa->ocorrenciasAPartirDe($aPartirDe)->get() as $ocorrencia){
                $mes = Carbon::parse($ocorrencia->data_vencimento ?: $ocorrencia->created_at);
                $ocorrencia->valor_total = $fixa->valor;
                $ocorrencia->data_vencimento = $vigencia->vencimentoNoMes($mes)->format('Y-m-d');
                $ocorrencia->save();
            }

            $this->setRawAttributes($fixa->getAttributes(), true);
        });
    }

    /**
     * Encerramento: o fim da Vigência passa a ser o mês informado, e as Ocorrências depois dele são excluídas
     * (com os Lançamentos delas). Se o fim ficar antes do início, não sobra Vigência e a fixa é excluída.
     */
    public function encerrar(Carbon $fim){
        DB::transaction(function() use ($fim){
            $fixa = static::lockForUpdate()->findOrFail($this->id);
            $fim = $fim->copy()->startOfMonth();

            foreach($fixa->ocorrenciasAPartirDe($fim->copy()->addMonthNoOverflow())->get() as $ocorrencia){
                $ocorrencia->excluirComLancamentos();
            }

            if($fim->lt($fixa->mes_inicio)){
                $fixa->delete();
                return;
            }

            $fixa->mes_fim = $fim->format('Y-m-d');
            $fixa->save();
            $this->setRawAttributes($fixa->getAttributes(), true);
        });
    }

    /**
     * Completa a janela de Ocorrências das fixas do usuário. Idempotente: abrir a tela de novo não grava nada.
     */
    public function completarJanela($user){
        $ids = $this
            ->where('user_id', $user->id)
            ->where('ativo', 1)
            ->pluck('id');

        $gravadas = 0;
        foreach($ids as $id){
            $gravadas += DB::transaction(function() use ($id){
                //TRAVA A FIXA: DUAS TELAS ABERTAS AO MESMO TEMPO NÃO GRAVAM A MESMA OCORRÊNCIA DUAS VEZES
                return static::lockForUpdate()->find($id)->gravarOcorrencias();
            });
        }

        return $gravadas;
    }

    public function temOcorrencias(){
        return isset($this->despesas_count)
            ? $this->despesas_count > 0
            : $this->despesas()->exists();
    }

    private function resposta(callable $operacao, $sucesso, $falha){
        try{
            $operacao();

            return [
                'error' => false,
                'msg' => $sucesso
            ];
        }catch(\Exception $error){
            \Log::error($error->getMessage(), ['exception' => $error]);
            return [
                'error' => true,
                'msg' => $falha
            ];
        }
    }

    /**
     * Renomeia a fixa. O nome novo vale para as Ocorrências do mês atual em diante; as anteriores ficam como estão.
     */
    public function updateDespesaFixa($id, $request = [], $user){
        $despesaFixa = $this->where('user_id', $user->id)->findOrFail($id);

        return $this->resposta(function() use ($despesaFixa, $request){
            DB::transaction(function() use ($despesaFixa, $request){
                $despesaFixa->nome_despesa = $request['nome_despesa'];
                $despesaFixa->save();

                $vigencia = $despesaFixa->vigencia();
                foreach($despesaFixa->ocorrenciasAPartirDe(Carbon::now())->get() as $ocorrencia){
                    $ocorrencia->nome_despesa = $vigencia->ocorrenciaNoMes($ocorrencia->competencia())->nome($despesaFixa->nome_despesa);
                    $ocorrencia->save();
                }
            });
        }, 'Despesa fixa alterada com sucesso!', 'Não foi possível alterar o registro, tente de novo.');
    }

    public function reajustarDespesaFixa($id, $request, $user){
        $despesaFixa = $this->where('user_id', $user->id)->findOrFail($id);

        return $this->resposta(function() use ($despesaFixa, $request){
            $despesaFixa->reajustar(
                Carbon::createFromFormat('!Y-m', $request['a_partir_de']),
                setToDecimal($request['valor']),
                (int) $request['dia_vencimento']
            );
        }, 'Reajuste aplicado!', 'Não foi possível reajustar, tente de novo.');
    }

    public function encerrarDespesaFixa($id, $request, $user){
        $despesaFixa = $this->where('user_id', $user->id)->findOrFail($id);

        return $this->resposta(function() use ($despesaFixa, $request){
            $despesaFixa->encerrar(Carbon::createFromFormat('!Y-m', $request['mes_fim']));
        }, 'Despesa fixa encerrada!', 'Não foi possível encerrar, tente de novo.');
    }

    public function deleteDespesaFixa($id, $user){
        $despesaFixa = $this->where('user_id', $user->id)->findOrFail($id);

        //COM OCORRÊNCIAS, EXCLUIR APAGARIA O HISTÓRICO: O CAMINHO É O ENCERRAMENTO
        if($despesaFixa->temOcorrencias()){
            return [
                'error' => true,
                'msg' => 'Esta despesa fixa já tem Ocorrências. Use "Encerrar" para terminar a Vigência.'
            ];
        }

        return $this->resposta(function() use ($despesaFixa){
            $despesaFixa->delete();
        }, 'Registro excluído com sucesso!', 'Não foi possível excluír o registro, tente de novo');
    }

    public function totalAtivas($user){
        return $this
            ->where('user_id', $user->id)
            ->where('ativo', 1)
            ->sum('valor');
    }
}

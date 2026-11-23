<?php

namespace App\Models;

use Carbon\Carbon;
use DB;
use Illuminate\Database\Eloquent\Model;
use App\Recorrencia\Ocorrencia;
use App\Recorrencia\Recorrente;
use App\Recorrencia\Vigencia;
use App\Recorrencia\SemOcorrenciasOcultas;

class DespesaFixa extends Model
{
    use Recorrente;

    protected $table = 'despesas_fixas';
    protected $fillable = [
        'user_id',
        'nome_despesa',
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

    //A PRÓPRIA FIXA ENXERGA AS SUAS OCORRÊNCIAS MESMO OCULTA (PARA ENCERRAR E EXCLUIR)
    public function despesas(){
        return $this->hasMany(Despesa::class, 'despesa_fixa_id', 'id')->withoutGlobalScope(SemOcorrenciasOcultas::class);
    }

    /**
     * Busca a Despesa Fixa só entre as do usuário: a de outro usuário é tratada como inexistente (404).
     */
    public function doUsuario($id, $user){
        return $this->where('user_id', $user->id)->findOrFail($id);
    }

    public function getDespesasFixas($request = [], $user){
        $conditions = [];
        $conditions[] = ['user_id', '=', $user->id];

        if(isset($request['nome_despesa']) && !empty($request['nome_despesa'])){
            $conditions[] = ['nome_despesa', 'LIKE', "%".$request['nome_despesa']."%"];
        }

        if(isset($request['ativo']) && $request['ativo'] !== ''){
            $conditions[] = ['ativo', '=', $request['ativo']];
        }

        //AS OCULTAS SÓ APARECEM COM "MOSTRAR OCULTAS", DEPOIS DAS VISÍVEIS
        if(empty($request['mostrar_ocultas'])){
            $conditions[] = ['oculta', '=', 0];
        }

        return $this
            ->where($conditions)
            ->orderBy('oculta', 'ASC')
            ->withCount('ocorrencias')
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

    public function ocorrencias(){
        return $this->despesas();
    }

    //COMPETÊNCIA É O MÊS DO VENCIMENTO; REGISTROS ANTIGOS SEM VENCIMENTO USAM A DATA DE CADASTRO (ADR-0002)
    protected function colunaCompetencia(){
        return 'COALESCE(data_vencimento, created_at)';
    }

    protected function diaDaOcorrencia(){
        return $this->dia_vencimento;
    }

    protected function criarOcorrencia(Ocorrencia $ocorrencia){
        Despesa::create([
            'user_id' => $this->user_id,
            'nome_despesa' => $ocorrencia->nome($this->nome_despesa),
            'despesa_fixa_id' => $this->id,
            'valor_total' => $this->valor,
            'data_vencimento' => $ocorrencia->vencimento->format('Y-m-d'),
            'ativo' => 1,
            'pago' => 0,
        ]);
    }

    protected function reajustarOcorrencia($despesa, Ocorrencia $noMes){
        $despesa->valor_total = $this->valor;
        $despesa->data_vencimento = $noMes->vencimento->format('Y-m-d');
    }

    //A OCORRÊNCIA JÁ PAGA É HISTÓRICO: TEM LANÇAMENTO COM O VALOR PAGO
    protected function ocorrenciaPreservada($despesa){
        return $despesa->paga();
    }

    protected function excluirOcorrencia($despesa){
        $despesa->excluirComLancamentos();
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
                    //A OCORRÊNCIA PAGA É HISTÓRICO: FICA COM O NOME QUE TINHA
                    if($despesaFixa->ocorrenciaPreservada($ocorrencia)){
                        continue;
                    }
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

        $fim = Carbon::createFromFormat('!Y-m', $request['mes_fim']);
        if($despesaFixa->fimDepoisDoAtual($fim)){
            return [
                'error' => true,
                'msg' => 'A Vigência já termina em ' . $despesaFixa->mes_fim->format('m/Y') . '. Para estender, cadastre uma nova Despesa Fixa.'
            ];
        }

        return $this->resposta(function() use ($despesaFixa, $request){
            $despesaFixa->encerrar(Carbon::createFromFormat('!Y-m', $request['mes_fim']));
        }, 'Despesa fixa encerrada!', 'Não foi possível encerrar, tente de novo.');
    }

    const MSG_EXCLUIR_COM_PAGA = 'Esta despesa fixa já tem Ocorrência paga. Use "Encerrar" para terminar a Vigência.';

    /**
     * Exclui a Despesa Fixa junto com todas as Ocorrências dela, enquanto nenhuma foi paga (corrige um cadastro
     * errado sem apagar pagamento real). Com alguma paga, o caminho é o Encerramento.
     */
    public function deleteDespesaFixa($id, $user){
        $despesaFixa = $this->doUsuario($id, $user);

        if($despesaFixa->temOcorrenciaPaga()){
            return [
                'error' => true,
                'msg' => self::MSG_EXCLUIR_COM_PAGA
            ];
        }

        return $this->resposta(function() use ($despesaFixa){
            DB::transaction(function() use ($despesaFixa){
                $fixa = static::lockForUpdate()->findOrFail($despesaFixa->id);

                //UM PAGAMENTO FEITO ENTRE A CONFERÊNCIA ACIMA E A TRAVA CANCELA A EXCLUSÃO
                if($fixa->temOcorrenciaPaga()){
                    throw new \RuntimeException('Ocorrência paga durante a exclusão da Despesa Fixa ' . $fixa->id);
                }

                foreach($fixa->ocorrencias()->get() as $ocorrencia){
                    $ocorrencia->excluirComLancamentos();
                }
                $fixa->delete();
            });
        }, 'Despesa fixa e Ocorrências excluídas!', 'Não foi possível excluír o registro, tente de novo');
    }

    public function temOcorrenciaPaga(){
        return $this->ocorrencias()->get()->contains(function($ocorrencia){
            return $ocorrencia->paga();
        });
    }

    /**
     * Soma do Valor Previsto das Despesas Fixas cuja Vigência inclui o mês atual.
     */
    public function totalVigentes($user){
        $mesAtual = Carbon::now()->startOfMonth()->format('Y-m-d');

        return $this
            ->where('user_id', $user->id)
            ->where('ativo', 1)
            ->where('oculta', 0)
            ->whereDate('mes_inicio', '<=', $mesAtual)
            ->where(function($query) use ($mesAtual){
                $query->whereNull('mes_fim')->orWhereDate('mes_fim', '>=', $mesAtual);
            })
            ->sum('valor');
    }
}

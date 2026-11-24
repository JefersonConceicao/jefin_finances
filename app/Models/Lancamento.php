<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use DB;
use App\Recorrencia\SemOcorrenciasOcultas;

class Lancamento extends Model
{
    protected $table = 'lancamentos';
    protected $fillable = [
        'data_lancamento',
        'despesa_id',
        'valor',
        'descricao',
        'user_id'
    ];

    public $timestamps = false;

    //ENXERGA A DESPESA MESMO QUANDO É OCORRÊNCIA DE UMA FIXA OCULTA: O PAGAMENTO CONTINUA LIGADO A ELA
    public function despesa(){
        return $this->hasOne(Despesa::class, 'id', 'despesa_id')->withoutGlobalScope(SemOcorrenciasOcultas::class);
    }

    /**
     * Lançamentos do usuário, sem os que pagam Ocorrência de uma Despesa Fixa oculta (Ocultação: somem de todo o app).
     */
    private function doUsuarioVisiveis($user){
        return $this
            ->where('user_id', $user->id)
            ->where(function($query){
                $query->whereNull('despesa_id')
                    ->orWhereNotIn('despesa_id', function($ocultas){
                        $ocultas->select('despesas.id')
                            ->from('despesas')
                            ->join('despesas_fixas', 'despesas_fixas.id', '=', 'despesas.despesa_fixa_id')
                            ->where('despesas_fixas.oculta', 1);
                    });
            });
    }

    public function getLancamentos($request = [], $user){
        return $this->doUsuarioVisiveis($user);
    }

    public function getTotalLancamentosByMonth($request = [], $user){
        return $this->doUsuarioVisiveis($user)
            ->whereMonth('data_lancamento', !empty($request['mes']) ? $request['mes'] : '')
            ->whereYear('data_lancamento', !empty($request['ano']) ? $request['ano'] : '')
            ->sum('valor');
    }

    public function getLancamentosDespesa($request = [], $user){
        return $this->doUsuarioVisiveis($user)
            ->whereMonth('data_lancamento', !empty($request['mes']) ? $request['mes'] : '')
            ->whereYear('data_lancamento', !empty($request['ano']) ? $request['ano'] : '')
            ->has('despesa')
            ->get();
    }

    public function getOutrosLancamentos($request = [], $user){
        return $this->doUsuarioVisiveis($user)
            ->whereMonth('data_lancamento', !empty($request['mes']) ? $request['mes'] : '')
            ->whereYear('data_lancamento', !empty($request['ano']) ? $request['ano'] : '')
            ->doesntHave('despesa')
            ->get();
    }

    public function getUltimosLancamentos($user){
        return $this->doUsuarioVisiveis($user)
            ->orderBy('id', 'DESC')
            ->limit(5)
            ->get();
    }

    public function getLancamentosGroupData($user){
        return $this->doUsuarioVisiveis($user)
            ->select('*')
            ->whereYear('data_lancamento', '=', date('Y'))
            ->orderBy('data_lancamento', 'ASC')
            ->get();
    }

    /**
     * Grava o Lançamento e, se ele paga uma Despesa, marca o pago dela, numa transação.
     * Lança a exceção em caso de falha, para quem chama dentro de outra transação desfazê-la.
     */
    public function lancar(array $request, $user){
        $request['user_id'] = $user->id;

        //SÓ CONVERTE A DATA DO FORMULÁRIO (d/m/Y); A DATA E HORA DO PAGAMENTO (Y-m-d H:i:s) FICA COMO VEIO
        if(isset($request['data_lancamento']) && strpos($request['data_lancamento'], '/') !== false){
            $request['data_lancamento'] = converteData(str_replace('/', '-',$request['data_lancamento']), 'Y-m-d');
        }

        if(isset($request['valor']) && !empty($request['valor'])){
            $request['valor'] = setToDecimal($request['valor']);
        }

        //O LANÇAMENTO E O pago DA DESPESA MUDAM JUNTOS: SE UM FALHAR, NENHUM FICA
        DB::transaction(function() use ($request, $user){
            $despesa = null;

            if(!empty($request['despesa_id'])){
                //TRAVA A DESPESA E CONFERE DE NOVO: A VALIDAÇÃO NÃO IMPEDE DOIS PAGAMENTOS AO MESMO TEMPO
                $despesa = Despesa::where('user_id', $user->id)->lockForUpdate()->findOrFail($request['despesa_id']);
                if(Despesa::estaPaga($despesa->id, $user)){
                    throw new \RuntimeException('Despesa já está paga');
                }
            }

            $this->fill($request)->save();

            if($despesa){
                $despesa->pago = 1;
                $despesa->save();
            }
        });
    }

    public function saveLancamento($request = [], $user){
        try{
            $this->lancar($request, $user);

            return [
                'error' => false,
                'msg' => 'Novo lançamento efetuado',
            ];
        }catch(\Exception $error){
            \Log::error($error->getMessage(), ['exception' => $error]);
            return [
                'error' => true,
                'msg' => 'Não foi possível efetuar o lançamento, tente de novo'
            ];
        }
    }

    /**
     * Busca o Lançamento só entre os do usuário: o de outro usuário é tratado como inexistente (404).
     */
    public function doUsuario($id, $user){
        return $this->doUsuarioVisiveis($user)->findOrFail($id);
    }

    public function deleteLancamento($id, $user){
        $lancamento = $this->doUsuario($id, $user);

        try{
            //O pago DA DESPESA E A EXCLUSÃO MUDAM JUNTOS: SE A EXCLUSÃO FALHAR, A DESPESA CONTINUA PAGA
            DB::transaction(function() use ($lancamento, $user){
                //TRAVA A DESPESA COMO O PAGAMENTO FAZ, PARA A CONFERÊNCIA ABAIXO NÃO CORRER JUNTO COM ELE
                if($lancamento->despesa_id){
                    Despesa::where('id', $lancamento->despesa_id)->where('user_id', $user->id)->lockForUpdate()->first();
                }

                if(!$lancamento->delete()){
                    throw new \RuntimeException('Lançamento não foi excluído');
                }

                //A DESPESA SÓ VOLTA A "A PAGAR" QUANDO NÃO RESTA LANÇAMENTO DO USUÁRIO PARA ELA
                $restaOutro = static::where('despesa_id', $lancamento->despesa_id)->where('user_id', $user->id)->exists();

                if($lancamento->despesa_id && !$restaOutro){
                    //SÓ DESFAZ O PAGAMENTO DE DESPESA DO PRÓPRIO USUÁRIO (DADOS ANTIGOS PODEM LIGAR À DESPESA DE OUTRO)
                    $lancamento->despesa()->where('user_id', $user->id)->update([
                        'pago' => 0
                    ]);
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
                'msg' => 'Não foi possível excluir o registro, tente de novo'
            ];
        }
    }
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use DB;
use Carbon\Carbon;
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

    public function despesaFixa(){
        return $this->belongsTo(DespesaFixa::class, 'despesa_fixa_id', 'id');
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
            return [
                'error' => true,
                'msg' => 'Não foi possível salvar o registro, tente de novo',
                'error_message' => $error->getMessage()
            ];
        }
    }

    public function updateDespesa($id, $request = []){
        try{
            if(isset($request['valor_total']) && !empty($request['valor_total'])){
                $request['valor_total'] = setToDecimal($request['valor_total']);
            }

            //A DATA DE CADASTRO NÃO MUDA; ALTERAR O VENCIMENTO MUDA A COMPETÊNCIA
            $request['data_vencimento'] = Carbon::createFromFormat('d/m/Y', $request['data_vencimento'])->format('Y-m-d');
            unset($request['created_at']);

            $despesa = $this->find($id);
            $despesa->fill($request)->save();

            return [
                'error' => false,
                'msg' => 'Despesa alterada com sucesso!'
            ];
        }catch(\Exception $error){
            return [
                'error' => true,
                'msg' => 'Não foi possível alterar o registro, tente de novo.',
                'error_message' => $error->getMessage()
            ];
        }
    }

    public function deleteDespesa($id){
        if($this->find($id)->delete()){
            return [
                'error' => false,
                'msg' => 'Registro excluído com sucesso!'
            ];
        }else{
            return [
                'error' => true,
                'msg' => 'Não foi possível excluír o registro, tente de novo'
            ];
        }
    }

    public function pagamentoDespesa($id, $user){
        try{
            $lancamento = new Lancamento;

            $despesa = $this->find($id);
            $despesa->pago = $despesa->pago == 1 ? 0 : 1;
            $despesa->save();

            if($despesa->pago == 1){
                $formDataLancamento = [
                    'data_lancamento' => date('Y-m-d H:i:s'),
                    'despesa_id' => $id,
                    'valor' => floatVal($despesa->valor_total),
                    'descricao' => $despesa->nome_despesa,
                ];

                $lancamento->saveLancamento($formDataLancamento, $user);
            }else{
                $lancamento->where('despesa_id', $id)->delete();
            }

            return [
                'error' => false,
                'msg' => $despesa->pago == 1 ? "Pagamento registrado" : "Remoção de Pagamento efetuado com sucesso!"
            ];
        }catch(\Exception $error){
            return [
                'error' => true,
                'msg' => 'Ocorreu um erro ao declarar/remover pagamento, tente de novo'
            ];
        }
    }
}

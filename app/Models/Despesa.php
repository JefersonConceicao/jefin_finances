<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use DB;

class Despesa extends Model
{
    protected $table = 'despesas';
    protected $fillable = [
        'user_id',
        'nome_despesa',
        'despesa_tipo_id',
        'valor_total',
        'ativo',
        'pago',
        'created_at',
        'updated_at'
    ];

    public function despesaTipo(){
        return $this->hasOne(TiposDespesa::class, 'id', 'despesa_tipo_id');
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

        $dataWithFilter = $this
            ->where($conditions)
            ->with('despesaTipo');

        if(empty($request['mes']) && !empty($request['ano'])){
            return $this
                ->where($conditions)
                ->whereYear('created_at', $request['ano'])
                ->with('despesaTipo');
        
        }

        if(empty($request['ano']) && !empty($request['mes'])){
            return $this
            ->where($conditions)
            ->whereMonth('created_at', $request['mes'])
            ->with('despesaTipo');
    
        }

        if(!empty($request['mes']) && !empty($request['ano'])){
            return $this
            ->where($conditions)
            ->whereMonth('created_at', $request['mes'])
            ->whereYear('created_at', $request['ano'])
            ->with('despesaTipo');
        
        }

        return $dataWithFilter;
    }


    public function saveDespesa($request = [], $user){
        try{
            $request['user_id'] = $user->id;

            if(isset($request['valor_total']) && !empty($request['valor_total'])){
                $request['valor_total'] = setToDecimal($request['valor_total']);
            }

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

    public function pagamentoDespesa($id){
        try{
            $despesa = $this->find($id);

            $despesa->pago = $despesa->pago == 1 ? 0 : 1;
            $despesa->save();

            return [
                'error' => false,
                'msg' => $despesa->pago == 1 ? "Pagamento registrado!" : "Remoção de Pagamento efetuado com sucesso!" 
            ];
        }catch(\Exception $error){
            return [
                'error' => true,
                'msg' => 'Ocorreu um erro ao declarar/remover pagamento, tente de novo'
            ];
        }
    }

    public function copyDespesas($request = []){
        try{
            $this->timestamps = false;
            $anteriorDate = $request['mes'] - 1;
            
            $despMesAnterior = $this
            ->whereMonth('created_at', $anteriorDate)
            ->whereYear('created_at', $request['ano']);
            
            if($despMesAnterior->count() == 0){
               return [
                    'error' => true,
                    'msg' => 'Infelizmente não encontramos despesas do mês anterior, é necessario o cadastro manual'
               ];
            }

           DB::beginTransaction();
            foreach($despMesAnterior->get()->toArray() as $k => $v){
                $formData = [
                    'user_id' => $v['user_id'],
                    'nome_despesa' => $v['nome_despesa'],
                    'despesa_tipo_id' => $v['despesa_tipo_id'],
                    'valor_total' => $v['valor_total'],
                    'ativo' => $v['ativo'],
                    'created_at' => $request['ano'].'-'.$request['mes'].'-'.date('d').' '.date('H:i:s'),
                    'updated_at' => $request['ano'].'-'.$request['mes'].'-'.date('d').' '.date('H:i:s'),
                    'pago' => 0
                ];

                $this->create($formData);
                unset($formData);
            }

            DB::commit();
            return [
                'error' => false,
                'msg' => 'Despesas Adicionadas!'
            ];
        }catch(\Exception $error){
            DB::rollback();
            return [
                'error' => true,
                'msg' => 'Algo deu errado, tente de novo.',
            ];
        }
    }
}

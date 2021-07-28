<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Proventos extends Model
{
    protected $table = 'proventos';
    protected $fillable = [
        'user_id',
        'valor_provento',
        'data_provento',
        'created_at',
        'updated_at',
        'descricao_provento'
    ];

    public $timestamps = true; 

    public function getProventos($request = [], $user){
        $conditions = [];   
        $conditions[] = ['user_id', '=', $user->id];

        if(isset($request['descricao_provento']) && !empty($request['descricao_provento'])){
            $conditions[] = ['descricao_provento', 'LIKE', "%".$request['descricao_provento']."%"];
        }

        $dataWithFilter = $this
            ->where($conditions)
            ->whereMonth('data_provento', !empty($request['mes']) ? $request['mes'] : null)
            ->whereYear('data_provento',  !empty($request['ano']) ? $request['ano'] : null);
           

      
        if(empty($request['mes']) && isset($request['ano']) && !empty($request['ano'])){
            return $this
                ->where($conditions)
                ->whereYear('data_provento', $request['ano']);
             
        }

        if(empty($request['ano']) && isset($request['mes']) && !empty($request['mes'])){
            return $this
            ->where($conditions)
            ->whereMonth('data_provento', $request['mes']);
           
        }

        if(empty($request['mes']) && empty($request['ano'])){
            return $this
                ->where($conditions);
               
        }

        return $dataWithFilter;
    }

    public function saveProvento($request = [], $user){
        try{ 
            $request['user_id'] = $user->id;

            if(isset($request['valor_provento']) && !empty($request['valor_provento'])){
                $request['valor_provento'] = setToDecimal($request['valor_provento']);
            }

            if(isset($request['data_provento']) && !empty($request['data_provento'])){
                $request['data_provento'] = converteData(str_replace('/','-', $request['data_provento']), 'Y-m-d');
            }

            $this->fill($request)->save();

            return [
                'error' => false,
                'msg' => 'Provento adicionado!'
            ];
        }catch(\Exception $error){
            return [
                'error' => true,
                'msg' => 'Não foi possível salvar o registro.',
                'error_message' => $error->getMessage()
            ];
        }
    }

    public function updateProvento($id, $request = []){
        try{
            if(isset($request['valor_provento']) && !empty($request['valor_provento'])){
                $request['valor_provento'] = setToDecimal($request['valor_provento']);
            }  

            if(isset($request['data_provento']) && !empty($request['data_provento'])){
                $request['data_provento'] = converteData(str_replace('/','-', $request['data_provento']), 'Y-m-d');
            }

            $provento = $this->find($id);
            $provento->fill($request)->save();

            return [
                'error' => false,
                'msg' => 'Provento alterado!'
            ];
        }catch(\Exception $error){
            return [
                'error' => false,
                'msg' => 'Não foi possível alterar o registro.',
            ];
        }
    }

    public function deleteProvento($id){
        if($this->find($id)->delete()){
            return [
                'error' => false,
                'msg' => 'Registro excluído com sucesso!',
            ];
        }else{
            return [
                'error' => true,
                'msg' => 'Não foi possível excluír o registro, tente de novo',
            ];
        }
    }
}

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

    public function getProventos($request = []){
        $conditions = [];
        return $this->all();
    }

    public function saveProvento($request = [], $user){
        try{ 
            $request['user_id'] = $user->id;

            if(isset($request['valor_provento']) && !empty($request['valor_provento'])){
                $request['valor_provento'] = floatVal(clearSpecialCaracters($request['valor_provento']));
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

    public function updateProvento(){

    }

    public function deleteProvento(){

    }
}

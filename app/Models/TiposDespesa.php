<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TiposDespesa extends Model
{
    protected $table = 'despesas_tipos';
    protected $fillable = [
        'nome',
        'ativo'
    ];

    public $timestamps = false;

    public function getTiposDespesas(){
        return $this->all();
    }

    public function saveTiposDespesa($request = []){
        try{
            $this->fill($request)->save();

            return [
                'error' => false,
                'msg' => 'Registro salvo com sucesso!'   
            ];
        }catch(\Exception $error){
            return [
                'error' => true,
                'msg' => 'Não foi possível salvar o registro, tente de novo',
            ];
        }
    }

    public function updateTiposDespesa($id, $request = []){
        try{ 
            $tipoDespesa = $this->find($id);
            $tipoDespesa->fill($request)->save();

            return [
                'error' => false,
                'msg' => 'Registro alterado com sucesso!'   
            ];
        }catch(\Excpetion $error){
            return [
                'error' => true,
                'msg' => 'Não foi possível atlerar o registro, tente de novo',
            ];
        }
    }

    public function deleteTipoDespesa($id){
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
}

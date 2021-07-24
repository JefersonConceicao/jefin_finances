<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Despesa extends Model
{
    protected $table = 'despesas';
    protected $fillable = [
        'user_id',
        'nome_despesa',
        'despesa_tipo_id',
        'valor_total',
        'ativo',
        'data_gasto'
    ];

    public function getDespesas($request = []){
        return $this->all();
    }

    public function saveDespesa(){

    }

    public function updateDespesa(){
        
    }

    public function deleteDespesa(){
        
    }
}

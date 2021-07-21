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

    public function getProventos(){
        return $this->all();
    }

    public function saveProvento(){

    }

    public function updateProvento(){

    }

    public function deleteProvento(){

    }
}

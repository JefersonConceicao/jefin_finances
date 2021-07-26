<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Lancamento extends Model
{
    protected $table = 'lancamentos';
    protected $fillable = [
        'data_lancamento',
        'despesa_id',
        'valor',
        'descricao'
    ];
    
    public $timestamps = false;
}

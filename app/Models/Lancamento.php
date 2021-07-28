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

    public function despesa(){
        return $this->hasOne(Despesa::class, 'id', 'despesa_id');
    }


    public function getLancamentos($request = [], $user){
        return $this
            ->where('user_id', $user->id)
            ->whereMonth('data_lancamento', !empty($request['mes']) ? $request['mes'] : '')
            ->whereYear('data_lancamento', !empty($request['ano']) ? $request['ano'] : '')
            ->get();
    }

    public function getLancamentosDespesa($request = [], $user){
        return $this
            ->where('user_id', $user->id)
            ->whereMonth('data_lancamento', !empty($request['mes']) ? $request['mes'] : '')
            ->whereYear('data_lancamento', !empty($request['ano']) ? $request['ano'] : '')
            ->has('despesa')
            ->get();
    }

    public function getOutrosLancamentos($request = [], $user){
        return $this
            ->where('user_id', $user->id)
            ->whereMonth('data_lancamento', !empty($request['mes']) ? $request['mes'] : '')
            ->whereYear('data_lancamento', !empty($request['ano']) ? $request['ano'] : '')
            ->doesntHave('despesa')
            ->get();
    } 
}

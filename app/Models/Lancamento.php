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
        'descricao',
        'user_id'
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

    public function saveLancamento($request = [], $user){
        try{  
            $request['data_lancamento'] = date('Y-m-d H:i:s');
            $request['user_id'] = $user->id; 

            if(isset($request['valor']) && !empty($request['valor'])){
                $request['valor'] = setToDecimal($request['valor']);
            }

            $this->fill($request)->save();

            if(isset($request['despesa_id']) && !empty($request['despesa_id'])){
                dd($this->despesa()->update([
                    'pago' => 1
                ]));
            }   
            
            return [
                'error' => false,
                'msg' => 'Novo lançamento efetuado',
            ];
        }catch(\Exception $error){
            return [
                'error' => true,
                'msg' => 'Não foi possível efetuar o lançamento, tente de novo',
                'error_message' => $error->getMessage()
            ];
        }   
    }

    public function deleteLancamento($id){

    }
}

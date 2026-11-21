<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TiposDespesa extends Model
{
    protected $table = 'despesas_tipos';
    protected $fillable = [
        'nome',
        'ativo',
        'user_id',
    ];

    public $timestamps = false;

    public function getTiposDespesas($request = [], $user){
        $conditions = [];
        $conditions[] = ['user_id', '=', $user->id];
                
        if(isset($request['nome']) && !empty($request['nome'])){
            $conditions[] = ['nome', 'LIKE',"%".$request['nome']."%"];
        }

        if(isset($request['ativo']) && $request['ativo'] != ""){
            $conditions[] = ['ativo', '=' ,$request['ativo']];
        }
    
        return $this
            ->where($conditions)
            ->orderBy('id', 'DESC')
            ->get();
    }

    public function saveTiposDespesa($request = [], $user){
        try{
            $request['user_id'] = $user->id;
            $this->fill($request)->save();

            return [
                'error' => false,
                'msg' => 'Registro salvo com sucesso!',
                'dataAdded' => $this->find($this->id)   
            ];
        }catch(\Exception $error){
            return [
                'error_msg' => $error->getMessage(),
                'error' => true,
                'msg' => 'Não foi possível salvar o registro, tente de novo',
            ];
        }
    }

    /**
     * Opções de Tipo de Despesa para uma Despesa Variável: os do usuário, sem o tipo reservado.
     */
    public function opcoesDespesaVariavel($user){
        return $this
            ->where('user_id', $user->id)
            ->where('reservado', 0)
            ->pluck('nome', 'id');
    }

    const MSG_RESERVADO = 'O tipo "Fixa" é reservado às Despesas Fixas e não pode ser alterado nem excluído';

    public function updateTiposDespesa($id, $request = []){
        try{ 
            $tipoDespesa = $this->find($id);

            if($tipoDespesa->reservado){
                return ['error' => true, 'msg' => self::MSG_RESERVADO];
            }

            $tipoDespesa->fill($request)->save();

            return [
                'error' => false,
                'msg' => 'Registro alterado com sucesso!',
                'dataUpdated' => $tipoDespesa  
            ];
        }catch(\Excpetion $error){
            return [
                'error' => true,
                'msg' => 'Não foi possível atlerar o registro, tente de novo',
            ];
        }
    }

    /**
     * Tipo de Despesa "Fixa" reservado do usuário, usado em toda Despesa Fixa e nas Ocorrências dela.
     * É identificado pelo marcador `reservado`, não pelo nome; se o usuário ainda não tiver, é criado.
     */
    public function idTipoFixaDoUsuario($user){
        $existente = $this
            ->where('user_id', $user->id)
            ->where('reservado', 1)
            ->orderBy('id')
            ->value('id');

        if($existente){
            return $existente;
        }

        //reservado FICA FORA DO fillable PARA O FORMULÁRIO NÃO CONSEGUIR MARCAR UM TIPO COMO RESERVADO
        $tipo = new static;
        $tipo->forceFill([
            'user_id' => $user->id,
            'nome' => 'Fixa',
            'ativo' => 1,
            'reservado' => 1,
        ])->save();

        return $tipo->id;
    }

    public function deleteTipoDespesa($id){
        $tipoDespesa = $this->find($id);

        if($tipoDespesa->reservado){
            return ['error' => true, 'msg' => self::MSG_RESERVADO];
        }

        if($tipoDespesa->delete()){
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
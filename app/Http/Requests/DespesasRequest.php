<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Route;
use App\Models\Despesa;

class DespesasRequest extends FormRequest
{
    /**
     * @return bool
     */
    public function authorize()
    {
        //NA EDIÇÃO, DESPESA DE OUTRO USUÁRIO VIRA 404 ANTES DA VALIDAÇÃO (QUE RESPONDERIA 422)
        if($this->route()->getName() === 'despesas.update'){
            (new Despesa)->doUsuario($this->route('id'), $this->user());
        }

        return true;
    }

    /**
     * @return array
     */
    public function rules()
    {   
        $currentRoute = explode('.', Route::currentRouteName());
        switch(end($currentRoute)){
            case 'store':
                //SEM data_vencimento, A DESPESA VENCE HOJE
                return [
                    'nome_despesa' => 'required',
                    'valor_total' => 'required',
                    'ativo' => 'required',
                    'data_vencimento' => 'nullable|date_format:d/m/Y'
                ];
            case 'update': 
                return [
                    'nome_despesa' => 'required',
                    'valor_total' => 'required',
                    'ativo' => 'required',
                    'data_vencimento' => 'required|date_format:d/m/Y',
                    //SÓ VALE PARA OCORRÊNCIA DE DESPESA FIXA: "SÓ ESTA" OU "ESTA E AS SEGUINTES"
                    'escopo' => 'nullable|in:so_esta,seguintes',
                ];
        }
    }

    public function messages(){
        return [
            'required' => 'Campo obrigatório'
        ];
    }
}

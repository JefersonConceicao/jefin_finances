<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Route;

class DespesasRequest extends FormRequest
{
    /**
     * @return bool
     */
    public function authorize()
    {
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
                return [
                    'nome_despesa' => 'required',
                    'despesa_tipo_id' => 'required',
                    'valor_total' => 'required',
                    'ativo' => 'required'
                ];
            case 'update': 
                return [
                    'nome_despesa' => 'required',
                    'despesa_tipo_id' => 'required',
                    'valor_total' => 'required',
                    'ativo' => 'required'
                ];  
        }
    }

    public function messages(){
        return [
            'required' => 'Campo obrigatório'
        ];
    }
}

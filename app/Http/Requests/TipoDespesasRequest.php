<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Route;

class TipoDespesasRequest extends FormRequest
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
                    'nome' => 'required',
                    'ativo' => 'required'
                ]; 
            case 'update': 
                return [
                    'nome' => 'required',
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

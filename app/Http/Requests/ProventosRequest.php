<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Route;

class ProventosRequest extends FormRequest
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
        $validate = [];

        switch(end($currentRoute)){
            case 'store': 
                $validate = [
                    'descricao_provento' => 'required',
                    'valor_provento' => 'required',
                    'data_provento' => 'required|date_format:d/m/Y'
                ]; 
            break;
            case 'update': 
                $validate = [
                    'descricao_provento' => 'required',
                    'valor_provento' => 'required',
                    'data_provento' => 'required|date_format:d/m/Y'
                ];
            break;
        }

        return $validate;
    }

    public function messages(){
        return [
            'required' => 'Campo obrigatório',
            'numeric' => 'Este campo deve conter apenas números',
            'date' => 'Insira uma data válida'
        ];
    }
}

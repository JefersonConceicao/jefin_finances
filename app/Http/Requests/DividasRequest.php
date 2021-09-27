<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Route;

class DividasRequest extends FormRequest
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
                    'descricao_divida' => 'required',
                    'data_inicial_divida' => [
                        'required',
                        'date_format:d/m/Y',
                    ],
                    'valor_total' => 'required',
                    'qtd_parcela_total' => 'required|numeric',
                ];
            case 'update':
                return [
                    'descricao_divida' => 'required',
                    'valor_total' => 'required',
                    'qtd_parcela_total' => 'required|numeric'
                ];
        }
    }

    public function messages(){
        return [
            'required' => 'Campo obrigatório',
            'date_format' => 'Data inválida',
            'numeric' => 'O campo deve conter apenas números'
        ];
    }
}

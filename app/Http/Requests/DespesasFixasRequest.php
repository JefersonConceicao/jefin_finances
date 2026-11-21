<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class DespesasFixasRequest extends FormRequest
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
        return [
            'nome_despesa' => 'required|max:100',
            'valor' => 'required',
            'ativo' => 'required',
        ];
    }

    public function messages(){
        return [
            'required' => 'Campo obrigatório',
            'max' => 'Máximo de :max caracteres',
        ];
    }
}
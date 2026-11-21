<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Route;
use Illuminate\Validation\Rule;

class LancamentosRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        $currentRoute = explode('.', Route::currentRouteName());
        switch(end($currentRoute)){
            case 'store':
                return [
                    'descricao' => 'required',
                    'valor' => 'required',
                    'data_lancamento' => [
                        'required',
                        'date_format:d/m/Y'
                    ],
                    //SÓ PODE PAGAR DESPESA DO PRÓPRIO USUÁRIO
                    'despesa_id' => [
                        'nullable',
                        'integer',
                        Rule::exists('despesas', 'id')->where('user_id', $this->user()->id),
                    ],
                ];
        }
    }

    public function messages(){
        return [
            'required' => 'Campo obrigatório',
            'date_format' => 'Formato de data inválido',
            'despesa_id.exists' => 'Despesa inválida'
        ];
    }
}

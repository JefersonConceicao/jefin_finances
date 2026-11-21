<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Route;
use Illuminate\Validation\Rule;
use App\Models\Despesa;

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
                        //PARA NA PRIMEIRA FALHA: DESPESA DE OUTRO USUÁRIO OU id INVÁLIDO NÃO CHEGAM À CONFERÊNCIA DO PAGAMENTO
                        'bail',
                        'nullable',
                        'integer',
                        Rule::exists('despesas', 'id')->where('user_id', $this->user()->id),
                        //UMA DESPESA SÓ TEM UM PAGAMENTO: SEGUNDO LANÇAMENTO PARA A MESMA DESPESA É RECUSADO
                        function($attribute, $value, $fail){
                            if(Despesa::estaPaga($value, $this->user())){
                                $fail('Despesa já está paga');
                            }
                        },
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

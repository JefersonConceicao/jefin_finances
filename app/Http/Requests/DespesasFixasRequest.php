<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Route;

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
        $rules = [
            'nome_despesa' => 'required|max:100',
            'valor' => 'required',
            'ativo' => 'required',
        ];

        if(Route::currentRouteName() === 'despesasFixas.store'){
            //VIGÊNCIA: MÊS DE INÍCIO, FIM OPCIONAL (OU QUANTIDADE DE PARCELAS) E DIA DE VENCIMENTO
            $rules += [
                'mes_inicio' => 'required|date_format:Y-m',
                'mes_fim' => 'nullable|date_format:Y-m|after_or_equal:mes_inicio',
                'parcelas' => 'nullable|integer|min:1|max:360',
                'dia_vencimento' => 'required|integer|min:1|max:31',
            ];
        }

        return $rules;
    }

    public function messages(){
        return [
            'required' => 'Campo obrigatório',
            'max' => 'Máximo de :max caracteres',
            'date_format' => 'Informe o mês',
            'mes_fim.after_or_equal' => 'O fim não pode ser antes do início',
            'integer' => 'Informe um número',
            'dia_vencimento.min' => 'Dia entre 1 e 31',
            'dia_vencimento.max' => 'Dia entre 1 e 31',
            'parcelas.min' => 'Pelo menos 1 parcela',
            'parcelas.max' => 'No máximo :max parcelas',
        ];
    }
}
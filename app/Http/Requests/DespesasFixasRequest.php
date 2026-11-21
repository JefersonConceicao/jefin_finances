<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Route;
use Carbon\Carbon;

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
        switch(Route::currentRouteName()){
            case 'despesasFixas.store':
                return [
                    'nome_despesa' => 'required|max:100',
                    'valor' => 'required',
                    //VIGÊNCIA: MÊS DE INÍCIO, FIM OPCIONAL (OU QUANTIDADE DE PARCELAS) E DIA DE VENCIMENTO
                    'mes_inicio' => 'required|date_format:Y-m',
                    'mes_fim' => 'nullable|date_format:Y-m|after_or_equal:mes_inicio',
                    'parcelas' => 'nullable|integer|min:1|max:360',
                    'dia_vencimento' => 'required|integer|min:1|max:31',
                ];
            case 'despesasFixas.update':
                return [
                    'nome_despesa' => 'required|max:100',
                ];
            case 'despesasFixas.reajustar':
                //O REAJUSTE VALE DE UM MÊS EM DIANTE, A PARTIR DO MÊS ATUAL
                return [
                    'a_partir_de' => ['required', 'date_format:Y-m', $this->mesAtualOuFuturo()],
                    'valor' => 'required',
                    'dia_vencimento' => 'required|integer|min:1|max:31',
                ];
            case 'despesasFixas.encerrar':
                //O ENCERRAMENTO É NO MÊS ATUAL OU NUM MÊS FUTURO
                return [
                    'mes_fim' => ['required', 'date_format:Y-m', $this->mesAtualOuFuturo()],
                ];
        }

        return [];
    }

    private function mesAtualOuFuturo(){
        return function($attribute, $value, $fail){
            $mes = \DateTime::createFromFormat('!Y-m', (string) $value);
            if($mes && $mes->format('Y-m') < Carbon::now()->format('Y-m')){
                $fail('Escolha o mês atual ou um mês futuro');
            }
        };
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
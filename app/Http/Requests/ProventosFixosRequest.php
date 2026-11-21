<?php

namespace App\Http\Requests;

use Carbon\Carbon;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Route;

class ProventosFixosRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        switch(Route::currentRouteName()){
            case 'proventosFixos.store':
                return [
                    'descricao' => 'required|max:255',
                    'valor' => 'required',
                    'mes_inicio' => 'required|date_format:Y-m',
                    'mes_fim' => 'nullable|date_format:Y-m|after_or_equal:mes_inicio',
                ];
            case 'proventosFixos.update':
                return [
                    'descricao' => 'required|max:255',
                ];
            case 'proventosFixos.reajustar':
                //O REAJUSTE VALE DE UM MÊS EM DIANTE, A PARTIR DO MÊS ATUAL
                return [
                    'a_partir_de' => ['required', 'date_format:Y-m', $this->mesAtualOuFuturo()],
                    'valor' => 'required',
                ];
            case 'proventosFixos.encerrar':
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
        ];
    }
}

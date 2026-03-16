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
                    'period_type' => 'nullable|in:especifico,anual',
                    'data_provento' => 'required_without:period_type|nullable|date_format:d/m/Y',
                    'periodo_inicio' => 'required_if:period_type,especifico|nullable|date_format:d/m/Y',
                    'periodo_fim' => 'required_if:period_type,especifico|nullable|date_format:d/m/Y',
                ]; 
            break;
            case 'update': 
                $validate = [
                    'descricao_provento' => 'required',
                    'valor_provento' => 'required',
                    'period_type' => 'nullable|in:especifico,anual',
                    'data_provento' => 'required_without:period_type|nullable|date_format:d/m/Y',
                    'periodo_inicio' => 'required_if:period_type,especifico|nullable|date_format:d/m/Y',
                    'periodo_fim' => 'required_if:period_type,especifico|nullable|date_format:d/m/Y',
                ];
            break;
        }

        return $validate;
    }

    public function messages(){
        return [
            'required' => 'Campo obrigatório',
            'numeric' => 'Este campo deve conter apenas números',
            'date' => 'Insira uma data válida',
            'date_format' => 'A data deve estar no formato DD/MM/AAAA',
            'periodo_inicio.required_if' => 'O campo Período Início é obrigatório quando Especificar Periodo estiver selecionado',
            'periodo_fim.required_if' => 'O campo Período Fim é obrigatório quando Especificar Periodo estiver selecionado',
            'data_provento.required_without' => 'Informe a data do provento ou selecione um tipo de período'
        ];
    }
}

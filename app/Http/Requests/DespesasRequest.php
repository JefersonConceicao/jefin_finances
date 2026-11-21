<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Route;
use Illuminate\Validation\Rule;
use App\Models\Despesa;

class DespesasRequest extends FormRequest
{
    /**
     * @return bool
     */
    public function authorize()
    {
        //NA EDIÇÃO, DESPESA DE OUTRO USUÁRIO VIRA 404 ANTES DA VALIDAÇÃO (QUE RESPONDERIA 422)
        if($this->route()->getName() === 'despesas.update'){
            (new Despesa)->doUsuario($this->route('id'), $this->user());
        }

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
                //SEM data_vencimento, A DESPESA VENCE HOJE
                return [
                    'nome_despesa' => 'required',
                    'despesa_tipo_id' => ['required', $this->tipoDoUsuario()],
                    'valor_total' => 'required',
                    'ativo' => 'required',
                    'data_vencimento' => 'nullable|date_format:d/m/Y'
                ];
            case 'update': 
                return [
                    'nome_despesa' => 'required',
                    'despesa_tipo_id' => ['required', $this->tipoDoUsuario()],
                    'valor_total' => 'required',
                    'ativo' => 'required',
                    'data_vencimento' => 'required|date_format:d/m/Y'
                ];
        }
    }

    //O TIPO DE DESPESA PRECISA SER DO PRÓPRIO USUÁRIO; O RESERVADO ("Fixa") SÓ VALE PARA A OCORRÊNCIA DE UMA DESPESA FIXA
    private function tipoDoUsuario(){
        $rule = Rule::exists('despesas_tipos', 'id')->where('user_id', $this->user()->id);

        $ocorrencia = $this->route()->getName() === 'despesas.update'
            && !empty((new Despesa)->doUsuario($this->route('id'), $this->user())->despesa_fixa_id);

        return $ocorrencia ? $rule : $rule->where('reservado', 0);
    }

    public function messages(){
        return [
            'required' => 'Campo obrigatório',
            'despesa_tipo_id.exists' => 'Tipo de despesa inválido'
        ];
    }
}

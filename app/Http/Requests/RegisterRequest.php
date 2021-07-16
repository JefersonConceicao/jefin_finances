<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Route;

class RegisterRequest extends FormRequest
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
            'name' => 'required',
            'last_name' => 'required',
            'email' => 'required|email|unique:users',
            'password' => 'required|min:8',
            'password_confirmation' => 'required|min:8|same:password'
        ];
    }

    public function messages(){
        return [
            'required' => 'Preenchimento obrigatório',
            'min' => 'Preencha a quantidade mínima de caracteres (:min)',
            'password_confirmation.same' => 'Confirmação de senha não confere',
            'email' => 'Este campo deve conter um e-mail válido',
            'email.unique' => 'Este e-mail já existe em nossa base de dados'
        ];
    }
}

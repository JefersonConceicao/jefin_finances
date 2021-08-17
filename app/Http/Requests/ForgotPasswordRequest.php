<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Route;
use App\Models\User;

class ForgotPasswordRequest extends FormRequest
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
        $currentRoute = explode('.',Route::currentRouteName());
 
        switch (end($currentRoute)) {
            case 'sendMailForgotPassword':
                return [
                    'email' => [
                        'required',
                        'email',
                        function($attribute, $value, $fail){
                            $user = new User;

                            if(!$user->where('email', $value)->exists()){
                                $fail('Este e-mail não existe em nossa base de dados.');
                            }
                        }
                    ]
                ];
            case 'resetPassword':
                return [
                    'new_password' => [
                        'required',
                        'min:8',
                    ],
                    'confirm_new_password' => [
                        'required',
                        'min:8',
                        'same:new_password'
                    ]
                ];
        }
    }

    public function messages(){
        return [
            'required' => 'Campo obrigatório',
            'min' => 'Quantidade de caracteres mínima (:min)',
            'same' => 'Senhas não conferem'
        ];
    }
}

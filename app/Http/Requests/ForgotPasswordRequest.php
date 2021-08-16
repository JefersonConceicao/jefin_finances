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
                break;  
        }
    }

    public function messages(){
        return [
            'required' => 'Campo obrigatório',
        ];
    }
}

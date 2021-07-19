<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Route;
use App\Models\User;

class LoginRequest extends FormRequest
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
            'email' => [
                'required',
                'email',
                function($attribute, $value, $fail){
                    $user = new User;
                    if($user->where('email', $value)->count() == 0){
                        $fail('Este e-mail não existe em nossa base de dados');
                    }
                }
            ],
            'password' => 'required'
        ];
    }

    public function messages(){
        return [
            'required' => 'Campo obrigatório',
            'email' => 'Este campo deve conter um e-mail válido'
        ];
    }
}

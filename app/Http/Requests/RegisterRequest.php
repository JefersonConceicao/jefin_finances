<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Route;
use Auth;
use Illuminate\Support\Facades\Hash;

use App\Models\User;

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
        $currentRoute = explode('.', Route::currentRouteName());
        $validate = [];

        switch(end($currentRoute)){
            case 'registerUser': 
                $validate = [
                    'name' => 'required',
                    'last_name' => 'required',
                    'email' => 'required|email|unique:users',
                    'password' => 'required|min:8',
                    'password_confirmation' => 'required|min:8|same:password'
                ];
            break;
            case 'store':
                $validate = [
                    'name' => 'required',
                    'last_name' => 'required',
                    'email' => 'required|email|unique:users',
                    'password' => 'required|min:8',
                    'password_confirmation' => 'required|min:8|same:password'
                ];
            break;
            case 'update': 
                $validate = [
                    'name' => 'required',
                    'last_name' => 'required',
                    'email' => [
                        'required', 
                        'email',
                        function($attribute, $value, $fail){
                            $user = new User;
                            
                            $existsMail = $user->where([
                                ['email', '=', $value],
                                ['id', '!=', $this->route('id')]
                            ])->count();

                            if($existsMail > 0){
                                $fail('Este e-mail já existe em nossa base de dados');
                            }   
                        }
                    ]
                ];
            break;
            case 'profileUpdate':
                $validate = [
                    'name' => 'required',
                    'last_name' => 'required',
                    'email' => [
                        'required',
                        'email',
                        function($attribute, $value, $fail){
                            $user = new User;

                            $existsMail = $user->where([
                                ['email', '=', $value],
                                ['id', '!=', Auth::user()->id]
                            ])->count();

                            if($existsMail > 0){
                                $fail('Este e-mail já existe em nossa base de dados');
                            }   
                        }   
                    ]
                ];  
            break;
            case 'changePassword': 
                $validate = [
                    'last_password' => [
                        'required',
                        'min:8',
                        function($attribute, $value, $fail){
                            if(!Hash::check($value, Auth::user()->password)){
                                $fail('Senha atual incorreta');
                            }
                        }
                    ],
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

        return $validate;
    }

    public function messages(){
        return [
            'required' => 'Preenchimento obrigatório',
            'min' => 'Preencha a quantidade mínima de caracteres (:min)',
            'password_confirmation.same' => 'Confirmação de senha não confere',
            'email' => 'Este campo deve conter um e-mail válido',
            'email.unique' => 'Este e-mail já existe em nossa base de dados',
            'confirm_new_password.same' => 'Confirmação de senha deve ser igual a nova senha',
        ];
    }
}

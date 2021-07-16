<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Hash;

class User extends Authenticatable
{
    use Notifiable;

    protected $fillable = [
        'email', 
        'name', 
        'last_name',
        'mail_token_confirm',
        'password_token_reset',
        'password',
        'ativo'
    ];
  
    protected $hidden = ['password'];

    public function signUpUser($request = []){
        try{
            if(isset($request['password']) && !empty($request['password'])){
                $request['password'] = Hash::make($request['password']);
            }

            $this->fill($request)->save();

            return [
                'error' => false,
                'msg' => 'Conta criada com sucesso!'
            ];
        }catch(\Exception $error){
            return [
                'error' => true,
                'msg' => 'Não foi possível criar a conta, tente novamente',
                'error_message' => $error->getMessage()
            ];
        }
    }

}

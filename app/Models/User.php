<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;

use Tymon\JWTAuth\Contracts\JWTSubject;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Hash;

class User extends Authenticatable implements JWTSubject
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

    public function getJWTIdentifier(){
        return $this->getKey();
    }   

    public function getJWTCustomClaims(){
        return [];
    }

    public function getUsers($request = []){
        $conditions = [];

        if(isset($request['name']) && !empty($request['name'])){
            $conditions[] = ['name', 'LIKE', "%".$request['name']."%"];
        }

        if(isset($request['email']) && !empty($request['email'])){
            $conditions[] = ['email', 'LIKE', "%".$request['email']."%"];
        }

        return $this
            ->where($conditions)
            ->paginate(7);
    }

    public function getUsersAPI(){
        return $this->all();
    }

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
            ];
        }
    }

    public function updateUser($id, $request = []){
        try{
            $user = $this->find($id);
            $user->fill($request)->save();

            return [
                'error' => false,
                'msg' => 'Registro alterado com sucesso!' 
            ];
        }catch(\Exception $error){
            return [
                'error' => true,
                'msg' => 'Não foi possível alterar o registro, tente de novo.' 
            ];
        }
    }

    public function deleteUser($id){
        if($this->find($id)->delete()){
            return [
                'error' => false,
                'msg' => 'Registro excluído com sucesso!'
            ];
        }else{
            return [
                'error' => true,
                'msg' => 'Não foi possível excluír o registro, tente de novo'
            ];
        }
    }
}

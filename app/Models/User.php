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
        'ativo',
        'temp_user',
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
            ->get();
    }

    public function getUsersAPI($request = []){
        $conditions = [];

        if(isset($request['name']) && !empty($request['name'])){
            $conditions[] = ['name', 'LIKE', "%".$request['name']."%"];
        }
        
        return $this
            ->where($conditions)
            ->orderBy('id', 'DESC')
            ->get();
    }

    public function signUpUser($request = []){
        try{
            if(isset($request['password']) && !empty($request['password'])){
                $request['password'] = Hash::make($request['password']);
            }

            $this->fill($request)->save();

            return [
                'error' => false,
                'msg' => 'Conta criada com sucesso!',
                'userAdded' => $this->find($this->id)
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
                'msg' => 'Registro alterado com sucesso!',
                'updated' => $user 
            ];
        }catch(\Exception $error){
            return [
                'error' => true,
                'msg' => 'Não foi possível alterar o registro, tente de novo.' 
            ];
        }
    }

    public function updateUserAPI($id, $request = []){
        try{
            $user = $this->find($id);
            $user->fill($request)->save();

            return [
                'error' => false,
                'msg' => 'Registro alterado com sucesso!',
                'updated' => $user
            ];
        }catch(\Excpetion $error){
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

    public function updateProfile($request = [], $authUser){
        try{
            $user = $this->find($authUser->id);
            $user->fill($request)->save();

            return [
                'error' => false,
                'msg' => 'Registro salvo com sucesso!'
            ];
        }catch(\Exception $error){
            return [
                'error' => true,
                'msg' => 'Não foi possível salvar o registro, tente de novo',
                'error_message' => $error->getMessage()
            ];
        }   
    }

    public function updatePassword($request = [], $authUser){
        try{
            $user = $this->find($authUser->id);
            $user->password = Hash::make($request['new_password']);
            $user->save();        

            return [
                'error' => false,
                'msg' => 'Senha alterada!'
            ];
        }catch(\Exception $error){
            return [
                'error' => true,
                'msg' => 'Não foi possível alterar a senha'
            ];
        }
    }

    public function saveTokenResetPassword($email){
        try{
            $user = $this->where('email', $email)->first();
            $user->password_token_reset = md5(uniqid(rand(), true));
            $user->save();
            
            return $user->password_token_reset;
        }catch(\Exception $error){
            return false;
        }
    }

    public function resetPassword($request = []){
        try{
            $user = $this->where('password_token_reset', $request['token'])->first(); 
            $user->password = Hash::make($request['new_password']);
            $user->save();

            return [
                'error' => false,
                'msg' => 'Senha alterada com sucesso'
            ];
        }catch(\Exception $error){
            return [
                'error' => true,
                'msg' => 'Ocorreu um erro inesperado, tente de novo.'
            ];
        }
    }

    public function verifyUserTempByMail($userMail){
        return $this
          ->where([
                ['temp_user', '=', 1],
                ['email', '=', $userMail]
          ]);   
    }

    public function saveTempUser($request = []){
        $request['temp_user'] = 1;
        $request['password'] = Hash::make('googleauth');
        
        if($this->fill($request)->save()){  
            return true; 
        }

        return false;
    }
}

<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Providers\RouteServiceProvider;
use Illuminate\Foundation\Auth\AuthenticatesUsers;
use Illuminate\Http\Request;
use App\Http\Requests\LoginRequest;
use Auth;
use App\Models\User;

class LoginController extends Controller
{
    /**
     * @return void
     */
    public function __construct()
    {
        $this->middleware('guest')->except('logout');
    }

    protected function renderFormLogin(){
        return view('auth.login');
    }

    protected function authenticateUser(LoginRequest $request){
        $userModel = new User;
        $credentials = $request->only('email', 'password');

        if(!Auth::guard('web')->attempt($credentials)){
            return [
                'error' => true,
                'msg' => 'Credenciais incorretas'
            ];
        }else{
            $this->setLastLogin(Auth::user());

            return response()->json([
                'error' => false,
                'msg' => 'Successful auth'
            ]);
        }
    }

    protected function authenticateUserAPI(Request $request){
        $credentials = $request->only('email', 'password');

        if(!$token = auth()->guard('api')->attempt($credentials)){
            return response()->json(['Credenciais Incorretas'], 401);
        }
        
        return response()->json([
            'token' => $token,
            'dataUser' => Auth::guard('api')->user()
        ]);
    }

    protected function userWithGoogle(Request $request){
        $user = new User;

        try{
            $credentials = ['email' => $request->email, 'password' => 'googleauth', 'temp_user' => 1];
            
            if($user->verifyUserTempByMail($request->email)->exists()){
                if($this->authenticateTempUser($credentials)){
                    $response = [
                        'error' => false,
                        'msg' => 'Autenticado com sucesso'
                    ];
                }else{
                    $response = [
                        'error' => true,
                        'msg' => 'Não foi possível autenticar'
                    ];
                }

                return response()->json($response);
            }

            if($user->saveTempUser($request->all())){
                if($this->authenticateTempUser($credentials)){
                    $response = [
                        'error' => false,
                        'msg' => 'Autenticado com sucesso'
                    ];
                }else{
                    $response = [
                        'error' => true,
                        'msg' => 'Não foi possível autenticar'
                    ];
                }

                return response()->json($response);
            }
        }catch(\Exception $error){
            return response()->json([
                'error' => true,
                'msg' => 'Não foi possível efetuar a autenticação',
                'error_feedback' => $error->getMessage()
            ]);
        }
    }

    protected function authenticateTempUser($credentials){
        if(Auth::guard('web')->attempt($credentials)){
            $this->setLastLogin(Auth::user());
            return true;
        }else{
            return false;
        }
    }

    protected function setLastLogin($user){
        $modelUser = new User;

        $user = $modelUser->find($user->id);

        $user->last_login = date('Y-m-d H:i:s');
        $user->save();
    }

    protected function logout(){
        $objUser = new User;
        $sessionUser = Auth::user();
        
        $objUser->deleteTempUser($sessionUser);
        Auth::logout();
        return response()->json(['logout' => true]);
    }

    protected function apiLogout(){
        auth('api')->logout();
        return response()->json(['logout' => true]);
    }
}

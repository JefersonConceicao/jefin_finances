<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Providers\RouteServiceProvider;
use Illuminate\Foundation\Auth\AuthenticatesUsers;
use Illuminate\Http\Request;
use App\Http\Requests\LoginRequest;
use Auth;

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
        $credentials = $request->only('email', 'password');
        
        if(!Auth::attempt($credentials)){
            return [
                'error' => true,
                'msg' => 'Credenciais incorretas'
            ];
        }else{
            return response()->json([
                'error' => false,
                'msg' => 'Successful auth'
            ]);
        }
    }

    protected function authenticateJWT(){
        
    }

    protected function logout(){
        Auth::logout();
        return redirect('/');
    }
}

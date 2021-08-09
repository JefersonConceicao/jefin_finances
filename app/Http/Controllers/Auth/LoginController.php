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

        if(!Auth::guard('web')->attempt($credentials)){
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

    protected function logout(){
        Auth::logout();
        return redirect('/');
    }

    protected function apiLogout(){
        auth('api')->logout();
        return response()->json(['logout' => true]);
    }
}

<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Providers\RouteServiceProvider;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Foundation\Auth\RegistersUsers;
use Illuminate\Support\Facades\Validator;

//REQUEST 
use App\Http\Requests\RegisterRequest;
//MODEL
use App\Models\User;

class RegisterController extends Controller
{
    /**
     * @return void
     */
    public function __construct()
    {
        $this->middleware('guest');
    }

    protected function renderFormSignUp(){
        return view('auth.register');
    }

    /**
     * Responsavel por criar um novo usuário no sistema.
     * 
     * @param Illuminate\Http\Request;
     * @return Illuminate\Http\Response;
     */
    protected function registerUser(RegisterRequest $request)
    {   
       $user = new User;
       
       $data = $user->signUpUser($request->all());
       return response()->json($data, $data['error'] ? 500 : 200);
    }
}

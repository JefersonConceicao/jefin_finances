<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Providers\RouteServiceProvider;
use Illuminate\Foundation\Auth\ResetsPasswords;

use App\Http\Requests\ForgotPasswordRequest;
use Illuminate\Http\Request;
use App\Models\User;

class ResetPasswordController extends Controller
{
    public function renderViewResetPassword($token){
        return view('auth.reset_password')->with('token', $token);
    }

    public function resetPassword(ForgotPasswordRequest $request){
        $user = new User;

        $data = $user->resetPassword($request->all());
        return response()->json($data);
    }
}

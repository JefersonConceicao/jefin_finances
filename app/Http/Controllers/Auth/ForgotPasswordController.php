<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;

use App\Mail\ForgotPasswordMail;
use App\Http\Requests\ForgotPasswordRequest;
use App\Models\User;

class ForgotPasswordController extends Controller
{
    public function renderViewForgotPassword(){
        return view('auth.forgot_password');
    }

    public function sendMailForgotPassword(ForgotPasswordRequest $request){
        $user = new User;
        
        $tokenSaved = $user->saveTokenResetPassword($request->email);

        if(!$tokenSaved){
            return response()->json([
                'error' => true,
                'msg' => 'Não foi possível enviar o e-mail, tente de novo.'
            ]);
        }

        Mail::to($request->email)->send(new ForgotPasswordMail($tokenSaved));

        return response()->json([
            'error' => false,
            'msg' => 'Um link de recuperação foi enviado para o seu e-mail.'
        ]);
    }
}

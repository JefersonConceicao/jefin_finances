<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class ForgotPasswordMail extends Mailable
{
    use Queueable, SerializesModels;
    
    private $token;

    public function __construct($tokenSaved)
    {
       $this->token = $tokenSaved; 
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        $linkMail = config('app.url')."/forgotChangePassword/$this->token";

        return $this
            ->subject('Recuperação de Senha')
            ->view('mails.forgot_password_mail')
            ->with('linkMail', $linkMail);
    }
}

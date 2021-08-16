@extends('layout.master')
<div id="auth">
    <div class="row h-100">
        <div class="col-lg-5 col-12">
            <div id="auth-left">
                <h3 class="auth-title"> Esqueci minha senha </h3>
                <p class="auth-subtitle mb-5"> 
                    Informe seu e-mail e enivaremos um link de recuperação
                </p>
                <form id="forgotPassword">  
                    <div class="form-group position-relative has-icon-left mb-4">
                        <input 
                            type="email" 
                            name="email"
                            class="form-control form-control-xl"
                            placeholder="Digite seu e-mail aqui"
                        />
                        <div class="form-control-icon">
                            <i class="bi bi-envelope"> </i>
                        </div>

                        <div class="error_feedback"> </div>
                    </div>
                    <button class="btn btn-primary btn-block btn-lg shadow-lg mt-5">
                        Enviar
                    </button>
                </form>

                <div class="text-center mt-5 text-lg fs-4">
                    <p class="text-gray-600">
                        Lembra da sua conta? 
                        <a href="/" class="font-bold"> Fazer Login </a>
                    </p>
                </div>  
            </div>
        </div>
        <div class="col-lg-7 d-none d-lg-block">
            <div id="auth-right"> </div>
        </div>
    </div>
</div>


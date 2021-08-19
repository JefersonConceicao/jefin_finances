@extends('layout.master')
@section('content')
    <div id="auth">
        <div class="row h-100">
            <div class="col-lg-5 col-12">
                <div id="auth-left">
                    <h1 class="auth-title"> Finance$ </h1>
                    <p class="auth-subtitle mb-5"> Login | Preencha suas credenciais. </p>

                    <form id="formLoginUser">
                        <div class="form-group position-relative has-icon-left mb-4">
                            <input name="email" type="email" class="form-control form-control-xl" placeholder="Preencha seu e-mail">
                            <div class="form-control-icon">
                                <i class="bi bi-person"></i>
                            </div>

                            <div class="error_feedback"> </div>
                        </div>
                        <div class="form-group position-relative has-icon-left mb-4">
                            <input name="password" type="password" class="form-control form-control-xl" placeholder="Preencha sua senha">
                            <div class="form-control-icon">
                                <i class="bi bi-shield-lock"></i>
                            </div>

                            <div class="error_feedback"> </div>
                        </div>

                        <div class="row">
                            <div class="col-md-12">
                                <button class="btn btn-primary btn-block btn-lg shadow-lg mt-5">Log in</button>
                            </div>
                        </div>
                    </form>
                    <hr/>
                    <div class="row">
                        <p class="text-center"> Outras formas de acesso: </p>
                    
                        <div class="col-md-6">
                            <button class="btn btn-default btn-block btn-lg shadow-lg customGPlusSignIn" id="loginWithGoogle">
                                <i class="fab fa-google"> </i> <b> Google </b>
                            </button>
                        </div>
                    </div>
                
                    <div class="text-center mt-5 text-lg fs-4">
                        <p class="text-gray-600"> 
                            Não tem uma conta? 
                            <a href="/signup" class="font-bold">Cadastre-se</a>.
                        </p>
                        <p><a class="font-bold" href="/forgotPassword"> Esqueci minha senha</a>.</p>
                    </div>
                </div>
            </div>
            <div class="col-lg-7 d-none d-lg-block">
                <div id="auth-right"> </div>
            </div>
        </div>
    </div>
@endsection
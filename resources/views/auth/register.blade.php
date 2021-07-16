@extends('layout.master')
@section('content')
    <div id="auth">
        <div class="row h-100">
            <div class="col-lg-5 col-12">
                <div id="auth-left">
                    <h1 class="auth-title"> Cadastre-se </h1>
                    <p class="auth-subtitle mb-5">
                        Preencha os campos corretamente 
                    </p> 
                    <form id="formAuthRegister">  
                        <div class="form-group position-relative mb-4">
                            <input 
                                type="text"
                                class="form-control form-control-xl"
                                name="name"
                                placeholder="Preencha seu nome *" 
                            />
                            
                            <div class="error_feedback"> </div>
                        </div>
                        <div class="form-group position-relative mb-4">
                            <input 
                                type="text" 
                                name="last_name"
                                placeholder="Preencha seu sobrenome *"
                                class="form-control form-control-xl"
                            />
                
                            <div class="error_feedback"> </div>
                        </div>
                        <div class="form-group position-relative mb-4">
                            <input 
                                type="email"
                                class="form-control form-control-xl"
                                name="email"
                                placeholder="Informe seu e-mail *"
                            />
                         
                            <div class="error_feedback"> </div>
                        </div>
                        <div class="form-group position-relative mb-4">
                            <input 
                                type="password"
                                name="password"
                                class="form-control form-control-xl"
                                placeholder="Informe uma senha *"
                                autocomplete="off"
                            />
                        
                            <div class="error_feedback"> </div>
                        </div>
                        <div class="form-group position-relative mb-4">
                            <input 
                                type="password"
                                name="password_confirmation"
                                class="form-control form-control-xl"
                                placeholder="Confirme sua senha *"
                                autocomplete="off"
                            />
                            
                            <div class="error_feedback"> </div>
                        </div>  
                        <button class="btn btn-primary btn-block btn-lg shadow-lg mt-5">
                            Cadastrar
                        </button>
                    </form>
                    <div class="text-center mt-5 text-lg fs-4">
                        <p class="text-gray-600">
                            Já tem uma conta? 
                            <a href="/" class="font-bold"> 
                                Entrar 
                            </a>
                        </p>
                    </div>
                </div>
            </div>
            <div class="col-lg-7 d-none d-lg-block">
                <div id="auth-right"> </div>
            </div>
        </div>
    </div>
@endsection

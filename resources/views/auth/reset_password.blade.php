@extends('layout.master')
<div id="auth">
    <div class="row h-100">
        <div class="col-lg-5 col-12">
            <div id="auth-left">
                <h3 class="auth-title"> Nova Senha </h3>
                <p class="auth-subtitle mb-5"> 
                    Cadastre uma nova senha
                </p>
                <form id="resetPassword"> 
                    <div class="form-group position-relative has-icon-left mb-4">
                        <input type="hidden" name="token" value="{{$token}}" />
                        <input 
                            type="password" 
                            name="new_password"
                            class="form-control form-control-xl"
                            placeholder="Preencha sua nova senha"
                        />
                        <div class="form-control-icon">
                            <i class="bi bi-lock"> </i>
                        </div>

                        <div class="error_feedback"> </div>
                    </div>
                    <div class="form-group position-relative has-icon-left mb-4">
                        <input 
                            type="password" 
                            name="confirm_new_password"
                            class="form-control form-control-xl"
                            placeholder="Confirme sua nova senha "
                        />
                        <div class="form-control-icon">
                            <i class="bi bi-lock"> </i>
                        </div>

                        <div class="error_feedback"> </div>
                    </div>
                    <button class="btn btn-primary btn-block btn-lg shadow-lg mt-5">
                        Enviar
                    </button>
                </form>
            </div>
        </div>
        <div class="col-lg-7 d-none d-lg-block">
            <div id="auth-right"> </div>
        </div>
    </div>
</div>



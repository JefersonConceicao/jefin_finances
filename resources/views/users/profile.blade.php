@extends('layout.page')
@section('content_header')
    <h3> Meu Perfil </h3>
    <p class="text-subtitle"> Gerencie suas informações </p>
@endsection
@section('content-page')
    <div class="row">
        <!-- LEFT PANEL -->
        <div class="col-md-4">
            <div class="card">
                <div class="card-header">
                    <div class="avatar avatar-x1 me-3">
                        <img 
                            src="https://img1.pnghut.com/10/13/18/wqCgb35w2c/user-drawing-avatar-blue-orange.jpg"
                        >
                    </div>
                    <span> <b> {{ $user->name." ".$user->last_name}} </b> </span>
                </div>
                <hr/>
                <div class="card-content">
                    <div class="card-body">
                        <div class="list-group text-center">
                            <span class="list-group-item"> Status:  
                                <label class="badge bg-{{$user->ativo == 1 ? "success": "danger"}}">
                                    {{ $user->ativo == 1 ? "Ativo" : "Inativo" }} 
                                </label> 
                            </span>
                            <span class="list-group-item"> Criado em: <b> {{ converteData($user->created_at, 'd/m/Y') }} </b> </span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <!-- END LEFT PANEL -->

        <!-- RIGHT PANEL -->
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">
                    <ul class="nav nav-tabs" id="tabProfile" role="tablist"> 
                        <li class="nav-item" role="presentation"> 
                            <a  
                            class="nav-link active" 
                            id="infoTab" 
                            href="#informacoes" 
                            role="tab"
                            aria-selected="true"
                            data-bs-toggle="tab"
                            aria-controls="informacoes"
                            > 
                                Informações Gerais
                            </a>
                        </li>
                        <li class="nav-item" role="presentation"> 
                            <a 
                            class="nav-link" 
                            id="passwordTab" 
                            href="#trocarSenha"
                            aria-controls="trocarSenha"
                            role="tab"
                            data-bs-toggle="tab"
                            aria-selected="false"
                            > 
                                Alterar senha
                            </a>
                        </li>
                    </ul>
                </div>
                <div class="card-content">
                    <div class="card-body">
                        <div class="tab-content" id="contenProfile">
                            <div class="tab-pane fade active show" id="informacoes" role="tabpanel">
                                <form id="profileUpdate">
                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label> Nome </label> 
                                                <input 
                                                    type="text" 
                                                    name="name" 
                                                    class="form-control" 
                                                    value="{{$user->name}}"
                                                />

                                                <div class="error_feedback"> </div>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label> Sobrenome </label>
                                                <input 
                                                    type="text" 
                                                    name="last_name" 
                                                    class="form-control"
                                                    value="{{ $user->last_name }}"
                                                /> 

                                                <div class="error_feedback"> </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="col-md-12">
                                            <div class="form-group">
                                                <label> Email </label> 
                                                <input 
                                                    type="email" 
                                                    name="email" 
                                                    class="form-control"
                                                    value="{{ $user->email }}"
                                                />

                                                <div class="error_feedback"> </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="col-md-12">
                                            <button 
                                                type="submit" 
                                                class="btn btn-primary rounded-pill" 
                                                style="width:100%; margin-top:2%;"
                                            > 
                                                Salvar
                                            </button>
                                        </div>  
                                    </div>
                                </form>
                            </div>
                            <div class="tab-pane fade show" id="trocarSenha" role="tabpanel">
                                <form id="changePassword">
                                    <div class="row">
                                        <div class="col-md-12">
                                            <div class="form-group">
                                                <label> Senha atual </label>
                                                <input 
                                                    type="password"
                                                    name="last_password"
                                                    class="form-control"
                                                />

                                                <div class="error_feedback"> </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label> Nova senha </label>
                                                <input 
                                                    type="password"
                                                    name="new_password"
                                                    class="form-control"
                                                />

                                                <div class="error_feedback"> </div>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <label>  Confirmar nova senha </label>
                                            <input 
                                                type="password"
                                                name="confirm_new_password"
                                                class="form-control"
                                            />

                                            <div class="error_feedback"> </div>
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="col-md-12">
                                            <button class="btn btn-primary rounded-pill" style="width:100%; margin-top:2%;">
                                                Alterar senha
                                            </button>
                                        </div>
                                    </div>
                                </form> 
                            </div>  
                        </div>
                    </div>
                </div>  
            </div>
        </div>
        <!--END RIGHT PANEL -->
    </div>
@endsection
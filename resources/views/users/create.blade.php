@extends('layout.modal')
@section('modal-header')
    Novo Usuário 
@endsection
@section('modal-content')
    <form id="formAddUser">
        <div class="row">
            <div class="col-md-6">
                <div class="form-group">
                    <label> Nome <span class="required"> * </span> </label>
                    <input 
                        name="name"
                        type="text"
                        class="form-control"
                    /> 
                </div>
            </div>
            <div class="col-md-6">
                <div class="form-group">
                    <label> Sobrenome <span class="required"> * </span> </label>
                    <input 
                        name="last_name"
                        type="text"
                        class="form-control"
                    />
                </div>
            </div>
        </div>
        <div class="row">
            <div class="col-md-12">
                <div class="form-group">
                    <label> E-mail <span class="required"> * </span> </label>
                    <input 
                        name="email"
                        type="email"
                        class="form-control"
                    />
                </div>
            </div>
        </div>
        <div class="row">
            <div class="col-md-6">
                <div class="form-group">
                    <label> Senha  <span class="required"> * </span> </label>
                    <input 
                        name="password"
                        type="password"
                        class="form-control"
                    />
                </div>
            </div>
            <div class="col-md-6">
                <div class="form-group">
                    <label> Confirmação de Senha  <span class="required"> * </span> </label>
                    <input 
                    name="password_confirmation"
                    type="password"
                    class="form-control"
                    />
                </div>
            </div>
        </div>
    </form>
@endsection

@section('btn_fechar', 'Fechar')
@section('btn_submit', 'Salvar')
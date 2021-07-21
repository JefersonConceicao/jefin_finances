@extends('layout.modal')
@section('modal-header')
    <i class="fa fa-plus-square"></i> Alterar Usuário 
@endsection
@section('modal-form', 'formEditUser')
@section('modal-content')
    <div class="row">
        <div class="col-md-6">
            <div class="form-group">
                <label> Nome <span class="required"> * </span> </label>
                <input 
                    name="name"
                    type="text"
                    class="form-control"
                    value="{{ $user->name }}"
                /> 

                <div class="error_feedback"> </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="form-group">
                <label> Sobrenome <span class="required"> * </span> </label>
                <input 
                    name="last_name"
                    type="text"
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
                <label> E-mail <span class="required"> * </span> </label>
                <input 
                    name="email"
                    type="email"
                    class="form-control"
                    value="{{ $user->email }}"
                />

                <div class="error_feedback"> </div>
            </div>
        </div>
    </div>
@endsection

@section('btn_fechar', 'Fechar')
@section('btn_submit', 'Salvar')
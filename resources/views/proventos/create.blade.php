@extends('layout.modal')
@section('form-modal', 'formAddProvento')
@section('modal-header')
    <i class="fa fa-plus-square"></i> Cadastrar Provento 
@endsection
@section('modal-content')
    <div class="row">
        <div class="col-md-12">
            <div class="form-group">
                <label> Descrição Provento <span class="required"> * </span> </label>
                <input 
                    name="descricao_provento"
                    type="text"
                    class="form-control"
                />

                <div class="error_feedback"> </div>
            </div>  
        </div>
    </div>
    <div class="row">
        <div class="col-md-6">
            <div class="form-group">
                <label> Valor Provento <span class="required"> * </span> </label>
                <input 
                    name="valor_provento"
                    type="text"
                    class="form-control"
                />

                <div class="error_feedback"> </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="form-group">
                <label> Data Provento <span class="required"> * </span> </label>
                <input 
                    name="data_provento"
                    type="text"
                    class="form-control"
                />

                <div class="error_feedback"> </div>
            </div>
        </div>
    </div>
@endsection

@section('btn_fechar', 'Fechar')
@section('btn_submit', 'Salvar')
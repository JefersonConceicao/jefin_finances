@extends('layout.modal')
@section('modal-form', 'formAddTipoDespesa')
@section('modal-header')
    <i class="fa fa-plus-square"></i> Cadastrar Tipo de Despesa 
@endsection
@section('modal-content')
    <div class="row">
        <div class="col-md-6">
            <div class="form-group">
                <label> Nome <span class="required"> * </span> </label>
                <input 
                    name="nome"
                    type="text"
                    class="form-control"
                    required
                />

                <div class="error_feedback"> </div>
            </div>  
        </div>
        <div class="col-md-6">
            <div class="form-group">
                <label> Ativo <span class="required"> * </span> </label>
                <select class="form-select" name="ativo"> 
                    <option value="1"> Sim  </option>
                    <option value="0"> Não </option>
                </select>   

                <div class="error_feedback"> </div>
            </div>  
        </div>
    </div>
@endsection
@section('btn_fechar', 'Fechar')
@section('btn_submit', 'Salvar')
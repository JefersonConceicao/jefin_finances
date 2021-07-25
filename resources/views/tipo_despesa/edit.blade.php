@extends('layout.modal')
@section('modal-form', 'formEditTipoDespesa')
@section('modal-header')
    <i class="fa fa-plus-square"></i> Alterar Tipo de Despesa 
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
                    value="{{ $tipoDespesa->nome }}"
                    required
                />

                <div class="error_feedback"> </div>
            </div>  
        </div>
        <div class="col-md-6">
            <div class="form-group">
                <label> Ativo <span class="required"> * </span> </label>
                <select class="form-control" name="ativo"> 
                    <option value="1" @if($tipoDespesa->ativo == 1) selected @endif> Sim  </option>
                    <option value="0" @if($tipoDespesa->ativo == 0) selected @endif> Não </option>
                </select>   

                <div class="error_feedback"> </div>
            </div>  
        </div>
    </div>
@endsection
@section('btn_fechar', 'Fechar')
@section('btn_submit', 'Salvar')
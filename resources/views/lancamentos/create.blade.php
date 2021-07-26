@extends('layout.modal')
@section('modal-form', 'formAddLancamento')
@section('modal-header')
    <i class="fa fa-plus-square"></i> Novo Lancamento 
@endsection 
@section('modal-content')
    <div class="row">
        <div class="col-md-8">
            <div class="form-group"> 
                <label> Descrição </label>
                <input 
                    type="text"
                    name="descricao"
                    class="form-control"
                />
                
                <div class="error_feedback"> </div>
            </div>
        </div>
        
        <div class="col-md-4">
            <div class="form-group">
                <label> Despesa </label>
                <select name="despesa" class="form-select">
                    <option value="N" selected> Não </option>
                    <option value="S"> Sim </option>
                </select>
            </div>
        </div>
    </div>
    <div class="row" id="inputDespesa" style="display:none">
        <div class="col-md-12"> 
            <div class="form-group">
                <label> Despesa </label> 
                <select class="form-select" name="despesa_id"> 
                    <option> Nenhum registro encontrado. </option>
                </select>
            </div>
        </div>
    </div>
    <div class="row">
        <div class="col-md-12">
            <div class="form-group">
                <label> Valor <span class="required"> * </span> </label>
                <input 
                    name="valor"
                    type="text"
                    class="form-control decimalValue"
                />

                <div class="error_feedback"> </div>
            </div>
        </div>
    </div>
@endsection

@section('btn_fechar', 'Fechar')
@section('btn_submit', 'Salvar')
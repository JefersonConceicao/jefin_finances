@extends('layout.modal')
@section('modal-form', 'formAddLancamento')
@section('modal-header')
    <i class="fa fa-plus-square"></i> Novo Lancamento 
@endsection 
@section('modal-content')
    <div class="row">
        <div class="col-md-12">
            <div class="form-group"> 
                <label> Descrição <span class="required"> * </span> </label>
                <input 
                    type="text"
                    name="descricao"
                    class="form-control"
                />
                
                <div class="error_feedback"> </div> 
            </div>
        </div>
    </div>
    <div class="row">
        <div class="col-md-6">
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
        <div class="col-md-6">
            <div class="form-group">
                <label> Data Lançamento <span class="required"> * </span> </label>
                <input 
                    name="data_lancamento"
                    type="text"
                    class="form-control datePicker"
                    value={{ date('d/m/Y')}}
                />

                <div class="error_feedback"> </div>
            </div>
        </div>
    </div>
@endsection

@section('btn_fechar', 'Fechar')
@section('btn_submit', 'Salvar')
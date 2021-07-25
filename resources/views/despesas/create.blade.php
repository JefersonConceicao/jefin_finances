@extends('layout.modal')
@section('modal-form', 'formAddDespesas')
@section('modal-header')
    <i class="fa fa-plus-square"></i> Nova Despesa 
@endsection
@section('modal-content')
    <div class="row">
        <div class="col-md-6">
            <div class="form-group">
                <label> Nome Despesa <span class="required"> * </span> </label>
                <input 
                    name="nome_despesa"
                    type="text"
                    class="form-control"
                />

                <div class="error_feedback"> </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="form-group">
                <label> Ativo <span class="required"> * </span> </label>
                <select name="ativo" class="form-select">
                    <option value="1"> Sim </option>
                    <option value="0"> Não </option>
                </select>

                <div class="error_feedback"> </div>
            </div>
        </div>  
    </div>
    <div class="row">
        <div class="col-md-6"> 
            <div class="form-group">
                <label> Tipo de Despesa <span class="required"> * </span> </label>
                <select name="despesa_tipo_id" class="form-select"> 
                    <option value=""> Selecione </option>
                    @foreach($optionsTipoDespesa as $k => $v)
                        <option value="{{ $k }}"> {{ $v }} </option>
                    @endforeach
                </select>

                <div class="error_feedback"> </div>
            </div>
        </div>
        <div class="col-md-6"> 
            <div class="form-group">
                <label> Valor (R$) <span class="required"> * </span> </label>
                <input 
                    name="valor_total"
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
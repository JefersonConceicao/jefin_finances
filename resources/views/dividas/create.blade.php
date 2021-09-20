@extends('layout.modal')
@section('modal-form', 'formAddDebt')
@section('modal-header')
    Nova dívida
@endsection
@section('modal-content')
    <div class="row">
        <div class="col-md-8">
            <div class="form-group">
                <label> Descrição <span class="required"> * </span> </label>
                <input name="descricao_divida" type="text" class="form-control"/>

                <div class="error_feedback"> </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="form-group">
                <label> Data <span class="required"> * </span> </label>
                <input name="data_inicial_divida" type="text" class="form-control datepicker" autocomplete="off"/>

                <div class="error_feedback"> </div>
            </div>
        </div>
    </div>
    <div class="row">
        <div class="col-md-4">
            <div class="form-group">
                <label> Valor <span class="required"> * </span> </label>
                <input name="valor_total"  type="text" class="form-control decimalValue"/>
                
                <div class="error_feedback"> </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="form-group">
                <label> Quantidade parcelas <span class="required"> * </span> </label>
                <input name="qtd_parcela_total" type="number" class="form-control" min="0"/>
                
                <div class="error_feedback"> </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="form-group">
                <label> Valor p/ parcela </label>
                <input name="valor_parcela" type="text" class="form-control decimalValue" />
                
                <div class="error_feedback"> </div>
            </div>  
        </div>
    </div>
@endsection
@section('btn_fechar')
    Fechar
@endsection
@section('btn_submit')
    Salvar
@endsection

@extends('layout.modal')
@section('modal-form', 'formAddProventoFixo')
@section('modal-header')
    <i class="fa fa-plus-square"></i> Novo Provento Fixo
@endsection
@section('modal-content')
    <div class="form-group">
        <label> Descrição <span class="required"> * </span> </label>
        <input name="descricao" type="text" class="form-control" maxlength="255" placeholder="Ex: Salário" />
        <div class="error_feedback"> </div>
    </div>
    <div class="row">
        <div class="col-md-4">
            <div class="form-group">
                <label> Valor Previsto (R$) <span class="required"> * </span> </label>
                <input name="valor" type="text" class="form-control decimalValue" />
                <div class="error_feedback"> </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="form-group">
                <label> Mês de início <span class="required"> * </span> </label>
                <input name="mes_inicio" type="month" class="form-control" value="{{ now()->format('Y-m') }}" />
                <div class="error_feedback"> </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="form-group">
                <label> Mês de fim </label>
                <input name="mes_fim" type="month" class="form-control" />
                <small class="text-muted"> Vazio: sem fim </small>
                <div class="error_feedback"> </div>
            </div>
        </div>
    </div>
    <p class="text-muted mb-0">
        <small>
            <i class="fa fa-info-circle"></i>
            As Ocorrências são gravadas em Proventos, no dia 1º de cada mês da Vigência (12 meses à frente quando não há fim).
        </small>
    </p>
@endsection

@section('btn_fechar', 'Fechar')
@section('btn_submit', 'Salvar')

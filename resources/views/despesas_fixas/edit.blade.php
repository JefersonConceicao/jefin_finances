@extends('layout.modal')
@section('modal-form', 'formEditDespesaFixa')
@section('modal-header')
    <i class="fa fa-edit"></i> Alterar Despesa Fixa
@endsection
@section('modal-content')
    <p class="text-muted">
        Vigência: {{ $despesaFixa->mes_inicio->format('m/Y') }} &ndash;
        {{ $despesaFixa->mes_fim ? $despesaFixa->mes_fim->format('m/Y') : 'sem fim' }}
        &middot; Valor Previsto: R$ {{ convertValorReal($despesaFixa->valor) }}
        &middot; Dia {{ $despesaFixa->dia_vencimento }}
    </p>
    <div class="form-group">
        <label> Nome Despesa <span class="required"> * </span> </label>
        <input
            name="nome_despesa"
            type="text"
            class="form-control"
            maxlength="100"
            value="{{ $despesaFixa->nome_despesa }}"
        />
        <small class="text-muted"> O nome novo vale para as Ocorrências do mês atual em diante. </small>
        <div class="error_feedback"> </div>
    </div>

    <hr />
    <h6> Reajustar </h6>
    <p class="text-muted mb-1"><small> O novo valor e o dia valem do mês escolhido em diante, inclusive nas Ocorrências ajustadas à mão. </small></p>
    <div class="row" id="reajusteDespesaFixa">
        <div class="col-md-4">
            <div class="form-group">
                <label> A partir de </label>
                <input name="a_partir_de" type="month" class="form-control" value="{{ now()->format('Y-m') }}" />
                <div class="error_feedback"> </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="form-group">
                <label> Valor Previsto (R$) </label>
                <input name="valor" type="text" class="form-control decimalValue" value="{{ convertValorReal($despesaFixa->valor) }}" />
                <div class="error_feedback"> </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="form-group">
                <label> Dia de vencimento </label>
                <input name="dia_vencimento" type="number" min="1" max="31" class="form-control" value="{{ $despesaFixa->dia_vencimento }}" />
                <div class="error_feedback"> </div>
            </div>
        </div>
        <div class="col-md-12">
            <button type="button" class="btn btn-outline-primary rounded-pill" id="btnReajustarDespesaFixa"> Reajustar </button>
        </div>
    </div>

    <hr />
    <h6> Encerrar </h6>
    <p class="text-muted mb-1"><small> A Vigência termina no mês escolhido; as Ocorrências depois dele são excluídas. </small></p>
    <div class="row" id="encerramentoDespesaFixa">
        <div class="col-md-8">
            <div class="form-group">
                <label> Último mês </label>
                <input name="mes_fim" type="month" class="form-control" value="{{ now()->format('Y-m') }}" />
                <div class="error_feedback"> </div>
            </div>
        </div>
        <div class="col-md-4" style="margin-top:6%;">
            <button type="button" class="btn btn-outline-danger rounded-pill" id="btnEncerrarDespesaFixa"> Encerrar </button>
        </div>
    </div>
@endsection

@section('btn_fechar', 'Fechar')
@section('btn_submit', 'Salvar nome')

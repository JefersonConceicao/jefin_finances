@extends('layout.modal')
@section('modal-form', 'formEditProventoFixo')
@section('modal-header')
    <i class="fa fa-edit"></i> Alterar Provento Fixo
@endsection
@section('modal-content')
    <p class="text-muted">
        Vigência: {{ $proventoFixo->mes_inicio->format('m/Y') }} &ndash;
        {{ $proventoFixo->mes_fim ? $proventoFixo->mes_fim->format('m/Y') : 'sem fim' }}
        &middot; Valor Previsto: R$ {{ convertValorReal($proventoFixo->valor) }}
    </p>
    <div class="form-group">
        <label> Descrição <span class="required"> * </span> </label>
        <input name="descricao" type="text" class="form-control" maxlength="255" value="{{ $proventoFixo->descricao }}" />
        <small class="text-muted"> A descrição nova vale para as Ocorrências do mês atual em diante. </small>
        <div class="error_feedback"> </div>
    </div>

    <hr />
    <h6> Reajustar </h6>
    <p class="text-muted mb-1"><small> O novo valor vale do mês escolhido em diante, inclusive nos meses ajustados à mão. </small></p>
    <div class="row" id="reajusteProventoFixo">
        <div class="col-md-5">
            <div class="form-group">
                <label> A partir de </label>
                <input name="a_partir_de" type="month" class="form-control" value="{{ now()->format('Y-m') }}" />
                <div class="error_feedback"> </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="form-group">
                <label> Valor Previsto (R$) </label>
                <input name="valor" type="text" class="form-control decimalValue" value="{{ convertValorReal($proventoFixo->valor) }}" />
                <div class="error_feedback"> </div>
            </div>
        </div>
        <div class="col-md-3" style="margin-top:6%;">
            <button type="button" class="btn btn-outline-primary rounded-pill" id="btnReajustarProventoFixo"> Reajustar </button>
        </div>
    </div>

    <hr />
    <h6> Encerrar </h6>
    <p class="text-muted mb-1"><small> A Vigência termina no mês escolhido; as Ocorrências depois dele são excluídas. </small></p>
    <div class="row" id="encerramentoProventoFixo">
        <div class="col-md-8">
            <div class="form-group">
                <label> Último mês </label>
                <input name="mes_fim" type="month" class="form-control" value="{{ now()->format('Y-m') }}" />
                <div class="error_feedback"> </div>
            </div>
        </div>
        <div class="col-md-4" style="margin-top:6%;">
            <button type="button" class="btn btn-outline-danger rounded-pill" id="btnEncerrarProventoFixo"> Encerrar </button>
        </div>
    </div>
@endsection

@section('btn_fechar', 'Fechar')
@section('btn_submit', 'Salvar descrição')

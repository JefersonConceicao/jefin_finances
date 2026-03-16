@extends('layout.modal')
@section('modal-form', 'formEditProvento')
@section('modal-header')
    <i class="fa fa-edit"></i> Alterar Provento
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
                    value="{{ $provento->descricao_provento }}"

                />

                <div class="error_feedback"> </div>
            </div>
        </div>
    </div>
    <div class="row">
        <div class="col-md-12">
            <div class="form-group">
                <label> Tipo de Período </label>
                <div class="form-check">
                    <input class="form-check-input" type="radio" name="period_type" id="period_type_especifico" value="especifico">
                    <label class="form-check-label" for="period_type_especifico"> Especificar Periodo </label>
                </div>
                <div class="form-check">
                    <input class="form-check-input" type="radio" name="period_type" id="period_type_anual" value="anual">
                    <label class="form-check-label" for="period_type_anual"> Periodo anual </label>
                </div>

                <div class="error_feedback"> </div>
            </div>
        </div>
    </div>

    <div class="row" id="rowPeriodoEspecifico" style="display:none;">
        <div class="col-md-6">
            <div class="form-group">
                <label> Periodo Início <span class="required"> * </span> </label>
                <input
                    name="periodo_inicio"
                    type="text"
                    class="form-control datepicker"
                />
                <div class="error_feedback"> </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="form-group">
                <label> Periodo Fim <span class="required"> * </span> </label>
                <input
                    name="periodo_fim"
                    type="text"
                    class="form-control datepicker"
                />
                <div class="error_feedback"> </div>
            </div>
        </div>
    </div>

    <div class="row" id="rowValorProvento">
        <div class="col-md-6">
            <div class="form-group">
                <label> Valor Provento R$<span class="required"> * </span> </label>
                <input
                    name="valor_provento"
                    type="text"
                    class="form-control decimalValue"
                    value="{{ $provento->valor_provento }}"
                />

                <div class="error_feedback"> </div>
            </div>
        </div>
    </div>
    <div class="row" id="rowDataProvento">
        <div class="col-md-6">
            <div class="form-group">
                <label> Data Provento <span class="required"> * </span> </label>
                <input
                    name="data_provento"
                    type="text"
                    class="form-control datepicker"
                    value="{{ converteData($provento->data_provento,'d/m/Y') }}"
                />

                <div class="error_feedback"> </div>
            </div>
        </div>
    </div>
@endsection

@section('btn_fechar', 'Fechar')
@section('btn_submit', 'Salvar')

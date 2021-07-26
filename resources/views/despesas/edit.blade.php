@extends('layout.modal')
@section('modal-form', 'formEditDespesas')
@section('modal-header')
    <i class="fa fa-plus-square"></i> Alterar Despesa 
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
                    value="{{ $despesa->nome_despesa }}"
                    required
                />

                <div class="error_feedback"> </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="form-group">
                <label> Ativo <span class="required"> * </span> </label>
                <select name="ativo" class="form-select">
                    <option value="1" @if($despesa->ativo == 1) selected @endif > Sim </option>
                    <option value="0" @if($despesa->ativo == 0) selected @endif> Não </option>
                </select>

                <div class="error_feedback"> </div>
            </div>
        </div>  
    </div>
    <div class="row">
        <div class="col-md-12"> 
            <div class="form-group">
                <label> Tipo de Despesa <span class="required"> * </span> </label>
                <select name="despesa_tipo_id" class="form-select"> 
                    <option value=""> Selecione </option>
                    @foreach($optionsTipoDespesa as $k => $v)
                        <option value="{{ $k }}" @if($k === $despesa->despesa_tipo_id) selected @endif > 
                            {{ $v }} 
                        </option>
                    @endforeach
                </select>

                <div class="error_feedback"> </div>
            </div>
        </div>
    </div> 
    <div class="row">
        <div class="col-md-6"> 
            <div class="form-group">
                <label> Valor (R$) <span class="required"> * </span> </label>
                <input 
                    name="valor_total"
                    type="text"
                    class="form-control decimalValue"
                    value="{{ convertValorReal($despesa->valor_total) }}"
                />

                <div class="error_feedback"> </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="form-group">
                <label> Data Criação <span class="required">  * </span> </label>
                <input 
                    name="created_at"
                    type="text"
                    class="form-control datepicker"
                    value="{{ converteData($despesa->created_at, 'd/m/Y') }}"
                />

                <div class="error_feedback"> </div>
            </div>
        </div>
    </div>  
@endsection

@section('btn_fechar', 'Fechar')
@section('btn_submit', 'Salvar')
@extends('layout.modal')
@section('modal-header')
    Detalhes
@endsection
@section('modal-content')
<div class="row">
    <div class="col-md-8">
        <div class="form-group">
            <label> Descrição </label>
            <input 
                name="descricao_divida" 
                type="text" 
                class="form-control" 
                value="{{$divida->descricao_divida}}"
                disabled
            />
        </div>
    </div>
    <div class="col-md-4">
        <div class="form-group">
            <label> Data Inicial</label>
            <input 
                name="data_inicial_divida" 
                type="text" 
                class="form-control datepicker" 
                autocomplete="off"
                value="{{ converteData($divida->data_inicial_divida, 'd/m/Y')}}"
                disabled
            />
        </div>
    </div>
</div>
<div class="row">
    <div class="col-md-4">
        <div class="form-group">
            <label> Valor </label>
            <input 
                name="valor_total"  
                type="text" 
                class="form-control decimalValue"
                value="{{convertValorReal($divida->valor_total)}}"
                disabled
            />
        </div>
    </div>
    <div class="col-md-4">
        <div class="form-group">
            <label> Quantidade parcelas </label>
            <input 
                name="qtd_parcela_total" 
                type="number" 
                class="form-control" 
                min="0"
                value="{{$divida->qtd_parcela_total}}"
                disabled    
            />
        </div>
    </div>
    <div class="col-md-4">
        <div class="form-group">
            <label> Valor p/ parcela </label>
            <input 
                name="valor_parcela" 
                type="text" 
                class="form-control decimalValue"
                value="{{ convertValorReal($divida->valor_parcela) }}"
                disabled
            />
        </div>  
    </div>
</div>
<div class="row">
    <div class="col-md-4">
        <div class="form-group">
            <label> Parcelas pagas </label>
            <input 
                name="qtd_parcela_parcial"
                type="text"
                class="form-control"
                value={{ $divida->qtd_parcela_parcial }}
                disabled
            />
        </div>
    </div>
    <div class="col-md-4">
        <div class="form-group">
            <label> Valor liquidado </label>
            <input 
                name="valor_parcial"
                type="text"
                class="form-control"
                value={{ convertValorReal($divida->valor_parcial)}}
                disabled
            />
        </div>
    </div>
    <div class="col-md-4">
        <div class="form-group">
            <label> Data Final </label>
            <input 
                name="data_final_divida" 
                type="text" 
                class="form-control datepicker" 
                autocomplete="off"
                value="{{ converteData($divida->data_fim_divida, 'd/m/Y')}}"
                disabled
            />
        </div>
    </div>
</div>
@endsection
@section('btn_fechar')
    Fechar 
@endsection
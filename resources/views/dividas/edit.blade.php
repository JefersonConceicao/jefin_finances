@extends('layout.modal')
@section('modal-form', 'formUpdateDebt')
@section('modal-header')
    Alterar dívida
@endsection

@section('modal-content')
    <div class="row">
        <div class="col-md-12">
            <div class="form-group">
                <label> Descrição <span class="required"> * </span> </label>
                <input 
                    name="descricao_divida" 
                    type="text" 
                    class="form-control" 
                    value={{ $divida->descricao_divida }}
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
                    name="valor_total"  
                    type="text" 
                    class="form-control decimalValue"
                    value={{ convertValorReal($divida->valor_total) }}
                />
                
                <div class="error_feedback"> </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="form-group">
                <label> Quantidade parcelas <span class="required"> * </span> </label>
                <input 
                    name="qtd_parcela_total" 
                    type="number" 
                    class="form-control" 
                    min="0"
                    value={{ $divida->qtd_parcela_total }}    
                />
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
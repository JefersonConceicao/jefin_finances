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
                <label> Vencimento <span class="required">  * </span> </label>
                {{-- REGISTRO ANTIGO SEM VENCIMENTO: SUGERE A DATA DE CADASTRO, QUE É A SUA COMPETÊNCIA ATUAL --}}
                <input
                    name="data_vencimento"
                    type="text"
                    class="form-control datepicker"
                    value="{{ converteData($despesa->data_vencimento ?: $despesa->created_at, 'd/m/Y') }}"
                />

                <div class="error_feedback"> </div>
            </div>
        </div>
    </div>  
    @if (!empty($despesa->despesa_fixa_id))
        <div class="row">
            <div class="col-md-12">
                <div class="form-group">
                    <label> Aplicar a </label>
                    <div class="form-check">
                        <input class="form-check-input" type="radio" name="escopo" id="escopoSoEsta" value="so_esta" checked />
                        <label class="form-check-label" for="escopoSoEsta"> Só esta </label>
                    </div>
                    <div class="form-check">
                        <input class="form-check-input" type="radio" name="escopo" id="escopoSeguintes" value="seguintes" />
                        <label class="form-check-label" for="escopoSeguintes">
                            Esta e as seguintes <small class="text-muted">(Reajuste do valor e do dia de vencimento)</small>
                        </label>
                    </div>
                </div>
            </div>
        </div>
    @endif
@endsection

@section('btn_fechar', 'Fechar')
@section('btn_submit', 'Salvar')
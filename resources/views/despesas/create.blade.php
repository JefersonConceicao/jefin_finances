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
        <div class="col-md-12"> 
            <div class="form-group">
                <label> Tipo de Despesa <span class="required"> * </span> </label>
                <select name="despesa_tipo_id" class="form-select"> 
                    <option value=""> Selecione </option>
                    @foreach($optionsTipoDespesa as $k => $v)
                        <option value="{{ $k }}"> {{ $v }} </option>
                    @endforeach
                </select>
                <div> 
                    <label class="float-end"> 
                        <a 
                            role="button"
                            href="#formAddTipoDespesa" 
                            id="newTipoDespesa" 
                            data-bs-toggle="collapse"
                            aria-expanded="true"
                            aria-controls="formAddTipoDespesa"
                        > 
                            Adicionar tipo de despesa
                        </a> 
                    </label>
                </div>
                <div class="error_feedback"> </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-md-12">
            <div class="collapse" id="formAddTipoDespesa" style="border: 1px solid black; margin:10px;">   
                <div class="card">
                    <h5 class="card-header">
                        Adicionar tipo de despesa 
                    </h5>
                    <div class="card-body">
                        <div class="row">
                            <form id="addTiposDespesas">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label> Tipo despesa </label>
                                        <input 
                                            name="nome"
                                            class="form-control"
                                        />

                                        <div class="error_feedback"> </div> 
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label> Ativo </label>
                                        <select class="form-select" name="ativo">  
                                            <option value="1"> Sim </option>
                                            <option value="0"> Não </option>     
                                        </select>
                                        <div class="error_feedback"> </div>
                                    </div>
                                </div>
                                <div class="col-md-2" style="margin-top:3%;">
                                    <button class="btn btn-success" type="button"> Salvar </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>  
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
                    value="{{ date('d/m/Y') }}"
                />

                <div class="error_feedback"> </div>
            </div>
        </div>
    </div> 
@endsection

@section('btn_fechar', 'Fechar')
@section('btn_submit', 'Salvar')
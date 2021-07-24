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
                    required
                />

                <div class="error_feedback"> </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="form-group">
                <label> Ativo <span class="required"> * </span> </label>
                <select name="ativo" class="form-select">
                    <option value="S"> Sim </option>
                    <option value="N"> Não </option>
                </select>

                <div class="error_feedback"> </div>
            </div>
        </div>  
    </div>
    <div class="row">

        
    </div>
@endsection

@section('btn_fechar', 'Fechar')
@section('btn_submit', 'Salvar')
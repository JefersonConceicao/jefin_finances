@extends('layout.modal')
@section('modal-form', 'formEditDespesaFixa')
@section('modal-header')
    <i class="fa fa-edit"></i> Alterar Despesa Fixa
@endsection
@section('modal-content')
    @include('despesas_fixas._form', ['despesaFixa' => $despesaFixa])
@endsection

@section('btn_fechar', 'Fechar')
@section('btn_submit', 'Salvar')

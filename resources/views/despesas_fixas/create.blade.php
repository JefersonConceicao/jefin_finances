@extends('layout.modal')
@section('modal-form', 'formAddDespesaFixa')
@section('modal-header')
    <i class="fa fa-plus-square"></i> Nova Despesa Fixa
@endsection
@section('modal-content')
    @include('despesas_fixas._form')
@endsection

@section('btn_fechar', 'Fechar')
@section('btn_submit', 'Salvar')

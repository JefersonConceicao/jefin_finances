@extends('layout.page')
@section('content_header')
    <h3> Oi {{ Auth::user()->name }} </h3>
    <p class="text-subtitle"> Estamos desenvolvendo um dashboard para você, aguarde atualizações... </p>
@endsection

@section('content-title')
   <p> Ultimos gastos </p>
@endsection
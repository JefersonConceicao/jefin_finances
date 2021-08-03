@extends('layout.page')
@section('content_header')
    <h3> Olá, {{ Auth::user()->name }} </h3>
    <p class="text-subtitle"> Este é o seu dashboard </p>
@endsection

@section('content-page')
    <section class="row" id="widgets">
        <div class="col-12 col-lg-9">  
            <div class="row">
                <div class="col-6 col-lg-4 col-md-6">
                    <div class="card">
                        <div class="card-body">
                            <div class="row">   
                                <div class="col-md-4">
                                    <div class="stats-icon purple">
                                        <i class="fa fa-list" style="color:white;"> </i>
                                    </div>
                                </div>
                                <div class="col-md-8">
                                    <small> Total de proventos:</small>
                                    <h6> {{"R$ ".convertValorReal($totalProventos) }} </h6>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-6 col-lg-4 col-md-6">
                    <div class="card">
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-4">
                                    <div class="stats-icon blue">
                                        <i class="fa fa-list" style="color:white;"> </i>
                                    </div>  
                                </div>
                                <div class="col-md-8">
                                    <small> Total de gastos: </small>
                                    <h6> {{"R$ ".convertValorReal($totalDespesas) }} </h6>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-6 col-lg-4 col-md-6">
                    <div class="card">
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-4">
                                    <div class="stats-icon purple">
                                        <i class="fa fa-list" style="color:white;"> </i>
                                    </div>
                                </div>
                                <div class="col-md-8">
                                    <small> Dias Ativos: </small> 
                                    <h6> {{ $diasAtividade }}  </h6>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
    <section class="graphics">
        <div class="row">
            <div class="col-12 col-lg-9">
                <div class="card"> 
                    <div class="card-header">
                        <h4> Diferença de gastos dos ultimos meses </h4>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            
                        {{-- GRAFICO COMPARATIVO DE GASTOS POR MÊS  --}}
                            <canvas id="myChart" width="400" height="150" class="chartjs-render-monitor"> </canvas>  
                        {{-- FIM DO GRÁFICO --}}
                    </div>
                </div>
            </div>
            <div class="col-12 col-lg-3">
                <div class="card">
                    <div class="card-header">
                        <h4> Ultimos Gastos: </h4>
                    </div>
                    <div class="card-body table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr> 
                                    <th> Descrição </th>    
                                    <th width="2%"> Valor </th>  
                                </tr>     
                            </thead>
                            <tbody> 
                                @foreach($dataUltimosLancamentos as $utlimosLancamentos)
                                    <tr> 
                                        <td> {{ $utlimosLancamentos->descricao }} </td>
                                    <td> <h6> {{ "R$".convertValorReal($utlimosLancamentos->valor) }} </h6> </td>
                                    </tr>
                                @endforeach 
                            </tbody>
                        </table>    
                    </div>
                </div>
            </div>
        </div>
    </section>
@endsection
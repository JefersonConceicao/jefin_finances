@extends('layout.page')
@section('content_header')
    <h3> Proventos </h3>
    <p class="text-subtitle"> Entrada de recursos </p>
@endsection

@section('content-page')
    @component('components.filtro')
        <form id="searchFormProventos">
            <div class="row">
                <div class="col-md-6">
                    <div class="form-group">
                        <label> Nome/Descrição Provento </label>
                        <input 
                            name="descricao_provento"
                            type="text"
                            class="form-control"
                        />
                    </div>
                </div>  
                <div class="col-md-6">
                    <div class="form-group">
                        <label> Data Provento </label>
                        <input 
                            name="data_provento"
                            type="text"
                            class="form-control datepicker"
                            value={{ date('d/m/Y') }}
                        />
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-md-12" style="margin-top:2%;">
                    <button class="btn btn-primary rounded-pill" type="submit">
                        <i class="fa fa-search"> </i> Pesquisar
                    </button>
                </div>
            </div>
        </form>
    @endcomponent
        <div class="card" id="gridProventos">
            <div class="card-header">
                <div class="row">
                    <div class="col-md-6">
                        <h1 class="card-title"> 
                            Total de registros: {{ count($dataProventos )}}
                        </h1>
                    </div>
                    <div class="col-md-6">
                        <button 
                            class="btn btn-primary rounded-pill float-end" 
                            id="addProvento"
                        >
                            Novo <i class="fa fa-plus-square"> </i>
                        </button>
                    </div>
                </div>
            </div>
            <div class="card-content">
                <div class="card-body table-responsive">
                    @if(count($dataProventos))
                        <table class="table table-hover"> 
                            <thead> 
                                <tr> 
                                    <th> Descrição </th>
                                    <th> Valor </th>
                                    <th> Data </th>
                                    <th width="2%"> Ações </th>
                                </tr>
                            </thead>
                            <tbody> 
                                @foreach($dataProventos as $provento)
                                    <tr>     
                                        <td> {{ $provento->descricao_provento }} </td>
                                        <td> {{ number_format($provento->valor_provento, 1)}}</td>
                                        <td> {{ converteData($provento->data_provento, 'd/m/Y') }} </td>
                                        <td> 
                                            <div class="text-center" style="display:flex">
                                                <button class="btn btn-secondary rounded-pill"> 
                                                    <i class="fa fa-edit"> </i>
                                                </button>
                                                &nbsp;
                                                <button class="btn btn-danger rounded-pill"> 
                                                    <i class="fa fa-trash"> </i>
                                                </button>
                                            </div>
                                        </td>
                                    </tr>   
                                @endforeach
                            </tbody>
                        </table>
                    @else 
                        <div class="text-center">
                            <h4> Nenhum registro encontrado.</h4>
                        </div>
                    @endif
                </div>
            </div>
        </div>
@endsection
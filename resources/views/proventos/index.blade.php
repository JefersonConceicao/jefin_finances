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
                            class="form-control"
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
        <div class="card">
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
        </div>
@endsection
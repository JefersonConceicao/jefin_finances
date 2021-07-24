@extends('layout.page')
@section('content_header')
    <h3> Despesas </h3>
    <p class="text-subtitle"> Gastos obrigatórios </p>
@endsection
@section('content-page')
    @component('components.filtro')
        <form id="searchFormDespesas"> 
            <div class="row">
                <div class="col-md-3"> 
                    <div class="form-group">
                        <label> Nome  </label> 
                        <input 
                            type="text"
                            class="form-control"
                            name="nome_despesa"
                        />
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="form-group">
                        <label> Tipo Despesa </label>
                        <select name="despesa_tipo_id" class="form-select"> 
                            <option value=""> Selecione </option>
                        </select>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="form-group">
                        <label> Mes </label>
                        <select name="mes" class="form-select"> 
                            <option value=""> Selecione </option>
                            @foreach($optionsMeses as $k => $v)
                                <option value="{{ $k }}" @if($k == date('m')) selected @endif> {{ $v }} </option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="form-group">
                        <label> Ano </label>
                        <input 
                            name="ano"
                            type="text"
                            class="form-control"
                            value="{{ date('Y') }}"
                        />
                    </div>
                </div>
            </div>
            <div class="row" style="margin-top:2%">
                <div class="col-md-12">
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
                    <h3 class="card-title"> Total de registros: {{ count($dataDespesas) }} </h3>     
                </div>
                <div class="col-md-6">
                    <button class="float-end btn btn-primary rounded-pill" id="addDespesa"> 
                        <i class="fa fa-plus-square"> </i> Novo
                    </button>
                </div>
            </div>
        </div>
        <div class="card-body">
            <div class="card-content">
                @if(count($dataDespesas) > 0)
                    <table class="table table-hover  table-responsive"> 
                        <thead> 
                            <tr> 
                                <th> Nome </th>
                                <th> Valor (R$) </th>
                                <th> Tipo  </th>
                            </tr>
                        </thead> 
                        <tbody> 
                            @foreach($dataDespesas as $despesa)
                                <tr style="cursor:pointer;" id="{{ $despesa->id }}"> 
                                    <td> {{ $despesa->nome_despesa }} </td>
                                    <td> {{ $despesa->valor_total }} </td>
                                    <td> Fixa </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                @else 
                    <div class="text-center">
                        <h4> Nenhum registro encontrado. </h4>
                    </div>
                @endif  
            </div>
        </div>
    </div>
@endsection
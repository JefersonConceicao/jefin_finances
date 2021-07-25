@extends('layout.page')
@section('content_header')
    <h3> Tipos Despesas </h3>
    <p class="text-subtitle"> Gerenciamento de tipos de despesas. </p>
@endsection

@section('content-page')
    @component('components.filtro')
        <form id="serachFormTiposDespesas">
            <div class="row">   
                <div class="col-md-6">
                    <div class="form-group">
                        <label> Nome </label>
                        <input 
                            name="nome"
                            type="text"
                            class="form-control"
                        />
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group">
                        <label> Ativo </label>
                        <select name="ativo" class="form-select"> 
                            <option> Selecione </option>
                            <option value="1"> Sim </option>
                            <option value="0"> Não </option>
                        </select>
                    </div>
                </div>
            </div>
            <div class="row" style="margin-top: 2%;">
                <div class="col-md-12">
                    <button class="btn btn-primary rounded-pill" type="submit"> 
                        <i class="fa fa-search"> </i> Pesquisar
                    </button>
                </div>
            </div>  
        </form>
    @endcomponent
    <div class="card" id="gridTiposDespesa">
        <div class="card-header"> 
            <div class="row">
                <div class="col-md-6">
                    <h4> Total de registros: {{ count($dataTiposDespesa) }} </h4>
                </div>
                <div class="col-md-6">
                    <button class="btn btn-primary rounded-pill float-end" id="addTiposDespesa"> 
                        <i class="fa fa-plus-square"> </i> Novo
                    </button>
                </div>
            </div>
        </div>
        <div class="card-body">
            <div class="card-content table-responsive"> 
                <table class="table table-hover"> 
                    <thead> 
                        <tr> 
                            <th width="70%"> Nome  </th>
                            <th> Ativo </th>
                            <th width="2%"> Ações </th>
                        </tr>
                    </thead>
                    <tbody> 
                        @foreach($dataTiposDespesa as $tipoDespesa)
                            <tr> 
                                <td> {{ $tipoDespesa->nome }} </td>
                                <td> 
                                   <label class="badge bg-{{ $tipoDespesa->ativo ? "success" : "danger"}}"> 
                                        {{ $tipoDespesa->ativo ? "Sim" : "Não" }}
                                    </label> 
                                </td>
                                <td> 
                                    <div class="text-center" style="display:flex;"> 
                                        <button class="btn btn-primary rounded-pill btnEditTiposDespesa" id="{{$tipoDespesa->id}}"> 
                                            <i class="fa fa-edit"> </i>
                                        </button>
                                        &nbsp;
                                        <button class="btn btn-danger rounded-pill btnDeleteTiposDespesa" id="{{$tipoDespesa->id}}"> 
                                            <i class="fa fa-trash"> </i>
                                        </button>
                                    </div>
                                </td>   
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
@endsection
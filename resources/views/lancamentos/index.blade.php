@extends('layout.page')
@section('content_header')
    <h3> Lançamentos </h3> 
    <p class="text-subtitle"> Histórico de gastos. </p>
@endsection
@section('breadcrumb')
    <div class="row">
        <div class="col-md-12">
            <button class="btn btn-primary rounded-pill float-end" id="addLancamento"> 
               <i class="fa fa-plus-square"> </i> Novo 
            </button>       
        </div>
    </div>
@endsection
@section('content-page')
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-body">
                    <form id="searchFormLancamentos">
                        <div class="row"> 
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label> Mês </label>
                                    <select class="form-select" name="mes"> 
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
                            <div class="col-md-6">
                                <button class="btn btn-primary rounded-pill float-end" type="submit" style="margin-top:15px;"> 
                                    <i class="fa fa-search"> </i> Pesquisar
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <div id="gridLancamentos">
        <div class="row">
            <div class="col-md-12" >
                <h6 style="color:green;"> Entrada: R$ {{ convertValorReal($totalProventos) }} </h6>
                <h6 style="color:red;"> Gastos: R$ {{ convertValorReal($totalLancamentos) }} </h6>
                <h6> Quantia Atual: R$ {{ convertValorReal($totalProventos - $totalLancamentos) }} </h6>
            </div>
        </div>
        <div class="row" style="margin-top:3%">
            <div class="col-md-6 col-lg-6">
            <h6> Despesas </h6>
                <div class="card"> 
                    <div class="card-body"> 
                        <div class="card-content table-responsive"> 
                            <table class="table table-hover"> 
                                <thead> 
                                    <tr> 
                                        <th> Descrição </th>
                                        <th> Valor </th>
                                        <th width="1%"> Ações </th>
                                    </tr>
                                <thead> 
                                <tbody> 
                                    @php $valorTotalDespesa = 0 @endphp
                                    @foreach($dataLancamentosDespesa as $lancamentoDespesa)
                                        @php $valorTotalDespesa += $lancamentoDespesa->valor; @endphp
                                        <tr> 
                                            <td> {{ $lancamentoDespesa->descricao }} </td>
                                            <td> {{ "R$". convertValorReal($lancamentoDespesa->valor) }} </td>
                                            <td> 
                                                <button id="{{$lancamentoDespesa->id}}" class="btn btn-danger rounded-pill btnExcluirLancamento"> 
                                                    <i class="fa fa-trash"> </i>
                                                </button>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>  
                    </div>
                </div>
                <div> <h6> Total Despesas: {{ "R$".convertValorReal($valorTotalDespesa) }} </h6> </div>
            </div>
            
            <div class="col-md-6 col-lg-6">
                <h6> Outros gastos </h6>
                <div class="card"> 
                    <div class="card-body"> 
                        <div class="card-content table-responsive"> 
                            <table class="table table-hover"> 
                                <thead> 
                                    <tr> 
                                        <th> Descrição </th>
                                        <th> Valor </th>
                                        <th width="1%"> Ações </th>
                                    </tr>
                                <thead> 
                                <tbody> 
                                    @php $valorTotalOutros = 0; @endphp
                                    @foreach($dataOutrosLancamentos as $outrosLancamentos)
                                        @php $valorTotalOutros += $outrosLancamentos->valor;  @endphp
                                        <tr> 
                                            <td> {{ $outrosLancamentos->descricao }} </td>
                                            <td> {{ convertValorReal($outrosLancamentos->valor) }} </td>
                                            <td> 
                                                <button id="{{$outrosLancamentos->id}}"class="btn btn-danger rounded-pill btnExcluirLancamento"> 
                                                    <i class="fa fa-trash"> </i>
                                                </button>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>  
                    </div>
                </div>
                <div> <h6> Total Outros Gastos: {{ "R$".convertValorReal($valorTotalOutros) }} </h6> </div>
            </div>
        </div>
    </div>
@endsection
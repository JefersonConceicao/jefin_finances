@extends('layout.page')
@section('content_header')
    <h3> Dívidas </h3>
    <p> Acompanhe o prazo e o valor parcial das dívidas </p>
@endsection
@section('content-page')
    @component('components.filtro')
        <form id="formFilterDividas">
            <div class="row">
                <div class="col-md-4">
                    <div class="form-group">
                        <label for=""> Descrição </label>
                        <input id="descricaoDivida" class="form-control" name="descricao_divida" type="text" />
                    </div>
                </div>
                <div class="col-md-2">
                    <div class="form-group">
                        <label for="dataInicialDivida"> Data inicial </label>
                        <input id="dataInicialDivida" class="form-control datepicker" name="data_inicial_divida" autocomplete="off"/>
                    </div>
                </div>
                <div class="col-md-2">
                    <div class="form-group">
                        <label for="dataInicial"> Data final </label>
                        <input id="dataFinalDivida" class="form-control datepicker" name="data_final_divida" autocomplete="off"/>
                    </div>
                </div>
                <div class="col-md-4">
                    <button style="margin-top:3%;" type="submit" class="btn btn-sm btn-primary rounded-pill float-end">
                        <i class="fa fa-search"> </i>
                        Pesquisar
                    </button>
                </div>
            </div>
        </form>
    @endcomponent
    <div class="card" id="gridDebts">
        <div class="card-content">
            <div class="card-header">
                <div class="row">
                    <div class="col-md-6">
                        <h4> Total de dívidas: {{ count($dataDividas) }} </h4>
                        <p>  Valor total a pagar de dívdas: {{ "R$ ".convertValorReal($countDividas) }} </p>
                    </div>
                    <div class="col-md-6">
                        <div class="btn btn-primary float-end rounded-pill" id="addDebt"> 
                            <i class="fa fa-plus-square"> </i> Novo
                        </div>
                    </div>
                </div>
            </div>
            <div class="card-body">
                <div class="card-content table-responsive">
                    @if (count($dataDividas) > 0)
                        <table class="table">
                            <thead> 
                                <tr> 
                                    <th> Descrição </th>
                                    <th> Qtd parcelas </th>
                                    <th> Valor total </th>
                                    <th> Parcelas restantes </th>
                                    <th> Total pago </th>
                                    <th> Valor a ser pago </th>
                                    <th width="2%"> Ações </th>
                                </tr>
                            </thead>
                            <tbody> 
                                @foreach($dataDividas as $dividas)
                                    <tr key="{{$dividas->id}}" style="cursor:pointer;">  
                                        <td> {{ $dividas->descricao_divida }} </td>
                                        <td> {{ $dividas->qtd_parcela_total }} </td>
                                        <td> {{ "R$ ".convertValorReal($dividas->valor_total) }} </td>
                                        <td>  
                                            <label class="badge bg-primary">
                                                {{ $dividas->qtd_parcela_total - $dividas->qtd_parcela_parcial }} 
                                            </label>
                                        </td>
                                        <td> 
                                           <label class="badge bg-primary"> 
                                               {{ "R$ ".convertValorReal($dividas->valor_parcial) }} 
                                            <label>
                                        </td>
                                        <td> 
                                            <label class="badge bg-primary">
                                                {{ "R$ ".convertValorReal($dividas->valor_total - ($dividas->valor_parcela * $dividas->qtd_parcela_parcial)) }}
                                            </label>
                                        </td>
                                        <td> 
                                            <div style="display:flex; justify-content:space-between"> 
                                                @if($dividas->pago === 1)
                                                    <button class="btn btn-sm btn-success rounded-pill" title="Remover parcela" id="{{$dividas->id}}">  
                                                        <small> Quitado </small>
                                                    </button>   
                                                @else 
                                                    <button class="btn btn-sm btn-primary rounded-pill payDebt" title="Remover parcela" id="{{$dividas->id}}">  
                                                        <small> Pagar </small>
                                                    </button>         
                                                @endif
                                                &nbsp;  
                                                <button 
                                                    type="button" 
                                                    class="btn btn-sm btn-danger rounded-pill deleteDebt" 
                                                    title="Excluir" 
                                                    id="{{$dividas->id}}"
                                                >
                                                    <i class="fa fa-trash"> </i>
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    @else
                        <h4> Nenhum registro encontrado. </h4>
                    @endif
                </div>
            </div>
        </div>
    </div>
@endsection

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
                        <label> Nome </label>
                        <input type="text" class="form-control" name="nome_despesa" />
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="form-group">
                        <label> Tipo Despesa </label>
                        <select name="despesa_tipo_id" class="form-select">
                            <option value=""> Todos </option>
                            @foreach ($optionsTipoDespesa as $k => $v)
                                <option value="{{ $k }}"> {{ $v }} </option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="form-group">
                        <label> Mes </label>
                        <select name="mes" class="form-select">
                            <option value=""> Todos os meses </option>
                            @foreach ($optionsMeses as $k => $v)
                                <option value="{{ $k }}" @if ($k == date('m')) selected @endif> {{ $v }} </option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="form-group">
                        <label> Ano </label>
                        <input name="ano" type="text" class="form-control" value="{{ date('Y') }}" />
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
    <div class="card" id="gridDespesas">
        <div class="card-header">
            <div class="row">
                <div class="col-md-6">
                    <h3 class="card-title"> Total de registros: {{ count($dataDespesas) }} </h3>
                    <h6> Total despesas: {{ "R$ " .convertValorReal($totalValor) }} </h6>
                    <h6 class="text-warning"> Valor a pagar: {{ "R$ " .convertValorReal($totalDividasAtivas) }} </h6>
                    <h6 class="text-success"> Valor Pago: {{ "R$ " .convertValorReal($totalValor - $totalDividasAtivas) }} </h6>
                </div>
                <div class="col-md-6">
                    <button class="float-end btn btn-primary rounded-pill" id="addDespesa">
                        <i class="fa fa-plus-square"> </i> Novo
                    </button>
                </div>
            </div>
        </div>
        <div class="card-body">
            <div class="card-content table-responsive">
                @if (count($dataDespesas) > 0)
                    <table class="table table-hover table-responsive">
                        <thead>
                            <tr>
                                <th> Criada em </th>
                                <th> Nome </th>
                                <th> Valor (R$) </th>
                                <th> Tipo </th>
                                <th width="2%"> Ações </th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($dataDespesas as $despesa)
                                <tr style="cursor:pointer;" id="{{ $despesa->id }}" class="rowSettingsDespesa">
                                    <td>
                                        {{ !empty($despesa->created_at) ? converteData($despesa->created_at, 'd/m/Y') : 'Não definido' }}
                                    </td>
                                    <td> {{ $despesa->nome_despesa }} </td>
                                    <td> {{ convertValorReal($despesa->valor_total) }} </td>
                                    <td>
                                        <b> {{ $despesa->despesaTipo->nome }} </b>
                                    </td>
                                    <td>
                                        <div class="text-center" style="display:flex;">
                                            <button
                                                class="btn btn-{{ $despesa->pago == 0 ? 'secondary' : 'success' }} btnDespesaPago"
                                                title="{{ $despesa->pago == 0 ? 'Declarar pagamento' : 'Remover Pagamento' }} ">
                                                <i class="fas fa-check-square"></i> <span>
                                            </button>
                                            &nbsp;
                                            <button class="btn btn-danger btnDeleteDespesa">
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
                        <h4> Nenhum registro encontrado. </h4>

                        <div class="text-center">
                            <button class="btn btn-primary rounded-pill" id="copyDespesas">
                                Repetir despesas do mes anterior
                            </button>
                        </div>
                    </div>
                @endif
            </div>
        </div>
    </div>
@endsection

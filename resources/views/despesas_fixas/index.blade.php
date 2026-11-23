@extends('layout.page')
@section('content_header')
    <h3> Despesas Fixas </h3>
    <p class="text-subtitle"> Gastos que se repetem todo mês dentro da Vigência. </p>
@endsection
@section('content-page')
    @component('components.filtro')
        <form id="searchFormDespesasFixas">
            <div class="row">
                <div class="col-md-12">
                    <div class="form-group">
                        <label> Nome </label>
                        <input type="text" class="form-control" name="nome_despesa" />
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
    <div class="card" id="gridDespesasFixas">
        <div class="card-header">
            <div class="row">
                <div class="col-md-6">
                    <h3 class="card-title"> Total de registros: {{ count($dataDespesasFixas) }} </h3>
                    <h6 class="text-primary"> Valor Previsto das vigentes neste mês: {{ "R$ " .convertValorReal($totalVigentes) }} </h6>
                    @if (!is_null($comprometimento))
                        @php
                            $corComprometimento = 'bg-success';
                            if($comprometimento > 70) $corComprometimento = 'bg-warning';
                            if($comprometimento > 90) $corComprometimento = 'bg-danger';
                        @endphp
                        <p class="mb-1 mt-2"> <small> Comprometimento da Renda com Despesas Fixas ({{ number_format($comprometimento, 1, ',', '.') }}%) </small> </p>
                        <div class="progress" style="height: 12px;">
                            <div class="progress-bar {{ $corComprometimento }}" role="progressbar"
                                style="width: {{ $comprometimento > 100 ? 100 : $comprometimento }}%"
                                aria-valuenow="{{ $comprometimento }}" aria-valuemin="0" aria-valuemax="100">
                            </div>
                        </div>
                    @else
                        <p class="mb-1 mt-2 text-muted"> <small> Comprometimento da Renda: sem Proventos no mês </small> </p>
                    @endif
                </div>
                <div class="col-md-6">
                    <div class="form-check form-switch float-end ms-3 mt-2">
                        <input class="form-check-input" type="checkbox" id="mostrarOcultas" name="mostrar_ocultas" value="1" @if(request()->boolean('mostrar_ocultas')) checked @endif>
                        <label class="form-check-label" for="mostrarOcultas"> Mostrar ocultas </label>
                    </div>
                    <button class="float-end btn btn-primary rounded-pill" id="addDespesaFixa">
                        <i class="fa fa-plus-square"> </i> Novo
                    </button>
                </div>
            </div>
        </div>
        <div class="card-body">
            <div class="card-content table-responsive">
                @if (count($dataDespesasFixas) > 0)
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th> Nome </th>
                                <th> Vigência </th>
                                <th> Valor Previsto (R$) </th>
                                <th> % da Renda </th>
                                <th> Vencimento </th>
                                <th width="2%"> Ações </th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($dataDespesasFixas as $fixa)
                                <tr id="{{ $fixa->id }}" @if($fixa->oculta) style="opacity:.55;" @endif>
                                    <td>
                                        {{ $fixa->nome_despesa }}
                                        @if ($fixa->oculta)
                                            <span class="badge bg-secondary ms-1"> Oculta </span>
                                        @endif
                                    </td>
                                    <td>
                                        {{ $fixa->mes_inicio->format('m/Y') }}
                                        &ndash;
                                        {{ $fixa->mes_fim ? $fixa->mes_fim->format('m/Y') : 'sem fim' }}
                                    </td>
                                    <td> {{ convertValorReal($fixa->valor) }} </td>
                                    <td>
                                        @if ($proventosDoMes > 0)
                                            {{ number_format(($fixa->valor / $proventosDoMes) * 100, 1, ',', '.') }}%
                                        @else
                                            <span class="text-muted"> &ndash; </span>
                                        @endif
                                    </td>
                                    <td> Dia {{ $fixa->dia_vencimento }} </td>
                                    <td>
                                        <div class="text-center" style="display:flex;">
                                            <button class="btn btn-secondary rounded-pill btnEditDespesaFixa" title="Alterar, Reajustar ou Encerrar">
                                                <i class="fa fa-edit"> </i>
                                            </button>
                                            &nbsp;
                                            @if ($fixa->oculta)
                                                <button class="btn btn-outline-secondary rounded-pill btnOcultarDespesaFixa" data-oculta="0" title="Desocultar">
                                                    <i class="fa fa-eye"> </i>
                                                </button>
                                            @else
                                                <button class="btn btn-outline-secondary rounded-pill btnOcultarDespesaFixa" data-oculta="1" title="Ocultar: some de todo o app, sem excluir">
                                                    <i class="fa fa-eye-slash"> </i>
                                                </button>
                                            @endif
                                            &nbsp;
                                            <button class="btn btn-danger rounded-pill btnDeleteDespesaFixa" title="Excluir com todas as Ocorrências" data-ocorrencias="{{ $fixa->ocorrencias_count }}">
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
                        <h4> Nenhuma despesa fixa cadastrada. </h4>
                        <p class="text-muted"> Cadastre aluguel, contas e assinaturas que se repetem todo mês. </p>
                    </div>
                @endif
            </div>
        </div>
    </div>
@endsection

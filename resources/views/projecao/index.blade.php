@extends('layout.page')
@section('content_header')
    <h3> Projeção </h3>
    <p class="text-subtitle"> Mês atual e os próximos {{ $horizonte }} meses, com o que já está registrado: Ocorrências, Despesas Variáveis e Proventos. </p>
@endsection
@section('content-page')
    <form method="GET" action="/projecao" class="mb-3">
        <div class="d-flex align-items-center gap-2">
            <label for="horizonte" class="mb-0"> Horizonte </label>
            <select name="horizonte" id="horizonte" class="form-select w-auto" onchange="this.form.submit()">
                @foreach ($horizontes as $opcao)
                    <option value="{{ $opcao }}" @if($opcao === $horizonte) selected @endif> {{ $opcao }} meses </option>
                @endforeach
            </select>
        </div>
    </form>

    @if (count($alertas) > 0)
        <div class="card">
            <div class="card-header pb-0">
                <h4 class="card-title mb-0"> Alertas </h4>
            </div>
            <div class="card-body">
                <ul class="list-unstyled mb-0">
                    @foreach ($alertas as $alerta)
                        <li class="mb-1 {{ $alerta['tipo'] === 'ganho' ? 'text-success' : 'text-warning' }}">
                            <i class="fa {{ $alerta['tipo'] === 'ganho' ? 'fa-arrow-up' : 'fa-exclamation-triangle' }}"></i>
                            {{ $alerta['descricao'] }}
                        </li>
                    @endforeach
                </ul>
            </div>
        </div>
    @endif

    <div class="row">
        @foreach ($meses as $mes)
            @php
                $percentual = $mes['percentual'];
                $corProgresso = 'bg-success';
                if($percentual > 70) $corProgresso = 'bg-warning';
                if($percentual > 90) $corProgresso = 'bg-danger';
            @endphp
            <div class="col-xl-3 col-md-6 col-12">
                <div class="card {{ $mes['isMesAtual'] ? 'border border-primary' : '' }}">
                    <div class="card-header pb-2">
                        <div class="d-flex justify-content-between align-items-center">
                            <h4 class="card-title mb-0"> {{ $mes['label'] }} </h4>
                            @if ($mes['isMesAtual'])
                                <span class="badge bg-primary"> Hoje </span>
                            @else
                                <span class="badge bg-light-secondary"> Projeção </span>
                            @endif
                        </div>
                    </div>
                    <div class="card-body">
                        <table class="table table-sm table-borderless mb-2">
                            <tr>
                                <td class="text-info"> Proventos </td>
                                <td class="text-end text-info"> {{ "R$ " .convertValorReal($mes['proventos']) }} </td>
                            </tr>
                            <tr>
                                <td> Despesas Fixas </td>
                                <td class="text-end"> {{ "R$ " .convertValorReal($mes['fixas']) }} </td>
                            </tr>
                            <tr>
                                <td> Despesas Variáveis </td>
                                <td class="text-end"> {{ "R$ " .convertValorReal($mes['variaveis']) }} </td>
                            </tr>
                            <tr class="border-top">
                                <td> <b> Total despesas </b> </td>
                                <td class="text-end"> <b> {{ "R$ " .convertValorReal($mes['total']) }} </b> </td>
                            </tr>
                            @if ($mes['isMesAtual'])
                                <tr>
                                    <td class="text-success"> Pago </td>
                                    <td class="text-end text-success"> {{ "R$ " .convertValorReal($mes['pago']) }} </td>
                                </tr>
                                <tr>
                                    <td class="text-warning"> A pagar </td>
                                    <td class="text-end text-warning"> {{ "R$ " .convertValorReal($mes['aPagar']) }} </td>
                                </tr>
                            @endif
                            <tr class="border-top">
                                <td> <b> Saldo do Mês </b> </td>
                                <td class="text-end">
                                    <b class="{{ $mes['saldo'] >= 0 ? 'text-success' : 'text-danger' }}">
                                        {{ "R$ " .convertValorReal($mes['saldo']) }}
                                    </b>
                                </td>
                            </tr>
                            <tr>
                                <td class="text-muted"> Saldo Acumulado </td>
                                <td class="text-end {{ $mes['saldoAcumulado'] >= 0 ? 'text-success' : 'text-danger' }}">
                                    {{ "R$ " .convertValorReal($mes['saldoAcumulado']) }}
                                </td>
                            </tr>
                        </table>

                        @if (count($mes['marcos']) > 0)
                            <div class="mb-2">
                                @foreach ($mes['marcos'] as $marco)
                                    <span class="badge {{ $marco['efeito'] === 'ganho' ? 'bg-light-success' : 'bg-light-warning' }} mb-1">
                                        {{ $marco['descricao'] }}
                                    </span>
                                @endforeach
                            </div>
                        @endif

                        @if (!is_null($percentual))
                            <p class="mb-1"> <small> Comprometimento da renda ({{ number_format($percentual, 1, ',', '.') }}%) </small> </p>
                            <div class="progress" style="height: 12px;">
                                <div class="progress-bar {{ $corProgresso }}" role="progressbar"
                                    style="width: {{ $percentual > 100 ? 100 : $percentual }}%"
                                    aria-valuenow="{{ $percentual }}" aria-valuemin="0" aria-valuemax="100">
                                </div>
                            </div>
                        @else
                            <p class="mb-0 text-muted"> <small> Sem Proventos neste mês. </small> </p>
                        @endif

                        <a href="/despesas?mes={{ $mes['mes'] }}&ano={{ $mes['ano'] }}" class="btn btn-sm btn-outline-primary rounded-pill w-100 mt-3">
                            Ver despesas do mês
                        </a>
                    </div>
                </div>
            </div>
        @endforeach
    </div>

    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h4 class="card-title mb-0"> Despesas Variáveis previstas </h4>
                </div>
                <div class="card-body">
                    @foreach ($meses as $mes)
                        <h6 class="mt-2">
                            {{ $mes['label'] }}
                            <small class="text-muted"> ({{ "R$ " .convertValorReal($mes['variaveis']) }}) </small>
                        </h6>
                        @if (count($mes['listaVariaveis']) > 0)
                            <div class="table-responsive mb-3">
                                <table class="table table-hover table-sm mb-0">
                                    <tbody>
                                        @foreach ($mes['listaVariaveis'] as $variavel)
                                            <tr>
                                                <td> {{ $variavel->nome_despesa }} </td>
                                                <td class="text-end"> {{ convertValorReal($variavel->valor_total) }} </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        @else
                            <p class="text-muted mb-3"> <small> Nenhuma Despesa Variável neste mês. </small> </p>
                        @endif
                    @endforeach
                </div>
            </div>
        </div>
    </div>
@endsection

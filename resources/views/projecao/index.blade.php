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

    @if (count($alertasPorMes) > 0)
        @php
            $proximo = collect($linhaDoTempo)->first(function($m){ return $m['grupo'] !== null; });
            $qtdProximo = count($proximo['grupo']['alertas']);
        @endphp
        <div class="card" id="cardAlertas">
            <div class="card-body py-3 d-flex flex-wrap align-items-center justify-content-between gap-2">
                <div>
                    <h4 class="card-title mb-1">
                        Alertas
                        <span class="text-muted fs-6 fw-normal"> · {{ count($alertas) }} nos próximos {{ $horizonte }} meses </span>
                    </h4>
                    <span class="text-muted">
                        Próximo: <b>{{ $proximo['label'] }}</b> · {{ $qtdProximo }} {{ $qtdProximo === 1 ? 'alerta' : 'alertas' }} ·
                        <b class="{{ $proximo['grupo']['liquido'] < 0 ? 'text-danger' : 'text-success' }}">{{ $proximo['grupo']['efeito'] }}</b>
                    </span>
                </div>
                <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#modalLinhaDoTempo">
                    <i class="fa fa-stream"></i> Ver linha do tempo
                </button>
            </div>
        </div>

        <div class="modal fade" id="modalLinhaDoTempo" tabindex="-1" aria-labelledby="tituloLinhaDoTempo" aria-hidden="true">
            <div class="modal-dialog modal-lg modal-dialog-scrollable modal-fullscreen-sm-down">
                <div class="modal-content">
                    <div class="modal-header flex-wrap gap-2">
                        <h5 class="modal-title" id="tituloLinhaDoTempo"> Linha do tempo dos Alertas </h5>
                        <div class="btn-group btn-group-sm ms-auto" role="group" aria-label="Filtrar Alertas" id="filtroLinhaDoTempo">
                            <button type="button" class="btn btn-outline-primary active" data-filtro="tudo" aria-pressed="true"> Tudo </button>
                            <button type="button" class="btn btn-outline-primary" data-filtro="entra" aria-pressed="false"> Entradas </button>
                            <button type="button" class="btn btn-outline-primary" data-filtro="sai" aria-pressed="false"> Saídas </button>
                        </div>
                        <button type="button" class="btn-close ms-1" data-bs-dismiss="modal" aria-label="Fechar"></button>
                    </div>
                    <div class="modal-body">
                        {{-- MINI-GRÁFICO: UMA COLUNA POR MÊS FUTURO; CLICAR ROLA ATÉ O MÊS NA LINHA --}}
                        <div class="ldt-barras" role="group" aria-label="Efeito no fluxo por mês">
                            @foreach ($linhaDoTempo as $m)
                                <button type="button" class="ldt-barra" data-alvo="ldt-{{ $m['mes'] }}" data-label="{{ $m['label'] }}"
                                    data-entra="{{ $m['grupo']['entra'] ?? 0 }}" data-sai="{{ $m['grupo']['sai'] ?? 0 }}"
                                    data-qtd-entra="{{ $m['grupo'] ? collect($m['grupo']['alertas'])->where('tipo', 'ganho')->count() : 0 }}"
                                    data-qtd-sai="{{ $m['grupo'] ? collect($m['grupo']['alertas'])->where('tipo', 'atencao')->count() : 0 }}">
                                    <span class="ldt-metade ldt-cima"><span class="ldt-coluna"></span></span>
                                    <span class="ldt-metade ldt-baixo"><span class="ldt-coluna"></span></span>
                                    <span class="ldt-rotulo"> {{ $m['rotuloCurto'] }} </span>
                                </button>
                            @endforeach
                        </div>

                        <ol class="ldt-linha">
                            @foreach ($linhaDoTempo as $m)
                                @continue($m['grupo'] === null)
                                <li class="ldt-mes" id="ldt-{{ $m['mes'] }}" data-entra="{{ $m['grupo']['entra'] }}" data-sai="{{ $m['grupo']['sai'] }}">
                                    <div class="ldt-cabecalho">
                                        <div>
                                            <span class="fw-bold"> {{ $m['label'] }} </span>
                                            <span class="text-muted"> · {{ $m['grupo']['emMeses'] }} </span>
                                        </div>
                                        <span class="ldt-efeito fw-bold {{ $m['grupo']['liquido'] < 0 ? 'text-danger' : 'text-success' }}"> {{ $m['grupo']['efeito'] }} </span>
                                    </div>
                                    <ul class="ldt-itens">
                                        @foreach ($m['grupo']['alertas'] as $alerta)
                                            @php $entra = $alerta['tipo'] === 'ganho'; @endphp
                                            <li class="ldt-item {{ $entra ? 'ldt-entra' : 'ldt-sai' }}" data-lado="{{ $entra ? 'entra' : 'sai' }}">
                                                <i class="fa {{ $entra ? 'fa-arrow-up' : 'fa-arrow-down' }}"></i>
                                                <div class="ldt-texto">
                                                    <div> {{ $alerta['titulo'] }} </div>
                                                    @if ($alerta['detalhe'])
                                                        <small class="text-muted"> {{ $alerta['detalhe'] }} </small>
                                                    @endif
                                                </div>
                                                <span class="ldt-valor"> {{ $entra ? '+' : '-' }}R$ {{ convertValorReal($alerta['valor']) }} </span>
                                            </li>
                                        @endforeach
                                    </ul>
                                </li>
                            @endforeach
                        </ol>
                    </div>
                </div>
            </div>
        </div>

        <style>
            .ldt-barras { display: flex; gap: 6px; overflow-x: auto; padding-bottom: 4px; margin-bottom: 1rem; }
            .ldt-barra { flex: 1 0 44px; display: flex; flex-direction: column; align-items: stretch; background: none; border: 0; padding: 4px 2px; border-radius: 6px; cursor: pointer; }
            .ldt-barra:hover:not(:disabled), .ldt-barra:focus-visible { background: rgba(67, 94, 190, .1); }
            .ldt-barra:disabled { cursor: default; }
            .ldt-metade { height: 48px; display: flex; justify-content: center; }
            .ldt-cima { align-items: flex-end; border-bottom: 1px solid var(--escuro-borda, #dce7f1); }
            .ldt-baixo { align-items: flex-start; }
            .ldt-coluna { width: 60%; max-width: 28px; height: 0; transition: height .25s; }
            .ldt-cima .ldt-coluna { background: #2fa86b; border-radius: 4px 4px 0 0; }
            .ldt-baixo .ldt-coluna { background: #e0505e; border-radius: 0 0 4px 4px; }
            .ldt-cima .ldt-coluna.ldt-neutra { background: var(--escuro-texto-suave, #adb5bd); }
            .ldt-rotulo { font-size: 12px; margin-top: 4px; color: var(--escuro-texto-suave, #6c757d); white-space: nowrap; }
            .ldt-linha { list-style: none; padding: 0 0 0 22px; margin: 0; border-left: 2px solid var(--escuro-borda, #dce7f1); margin-left: 6px; }
            .ldt-mes { position: relative; padding-bottom: 1.25rem; }
            .ldt-mes::before { content: ''; position: absolute; left: -30px; top: 4px; width: 14px; height: 14px; border-radius: 50%; background: #435ebe; box-shadow: 0 0 0 3px var(--escuro-superficie, #fff); }
            .ldt-mes.ldt-destaque .ldt-cabecalho { background: rgba(67, 94, 190, .15); border-radius: 6px; }
            .ldt-cabecalho { display: flex; justify-content: space-between; gap: .5rem; flex-wrap: wrap; padding: 0 4px; margin-bottom: .5rem; transition: background .4s; }
            .ldt-itens { list-style: none; padding: 0; margin: 0; }
            .ldt-item { display: flex; align-items: flex-start; gap: .6rem; padding: .45rem .7rem; border-radius: 6px; margin-bottom: 4px; }
            .ldt-item > svg, .ldt-item > i { margin-top: 4px; }
            .ldt-texto { flex: 1; min-width: 0; }
            .ldt-valor { font-weight: 600; white-space: nowrap; }
            .ldt-entra { background: rgba(25, 135, 84, .1); }
            .ldt-entra > svg, .ldt-entra .ldt-valor { color: #198754; }
            .ldt-sai { background: rgba(255, 193, 7, .14); }
            .ldt-sai > svg, .ldt-sai .ldt-valor { color: #b07d00; }
            html[data-theme="dark"] .ldt-entra > svg, html[data-theme="dark"] .ldt-entra .ldt-valor { color: #5cd29a; }
            html[data-theme="dark"] .ldt-sai > svg, html[data-theme="dark"] .ldt-sai .ldt-valor { color: #ffd666; }
        </style>
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
                        @if (count($mes['datasComemorativas']) > 0)
                            {{-- DATAS COMEMORATIVAS: PARA SE PROGRAMAR PARA OS GASTOS VARIÁVEIS DO MÊS --}}
                            <div class="mb-2">
                                @foreach ($mes['datasComemorativas'] as $comemorativa)
                                    <span class="badge bg-light-info mb-1" title="Data Comemorativa">
                                        <i class="fa fa-calendar-day"></i> {{ $comemorativa['nome'] }} ({{ $comemorativa['data']->format('d/m') }})
                                    </span>
                                @endforeach
                            </div>
                        @endif
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

                        @if (count($mes['gruposDeMarcos']) > 0)
                            <div class="mb-2">
                                @foreach ($mes['gruposDeMarcos'] as $grupo)
                                    @if ($grupo['agrupado'])
                                        {{-- MAIS DE 3 MARCOS DO MESMO LADO E TIPO: UM LINK COM A CONTAGEM ABRE A LISTA --}}
                                        <a href="#" class="badge {{ $grupo['efeito'] === 'ganho' ? 'bg-light-success' : 'bg-light-warning' }} mb-1 text-decoration-underline"
                                            data-bs-toggle="modal" data-bs-target="#marcos-{{ $grupo['chave'] }}">
                                            {{ $grupo['rotulo'] }}
                                        </a>
                                        <div class="modal fade" id="marcos-{{ $grupo['chave'] }}" tabindex="-1" aria-hidden="true">
                                            <div class="modal-dialog modal-dialog-scrollable">
                                                <div class="modal-content">
                                                    <div class="modal-header">
                                                        <h5 class="modal-title"> {{ $mes['label'] }}: {{ $grupo['rotulo'] }} </h5>
                                                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fechar"></button>
                                                    </div>
                                                    <div class="modal-body">
                                                        <table class="table table-sm mb-0">
                                                            <tbody>
                                                                @foreach ($grupo['itens'] as $item)
                                                                    <tr>
                                                                        <td> {{ $item['nome'] }} </td>
                                                                        <td class="text-end"> {{ "R$ " .convertValorReal($item['valor']) }} </td>
                                                                    </tr>
                                                                @endforeach
                                                            </tbody>
                                                        </table>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    @else
                                        @foreach ($grupo['itens'] as $item)
                                            <span class="badge {{ $grupo['efeito'] === 'ganho' ? 'bg-light-success' : 'bg-light-warning' }} mb-1">
                                                {{ $item['descricao'] }}
                                            </span>
                                        @endforeach
                                    @endif
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

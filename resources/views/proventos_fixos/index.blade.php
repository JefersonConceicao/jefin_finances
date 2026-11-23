@extends('layout.page')
@section('content_header')
    <h3> Proventos Fixos </h3>
    <p class="text-subtitle"> Proventos que se repetem todo mês dentro da Vigência, como o salário. </p>
@endsection
@section('content-page')
    <div class="card" id="gridProventosFixos">
        <div class="card-header">
            <div class="row">
                <div class="col-md-6">
                    <h3 class="card-title"> Total de registros: {{ count($dataProventosFixos) }} </h3>
                </div>
                <div class="col-md-6">
                    <div class="form-check form-switch float-end ms-3 mt-2">
                        <input class="form-check-input" type="checkbox" id="mostrarOcultas" name="mostrar_ocultas" value="1" @if(request()->boolean('mostrar_ocultas')) checked @endif>
                        <label class="form-check-label" for="mostrarOcultas"> Mostrar ocultos </label>
                    </div>
                    <button class="float-end btn btn-primary rounded-pill" id="addProventoFixo">
                        <i class="fa fa-plus-square"> </i> Novo
                    </button>
                </div>
            </div>
        </div>
        <div class="card-body">
            <div class="card-content table-responsive">
                @if (count($dataProventosFixos) > 0)
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th> Descrição </th>
                                <th> Vigência </th>
                                <th> Valor Previsto (R$) </th>
                                <th width="2%"> Ações </th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($dataProventosFixos as $fixo)
                                <tr id="{{ $fixo->id }}" @if($fixo->oculta) style="opacity:.55;" @endif>
                                    <td>
                                        {{ $fixo->descricao }}
                                        @if ($fixo->oculta)
                                            <span class="badge bg-secondary ms-1"> Oculto </span>
                                        @endif
                                    </td>
                                    <td>
                                        {{ $fixo->mes_inicio->format('m/Y') }}
                                        &ndash;
                                        {{ $fixo->mes_fim ? $fixo->mes_fim->format('m/Y') : 'sem fim' }}
                                    </td>
                                    <td> {{ convertValorReal($fixo->valor) }} </td>
                                    <td>
                                        <div class="text-center" style="display:flex;">
                                            <button class="btn btn-secondary rounded-pill btnEditProventoFixo" title="Alterar, Reajustar ou Encerrar">
                                                <i class="fa fa-edit"> </i>
                                            </button>
                                            &nbsp;
                                            @if ($fixo->oculta)
                                                <button class="btn btn-outline-secondary rounded-pill btnOcultarProventoFixo" data-oculta="0" title="Desocultar">
                                                    <i class="fa fa-eye"> </i>
                                                </button>
                                            @else
                                                <button class="btn btn-outline-secondary rounded-pill btnOcultarProventoFixo" data-oculta="1" title="Ocultar: some de todo o app, sem excluir">
                                                    <i class="fa fa-eye-slash"> </i>
                                                </button>
                                            @endif
                                            @if (!$fixo->temOcorrencias())
                                                &nbsp;
                                                <button class="btn btn-danger rounded-pill btnDeleteProventoFixo" title="Excluir">
                                                    <i class="fa fa-trash"> </i>
                                                </button>
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                @else
                    <div class="text-center">
                        <h4> Nenhum provento fixo cadastrado. </h4>
                        <p class="text-muted"> Cadastre o salário e outros proventos que se repetem todo mês. </p>
                    </div>
                @endif
            </div>
        </div>
    </div>
@endsection

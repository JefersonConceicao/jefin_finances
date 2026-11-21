@extends('layout.page')
@section('content_header')
    <h3> Despesas Fixas </h3>
    <p class="text-subtitle"> Gastos que se repetem todo mês dentro da Vigência. </p>
@endsection
@section('content-page')
    @component('components.filtro')
        <form id="searchFormDespesasFixas">
            <div class="row">
                <div class="col-md-8">
                    <div class="form-group">
                        <label> Nome </label>
                        <input type="text" class="form-control" name="nome_despesa" />
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="form-group">
                        <label> Ativo </label>
                        <select name="ativo" class="form-select">
                            <option value=""> Todos </option>
                            <option value="1"> Sim </option>
                            <option value="0"> Não </option>
                        </select>
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
                    <h6 class="text-primary"> Custo de vida basal (ativas): {{ "R$ " .convertValorReal($totalBasal) }} </h6>
                </div>
                <div class="col-md-6">
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
                                <th> Vencimento </th>
                                <th> Ativo </th>
                                <th width="2%"> Ações </th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($dataDespesasFixas as $fixa)
                                <tr id="{{ $fixa->id }}">
                                    <td>
                                        {{ $fixa->nome_despesa }}
                                        @if ($fixa->isTravada())
                                            <i class="fa fa-lock text-muted ms-1" title="Já lançada em {{ $fixa->despesas_count }} mês(es). Apenas inativação permitida."></i>
                                        @endif
                                    </td>
                                    <td>
                                        {{ $fixa->mes_inicio->format('m/Y') }}
                                        &ndash;
                                        {{ $fixa->mes_fim ? $fixa->mes_fim->format('m/Y') : 'sem fim' }}
                                    </td>
                                    <td> {{ convertValorReal($fixa->valor) }} </td>
                                    <td> Dia {{ $fixa->dia_vencimento }} </td>
                                    <td>
                                        <span class="badge bg-{{ $fixa->ativo ? 'success' : 'danger' }}">
                                            {{ $fixa->ativo ? 'Sim' : 'Não' }}
                                        </span>
                                    </td>
                                    <td>
                                        <div class="text-center" style="display:flex;">
                                            <button class="btn btn-secondary rounded-pill btnEditDespesaFixa" title="{{ $fixa->isTravada() ? 'Ativar/Inativar' : 'Alterar' }}">
                                                <i class="fa fa-edit"> </i>
                                            </button>
                                            @if (!$fixa->isTravada())
                                                &nbsp;
                                                <button class="btn btn-danger rounded-pill btnDeleteDespesaFixa" title="Excluir">
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
                        <h4> Nenhuma despesa fixa cadastrada. </h4>
                        <p class="text-muted"> Cadastre aluguel, contas e assinaturas que se repetem todo mês. </p>
                    </div>
                @endif
            </div>
        </div>
    </div>
@endsection

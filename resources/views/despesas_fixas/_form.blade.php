@php
    $despesaFixa = $despesaFixa ?? null;
    $travada = $despesaFixa && $despesaFixa->isTravada();
@endphp
@if ($travada)
    <div class="alert alert-light-warning">
        <i class="fa fa-lock"></i>
        Esta despesa fixa já foi lançada em Despesas. Para preservar o histórico, ela só pode ser
        <b>inativada</b>. Se o valor mudou, inative esta e cadastre uma nova.
    </div>
@endif
<div class="row">
    <div class="col-md-8">
        <div class="form-group">
            <label> Nome Despesa <span class="required"> * </span> </label>
            <input
                name="nome_despesa"
                type="text"
                class="form-control"
                maxlength="100"
                placeholder="Ex: Aluguel, Internet, Plano de saúde"
                value="{{ $despesaFixa->nome_despesa ?? '' }}"
                @if ($travada) readonly @endif
            />
            <div class="error_feedback"> </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="form-group">
            <label> Ativo <span class="required"> * </span> </label>
            <select name="ativo" class="form-select">
                <option value="1" @if(($despesaFixa->ativo ?? 1) == 1) selected @endif> Sim </option>
                <option value="0" @if(($despesaFixa->ativo ?? 1) == 0) selected @endif> Não </option>
            </select>
            <div class="error_feedback"> </div>
        </div>
    </div>
</div>
<div class="row">
    <div class="col-md-6">
        <div class="form-group">
            <label> Valor Previsto (R$) <span class="required"> * </span> </label>
            <input
                name="valor"
                type="text"
                class="form-control decimalValue"
                value="{{ $despesaFixa ? convertValorReal($despesaFixa->valor) : '' }}"
                @if ($travada) readonly @endif
            />
            <div class="error_feedback"> </div>
        </div>
    </div>
    <div class="col-md-6">
        <div class="form-group">
            <label> Dia de vencimento <span class="required"> * </span> </label>
            <input
                name="dia_vencimento"
                type="number"
                min="1"
                max="31"
                class="form-control"
                placeholder="Ex: 10"
                value="{{ $despesaFixa->dia_vencimento ?? '' }}"
                @if ($despesaFixa) readonly @endif
            />
            <div class="error_feedback"> </div>
        </div>
    </div>
</div>
<div class="row">
    <div class="col-md-4">
        <div class="form-group">
            <label> Mês de início <span class="required"> * </span> </label>
            <input
                name="mes_inicio"
                type="month"
                class="form-control"
                value="{{ $despesaFixa ? $despesaFixa->mes_inicio->format('Y-m') : date('Y-m') }}"
                @if ($despesaFixa) readonly @endif
            />
            <div class="error_feedback"> </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="form-group">
            <label> Mês de fim </label>
            <input
                name="mes_fim"
                type="month"
                class="form-control"
                value="{{ $despesaFixa && $despesaFixa->mes_fim ? $despesaFixa->mes_fim->format('Y-m') : '' }}"
                @if ($despesaFixa) readonly @endif
            />
            <small class="text-muted"> Vazio: sem fim </small>
            <div class="error_feedback"> </div>
        </div>
    </div>
    @if (!$despesaFixa)
        <div class="col-md-4">
            <div class="form-group">
                <label> Ou quantidade de parcelas </label>
                <input name="parcelas" type="number" min="1" max="360" class="form-control" placeholder="Ex: 10" />
                <small class="text-muted"> Calcula o mês de fim </small>
                <div class="error_feedback"> </div>
            </div>
        </div>
    @endif
</div>
<p class="text-muted mb-0">
    <small>
        <i class="fa fa-info-circle"></i>
        As Ocorrências são gravadas em Despesas para toda a Vigência (12 meses à frente quando não há fim).
    </small>
</p>

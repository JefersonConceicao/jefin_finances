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
            <label> Valor mensal (R$) <span class="required"> * </span> </label>
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
</div>
<p class="text-muted mb-0">
    <small>
        <i class="fa fa-info-circle"></i>
        Despesas fixas ativas são lançadas automaticamente em Despesas todo mês.
        Depois do primeiro lançamento, nome e valor não podem mais ser alterados.
    </small>
</p>

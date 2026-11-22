<div class="row">
    <div class="col-md-12">
        <div class="form-group">
            <label> Nome Despesa <span class="required"> * </span> </label>
            <input
                name="nome_despesa"
                type="text"
                class="form-control"
                maxlength="100"
                placeholder="Ex: Aluguel, Internet, Plano de saúde"
            />
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
                value="{{ now()->format('Y-m') }}"
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
            />
            <small class="text-muted"> Vazio: sem fim </small>
            <div class="error_feedback"> </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="form-group">
            <label> Ou quantidade de parcelas </label>
            <input name="parcelas" type="number" min="1" max="360" class="form-control" placeholder="Ex: 10" />
            <small class="text-muted"> Calcula o mês de fim </small>
            <div class="error_feedback"> </div>
        </div>
    </div>
</div>
<p class="text-muted mb-0">
    <small>
        <i class="fa fa-info-circle"></i>
        As Ocorrências são gravadas em Despesas para toda a Vigência (12 meses à frente quando não há fim).
    </small>
</p>

<?php

namespace App\Recorrencia;

use Carbon\Carbon;

/**
 * Uma Ocorrência a gravar: o mês, o Vencimento nele e, quando a Vigência tem fim, a parcela.
 */
class Ocorrencia
{
    public $mes;
    public $vencimento;
    public $parcela;
    public $totalParcelas;

    public function __construct(Carbon $mes, Carbon $vencimento, ?int $parcela, ?int $totalParcelas)
    {
        $this->mes = $mes;
        $this->vencimento = $vencimento;
        $this->parcela = $parcela;
        $this->totalParcelas = $totalParcelas;
    }

    /**
     * "Financiamento (5/10)" quando há parcelas; o nome como está quando não há fim.
     */
    public function nome(string $nome): string
    {
        return $this->parcela ? "{$nome} ({$this->parcela}/{$this->totalParcelas})" : $nome;
    }
}

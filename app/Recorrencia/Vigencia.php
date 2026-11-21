<?php

namespace App\Recorrencia;

use Carbon\Carbon;

/**
 * Vigência de algo recorrente (Despesa Fixa, Provento Fixo): mês de início, mês de fim opcional
 * e o dia de vencimento de cada mês. Decide quais Ocorrências gravar (ADR-0001).
 */
class Vigencia
{
    //RECORRÊNCIA SEM FIM MANTÉM ESTA QUANTIDADE DE MESES GRAVADOS, CONTANDO O MÊS ATUAL
    const JANELA_MESES = 12;

    private $inicio;
    private $fim;
    private $diaVencimento;

    public function __construct(Carbon $inicio, ?Carbon $fim, int $diaVencimento)
    {
        $this->inicio = $inicio->copy()->startOfMonth();
        $this->fim = $fim ? $fim->copy()->startOfMonth() : null;
        $this->diaVencimento = $diaVencimento;
    }

    /**
     * Fim calculado a partir da quantidade de parcelas: "10x a partir de março" termina em dezembro.
     */
    public static function fimPorParcelas(Carbon $inicio, int $parcelas): Carbon
    {
        return $inicio->copy()->startOfMonth()->addMonthsNoOverflow($parcelas - 1);
    }

    public function inicio(): Carbon
    {
        return $this->inicio->copy();
    }

    public function fim(): ?Carbon
    {
        return $this->fim ? $this->fim->copy() : null;
    }

    public function totalParcelas(): ?int
    {
        return $this->fim ? $this->inicio->diffInMonths($this->fim) + 1 : null;
    }

    /**
     * Ocorrências que faltam gravar: do mês seguinte à última gravada (ou do início) até o fim,
     * ou até completar a janela quando não há fim. Meses passados nunca são gerados.
     *
     * @return Ocorrencia[]
     */
    public function ocorrenciasAGravar(Carbon $hoje, ?Carbon $gravadasAte): array
    {
        $mesAtual = $hoje->copy()->startOfMonth();

        $de = $gravadasAte ? $gravadasAte->copy()->startOfMonth()->addMonthNoOverflow() : $this->inicio->copy();
        if ($de->lt($this->inicio)) {
            $de = $this->inicio->copy();
        }
        if ($de->lt($mesAtual)) {
            $de = $mesAtual->copy();
        }

        $ate = $this->fim ?: $mesAtual->copy()->addMonthsNoOverflow(self::JANELA_MESES - 1);

        $ocorrencias = [];
        for ($mes = $de; $mes->lte($ate); $mes = $mes->copy()->addMonthNoOverflow()) {
            $ocorrencias[] = new Ocorrencia(
                $mes->copy(),
                $this->vencimentoNoMes($mes),
                $this->fim ? $this->inicio->diffInMonths($mes) + 1 : null,
                $this->totalParcelas()
            );
        }

        return $ocorrencias;
    }

    /**
     * O dia de vencimento naquele mês; se o mês for mais curto, o último dia dele.
     */
    public function vencimentoNoMes(Carbon $mes): Carbon
    {
        $mes = $mes->copy()->startOfMonth();

        return $mes->day(min($this->diaVencimento, $mes->daysInMonth));
    }
}

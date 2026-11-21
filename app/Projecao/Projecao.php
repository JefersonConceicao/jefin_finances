<?php

namespace App\Projecao;

use Carbon\Carbon;
use App\Models\Despesa;
use App\Models\Proventos;

/**
 * A Projeção mês a mês, do mês atual até o Horizonte. Só lê o que está gravado
 * (Ocorrências, Despesas Variáveis e Proventos), então os ajustes feitos em meses futuros aparecem nela.
 */
class Projecao
{
    const HORIZONTES = [3, 6, 12];
    const HORIZONTE_PADRAO = 6;

    private $user;
    private $horizonte;
    private $mesAtual;

    public function __construct($user, $horizonte = null, Carbon $hoje = null)
    {
        $this->user = $user;
        $this->horizonte = self::horizonteValido($horizonte);
        $this->mesAtual = ($hoje ?: Carbon::now())->copy()->startOfMonth();
    }

    //UM HORIZONTE FORA DE 3, 6 OU 12 VOLTA PARA O PADRÃO
    public static function horizonteValido($horizonte)
    {
        return in_array((int) $horizonte, self::HORIZONTES, true) ? (int) $horizonte : self::HORIZONTE_PADRAO;
    }

    public function horizonte()
    {
        return $this->horizonte;
    }

    public function mesAtual()
    {
        return $this->mesAtual->copy();
    }

    //ÚLTIMO MÊS DA PROJEÇÃO
    public function ultimoMes()
    {
        return $this->mesAtual->copy()->addMonthsNoOverflow($this->horizonte);
    }

    public function meses($nomesDosMeses)
    {
        $despesa = new Despesa;
        $proventos = new Proventos;

        $meses = [];
        $saldoAcumulado = 0;

        for($i = 0; $i <= $this->horizonte; $i++){
            $data = $this->mesAtual->copy()->addMonthsNoOverflow($i);
            $filtro = ['mes' => $data->format('m'), 'ano' => $data->format('Y')];
            $isMesAtual = $i === 0;

            $totalProventos = (float) $proventos->getProventos($filtro, $this->user)->sum('valor_provento');

            $variaveis = $despesa->getDespesas($filtro, $this->user)->whereNull('despesa_fixa_id')->get();
            $totalVariaveis = (float) $variaveis->sum('valor_total');
            $totalFixas = (float) $despesa->getDespesas($filtro, $this->user)->whereNotNull('despesa_fixa_id')->sum('valor_total');

            $totalDespesas = $totalFixas + $totalVariaveis;
            $saldo = $totalProventos - $totalDespesas;
            //O SALDO ACUMULADO COMEÇA DO ZERO NO MÊS ATUAL
            $saldoAcumulado += $saldo;

            $mes = [
                'mes' => $filtro['mes'],
                'ano' => $filtro['ano'],
                'label' => $nomesDosMeses[$filtro['mes']] . '/' . $filtro['ano'],
                'isMesAtual' => $isMesAtual,
                'proventos' => $totalProventos,
                'fixas' => $totalFixas,
                'variaveis' => $totalVariaveis,
                'listaVariaveis' => $variaveis,
                'total' => $totalDespesas,
                'saldo' => $saldo,
                'saldoAcumulado' => $saldoAcumulado,
                'percentual' => $totalProventos > 0 ? ($totalDespesas / $totalProventos) * 100 : null,
            ];

            if($isMesAtual){
                $mes['aPagar'] = (float) $despesa->getDespesas($filtro, $this->user)->where('pago', 0)->sum('valor_total');
                $mes['pago'] = $totalDespesas - $mes['aPagar'];
            }

            $meses[] = $mes;
        }

        return $meses;
    }
}

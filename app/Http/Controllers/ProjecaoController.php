<?php

namespace App\Http\Controllers;

use Auth;
use Carbon\Carbon;

//MODELS
use App\Models\Despesa;
use App\Models\DespesaFixa;
use App\Models\Proventos;

class ProjecaoController extends Controller
{
    const MESES_PROJETADOS = 3;

    public function index()
    {
        $user = Auth::user();
        $despesa = new Despesa;
        $despesaFixa = new DespesaFixa;
        $proventos = new Proventos;

        $mesAtual = Carbon::now()->startOfMonth();
        $despesaFixa->completarJanela($user);

        $fixasAtivas = $despesaFixa
            ->getDespesasFixas(['ativo' => 1], $user)
            ->get();
        $totalFixasAtivas = $fixasAtivas->sum('valor');

        $meses = [];
        $saldoAcumulado = 0;

        for($i = 0; $i <= self::MESES_PROJETADOS; $i++){
            $data = $mesAtual->copy()->addMonths($i);
            $filtro = ['mes' => $data->format('m'), 'ano' => $data->format('Y')];
            $isMesAtual = $i === 0;

            $receitas = (float) $proventos->getProventos($filtro, $user)->sum('valor_provento');

            $variaveis = $despesa
                ->getDespesas($filtro, $user)
                ->whereNull('despesa_fixa_id')
                ->get();
            $totalVariaveis = (float) $variaveis->sum('valor_total');

            //MÊS ATUAL: FIXAS JÁ GERADAS | MESES FUTUROS: PROJEÇÃO DAS FIXAS ATIVAS
            $totalFixas = $isMesAtual
                ? (float) $despesa->getDespesas($filtro, $user)->whereNotNull('despesa_fixa_id')->sum('valor_total')
                : (float) $totalFixasAtivas;

            $totalDespesas = $totalFixas + $totalVariaveis;
            $saldo = $receitas - $totalDespesas;
            $saldoAcumulado += $saldo;

            $mes = [
                'mes' => $filtro['mes'],
                'ano' => $filtro['ano'],
                'label' => $this->optionsMeses[$filtro['mes']] . '/' . $filtro['ano'],
                'isMesAtual' => $isMesAtual,
                'receitas' => $receitas,
                'fixas' => $totalFixas,
                'variaveis' => $totalVariaveis,
                'listaVariaveis' => $variaveis,
                'total' => $totalDespesas,
                'saldo' => $saldo,
                'saldoAcumulado' => $saldoAcumulado,
                'percentual' => $receitas > 0 ? ($totalDespesas / $receitas) * 100 : null,
            ];

            if($isMesAtual){
                $mes['aPagar'] = (float) $despesa->getDespesas($filtro, $user)->where('pago', 0)->sum('valor_total');
                $mes['pago'] = $totalDespesas - $mes['aPagar'];
            }

            $meses[] = $mes;
        }

        return view('projecao.index')
            ->with('meses', $meses)
            ->with('fixasAtivas', $fixasAtivas)
            ->with('totalFixasAtivas', $totalFixasAtivas);
    }
}

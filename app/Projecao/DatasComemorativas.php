<?php

namespace App\Projecao;

use Carbon\Carbon;

/**
 * Datas Comemorativas (nacionais e de Salvador/BA) que costumam trazer gastos variáveis extras.
 * Lista fixa; Carnaval e Páscoa são calculados a cada ano.
 */
class DatasComemorativas
{
    /**
     * As Datas Comemorativas de um mês, em ordem de dia: [['nome' => 'São João', 'data' => Carbon], ...].
     */
    public static function doMes($ano, $mes)
    {
        $ano = (int) $ano;
        $mes = (int) $mes;

        $datas = array_filter(self::doAno($ano), function($data) use ($mes){
            return $data['data']->month === $mes;
        });

        usort($datas, function($a, $b){
            return $a['data'] <=> $b['data'];
        });

        return array_values($datas);
    }

    private static function doAno($ano)
    {
        $pascoa = self::pascoa($ano);

        return [
            ['nome' => 'Réveillon', 'data' => Carbon::create($ano, 1, 1)],
            //TERÇA-FEIRA DE CARNAVAL: 47 DIAS ANTES DA PÁSCOA
            ['nome' => 'Carnaval', 'data' => $pascoa->copy()->subDays(47)],
            ['nome' => 'Páscoa', 'data' => $pascoa],
            ['nome' => 'Dia das Mães', 'data' => self::enesimoDomingo($ano, 5, 2)],
            ['nome' => 'São João', 'data' => Carbon::create($ano, 6, 24)],
            ['nome' => '2 de Julho', 'data' => Carbon::create($ano, 7, 2)],
            ['nome' => 'Dia dos Pais', 'data' => self::enesimoDomingo($ano, 8, 2)],
            ['nome' => 'Dia das Crianças', 'data' => Carbon::create($ano, 10, 12)],
            //SEXTA-FEIRA DEPOIS DA 4ª QUINTA-FEIRA DE NOVEMBRO
            ['nome' => 'Black Friday', 'data' => Carbon::create($ano, 11, 1)->nthOfMonth(4, Carbon::THURSDAY)->addDay()],
            ['nome' => 'Natal', 'data' => Carbon::create($ano, 12, 25)],
            ['nome' => 'Réveillon', 'data' => Carbon::create($ano, 12, 31)],
        ];
    }

    private static function enesimoDomingo($ano, $mes, $n)
    {
        return Carbon::create($ano, $mes, 1)->nthOfMonth($n, Carbon::SUNDAY);
    }

    /**
     * Domingo de Páscoa pelo calendário gregoriano (algoritmo de Meeus/Jones/Butcher).
     */
    public static function pascoa($ano)
    {
        $a = $ano % 19;
        $b = intdiv($ano, 100);
        $c = $ano % 100;
        $d = intdiv($b, 4);
        $e = $b % 4;
        $f = intdiv($b + 8, 25);
        $g = intdiv($b - $f + 1, 3);
        $h = (19 * $a + $b - $d - $g + 15) % 30;
        $i = intdiv($c, 4);
        $k = $c % 4;
        $l = (32 + 2 * $e + 2 * $i - $h - $k) % 7;
        $m = intdiv($a + 11 * $h + 22 * $l, 451);
        $mes = intdiv($h + $l - 7 * $m + 114, 31);
        $dia = (($h + $l - 7 * $m + 114) % 31) + 1;

        return Carbon::create($ano, $mes, $dia)->startOfDay();
    }
}

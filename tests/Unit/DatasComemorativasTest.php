<?php

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;
use App\Projecao\DatasComemorativas;

class DatasComemorativasTest extends TestCase
{
    private function doMes($ano, $mes)
    {
        return array_map(function ($data) {
            return $data['nome'] . ' ' . $data['data']->format('d/m/Y');
        }, DatasComemorativas::doMes($ano, $mes));
    }

    /** @test */
    public function pascoa_cai_no_domingo_certo()
    {
        $this->assertSame('2025-04-20', DatasComemorativas::pascoa(2025)->format('Y-m-d'));
        $this->assertSame('2026-04-05', DatasComemorativas::pascoa(2026)->format('Y-m-d'));
        $this->assertSame('2027-03-28', DatasComemorativas::pascoa(2027)->format('Y-m-d'));
    }

    /** @test */
    public function carnaval_e_pascoa_mudam_de_mes_conforme_o_ano()
    {
        $this->assertSame(['Carnaval 17/02/2026'], $this->doMes(2026, 2));
        $this->assertSame([], $this->doMes(2026, 3));
        $this->assertSame(['Páscoa 05/04/2026'], $this->doMes(2026, 4));

        $this->assertSame(['Carnaval 09/02/2027'], $this->doMes(2027, 2));
        $this->assertSame(['Páscoa 28/03/2027'], $this->doMes(2027, 3));

        //CARNAVAL EM MARÇO
        $this->assertSame([], $this->doMes(2025, 2));
        $this->assertSame(['Carnaval 04/03/2025'], $this->doMes(2025, 3));
    }

    /** @test */
    public function datas_fixas_e_de_dia_da_semana()
    {
        $this->assertSame(['Réveillon 01/01/2027'], $this->doMes(2027, 1));
        $this->assertSame(['Dia das Mães 10/05/2026'], $this->doMes(2026, 5));
        $this->assertSame(['São João 24/06/2026'], $this->doMes(2026, 6));
        $this->assertSame(['2 de Julho 02/07/2026'], $this->doMes(2026, 7));
        $this->assertSame(['Dia dos Pais 09/08/2026'], $this->doMes(2026, 8));
        $this->assertSame(['Dia das Crianças 12/10/2026'], $this->doMes(2026, 10));
        $this->assertSame(['Black Friday 27/11/2026'], $this->doMes(2026, 11));
        $this->assertSame(['Natal 25/12/2026', 'Réveillon 31/12/2026'], $this->doMes(2026, 12));
    }

    /** @test */
    public function meses_sem_data_ficam_vazios()
    {
        $this->assertSame([], $this->doMes(2026, 9));
    }
}

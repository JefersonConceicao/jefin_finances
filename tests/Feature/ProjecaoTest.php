<?php

namespace Tests\Feature;

use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\User;
use App\Models\Despesa;
use App\Models\TiposDespesa;

class ProjecaoTest extends TestCase
{
    use RefreshDatabase;

    protected $user;

    protected function setUp(): void
    {
        parent::setUp();

        $this->withoutMiddleware(\App\Http\Middleware\VerifyCsrfToken::class);

        Carbon::setTestNow(Carbon::create(2026, 11, 10, 10, 0, 0));
        $this->user = factory(User::class)->create();
    }

    protected function tearDown(): void
    {
        Carbon::setTestNow();
        parent::tearDown();
    }

    private function projecao(array $parametros = [])
    {
        return $this->actingAs($this->user)->get(route('projecao.index', $parametros))->assertOk();
    }

    private function cadastrarDespesaVariavel($nome, $valor, $vencimento)
    {
        $tipo = TiposDespesa::firstOrCreate(['nome' => 'Impostos', 'user_id' => $this->user->id], ['ativo' => 1]);

        $this->actingAs($this->user)->postJson(route('despesas.store'), [
            'nome_despesa' => $nome,
            'despesa_tipo_id' => $tipo->id,
            'valor_total' => $valor,
            'ativo' => 1,
            'data_vencimento' => $vencimento,
        ])->assertJsonFragment(['error' => false]);
    }

    private function cadastrarDespesaFixa(array $dados)
    {
        $this->actingAs($this->user)->postJson(route('despesasFixas.store'), array_merge([
            'valor' => '1.000,00',
            'ativo' => 1,
            'mes_inicio' => '2026-11',
            'dia_vencimento' => 10,
        ], $dados))->assertJsonFragment(['error' => false]);
    }

    private function cadastrarProventoFixo(array $dados)
    {
        $this->actingAs($this->user)->postJson(route('proventosFixos.store'), array_merge([
            'mes_inicio' => '2026-11',
        ], $dados))->assertJsonFragment(['error' => false]);
    }

    private function meses($resposta)
    {
        return $resposta->viewData('meses');
    }

    /** @test */
    public function sem_parametro_mostra_o_mes_atual_e_mais_6_meses_e_com_12_o_mes_atual_e_mais_12()
    {
        $meses = $this->meses($this->projecao());
        $this->assertCount(7, $meses);
        $this->assertSame(['11', '2026'], [$meses[0]['mes'], $meses[0]['ano']]);
        $this->assertSame(['05', '2027'], [$meses[6]['mes'], $meses[6]['ano']]);

        $this->assertCount(13, $this->meses($this->projecao(['horizonte' => 12])));
        $this->assertCount(4, $this->meses($this->projecao(['horizonte' => 3])));
        $this->assertCount(7, $this->meses($this->projecao(['horizonte' => 5])));
    }

    /** @test */
    public function despesa_variavel_com_vencimento_em_3_meses_aparece_no_total_e_na_lista_daquele_mes()
    {
        $this->cadastrarDespesaVariavel('IPVA', '1.200,00', '15/02/2027');

        $fevereiro = $this->meses($this->projecao())[3];

        $this->assertSame('02', $fevereiro['mes']);
        $this->assertEquals(1200, $fevereiro['variaveis']);
        $this->assertEquals(1200, $fevereiro['total']);
        $this->assertSame(['IPVA'], $fevereiro['listaVariaveis']->pluck('nome_despesa')->all());
    }

    /** @test */
    public function ocorrencia_excluida_de_um_mes_futuro_nao_entra_no_total_daquele_mes()
    {
        $this->cadastrarDespesaFixa(['nome_despesa' => 'Academia']);
        $marco = Despesa::where('data_vencimento', '2027-03-10')->first();
        $this->actingAs($this->user)->deleteJson(route('despesas.delete', $marco->id))->assertJsonFragment(['error' => false]);

        $meses = $this->meses($this->projecao());

        $this->assertEquals(1000, $meses[3]['fixas']);
        $this->assertEquals(0, $meses[4]['fixas']);
        $this->assertEquals(0, $meses[4]['total']);
        $this->assertEquals(1000, $meses[5]['fixas']);
    }

    /** @test */
    public function saldo_do_mes_e_saldo_acumulado_somando_desde_o_mes_atual()
    {
        $this->cadastrarProventoFixo(['descricao' => 'Salário', 'valor' => '5.000,00']);
        $this->cadastrarDespesaFixa(['nome_despesa' => 'Aluguel']);
        $this->cadastrarDespesaVariavel('Presentes', '300,00', '20/12/2026');

        $meses = $this->meses($this->projecao(['horizonte' => 3]));

        $this->assertEquals([5000, 5000, 5000, 5000], array_column($meses, 'proventos'));
        $this->assertEquals([4000, 3700, 4000, 4000], array_column($meses, 'saldo'));
        $this->assertEquals([4000, 7700, 11700, 15700], array_column($meses, 'saldoAcumulado'));
        $this->assertArrayNotHasKey('receitas', $meses[0]);
    }

    private function alertas($resposta)
    {
        return collect($resposta->viewData('alertas'));
    }

    private function marcosDoMes($resposta, $indice)
    {
        return collect($this->meses($resposta)[$indice]['marcos']);
    }

    /** @test */
    public function fixa_de_cartao_que_termina_em_3_meses_gera_marco_de_termina_e_alerta_de_ganho()
    {
        $this->cadastrarDespesaFixa(['nome_despesa' => 'Cartão', 'valor' => '450,00', 'mes_inicio' => '2026-05', 'parcelas' => 10]);

        $resposta = $this->projecao();

        $this->assertEquals(
            [['tipo' => 'termina', 'descricao' => 'Cartão (10/10) termina', 'efeito' => 'ganho']],
            $this->marcosDoMes($resposta, 3)->all()
        );
        $this->assertEquals([[
            'tipo' => 'ganho',
            'mes' => '2027-02',
            'distancia' => 3,
            'quando' => 'Em 3 meses (Fevereiro/2027)',
            'valor' => 450.0,
            'descricao' => 'Termina Cartão (10/10): seu fluxo melhora em R$ 450,00 · em Março/2027, Despesas Fixas passam a R$ 0,00',
        ]], $this->alertas($resposta)->all());
    }

    /** @test */
    public function seguro_que_comeca_em_2_meses_gera_marco_de_comeca_e_alerta_de_atencao()
    {
        $this->cadastrarDespesaFixa(['nome_despesa' => 'Seguro', 'valor' => '180,00', 'mes_inicio' => '2027-01']);

        $resposta = $this->projecao()
            ->assertSee('Seguro começa')
            ->assertSee('Em 2 meses (Janeiro/2027)')
            ->assertSee('Começa Seguro: -R$ 180,00 · Despesas Fixas passam a R$ 180,00')
            ->assertSee('alert alert-warning', false);

        $this->assertEquals([['tipo' => 'comeca', 'descricao' => 'Seguro começa', 'efeito' => 'atencao']], $this->marcosDoMes($resposta, 2)->all());
        $this->assertEquals([[
            'tipo' => 'atencao',
            'mes' => '2027-01',
            'distancia' => 2,
            'quando' => 'Em 2 meses (Janeiro/2027)',
            'valor' => 180.0,
            'descricao' => 'Começa Seguro: -R$ 180,00 · Despesas Fixas passam a R$ 180,00',
        ]], $this->alertas($resposta)->all());
    }

    /** @test */
    public function provento_fixo_que_comeca_ou_termina_no_horizonte_gera_o_alerta_correspondente()
    {
        $this->cadastrarProventoFixo(['descricao' => 'Freela', 'valor' => '2.000,00', 'mes_inicio' => '2027-03']);
        $this->cadastrarProventoFixo(['descricao' => 'Bolsa', 'valor' => '800,00', 'mes_fim' => '2026-12']);

        $resposta = $this->projecao();

        $this->assertEquals([['tipo' => 'termina', 'descricao' => 'Bolsa termina', 'efeito' => 'atencao']], $this->marcosDoMes($resposta, 1)->all());
        $this->assertEquals([['tipo' => 'comeca', 'descricao' => 'Freela começa', 'efeito' => 'ganho']], $this->marcosDoMes($resposta, 4)->all());
        $this->assertEquals([
            ['tipo' => 'atencao', 'mes' => '2026-12', 'distancia' => 1, 'quando' => 'Em 1 mês (Dezembro/2026)', 'valor' => 800.0,
                'descricao' => 'Termina Bolsa: -R$ 800,00 · em Janeiro/2027, Proventos passam a R$ 0,00'],
            ['tipo' => 'ganho', 'mes' => '2027-03', 'distancia' => 4, 'quando' => 'Em 4 meses (Março/2027)', 'valor' => 2000.0,
                'descricao' => 'Começa Freela: +R$ 2.000,00 · Proventos passam a R$ 2.000,00'],
        ], $this->alertas($resposta)->all());
    }

    /** @test */
    public function despesa_variavel_com_vencimento_futuro_gera_alerta_de_atencao()
    {
        $this->cadastrarDespesaVariavel('Mercado', '400,00', '12/11/2026');
        $this->cadastrarDespesaVariavel('IPVA', '1.200,00', '15/02/2027');

        $this->assertEquals([[
            'tipo' => 'atencao',
            'mes' => '2027-02',
            'distancia' => 3,
            'quando' => 'Em 3 meses (Fevereiro/2027)',
            'valor' => 1200.0,
            'descricao' => 'Vence IPVA: -R$ 1.200,00',
        ]], $this->alertas($this->projecao())->all());
    }

    /** @test */
    public function mes_com_uma_fixa_comecando_e_outra_terminando_ganha_resumo_do_fluxo()
    {
        $this->cadastrarDespesaFixa(['nome_despesa' => 'Seguro moto', 'valor' => '306,00', 'mes_inicio' => '2027-01']);
        $this->cadastrarDespesaFixa(['nome_despesa' => 'Mac mini', 'valor' => '505,00', 'mes_inicio' => '2026-03', 'parcelas' => 11]);
        $this->cadastrarDespesaVariavel('IPVA', '1.200,00', '15/02/2027');

        $resposta = $this->projecao();
        $grupos = $resposta->viewData('alertasPorMes');

        $this->assertSame(['2027-01', '2027-02'], array_column($grupos, 'mes'));
        $this->assertCount(2, $grupos[0]['alertas']);
        $this->assertEquals(505, $grupos[0]['entra']);
        $this->assertEquals(306, $grupos[0]['sai']);
        $this->assertSame('Efeito no fluxo: +R$ 199,00 (entra R$ 505,00, sai R$ 306,00)', $grupos[0]['resumo']);
        //MÊS COM UM ALERTA SÓ NÃO TEM RESUMO
        $this->assertNull($grupos[1]['resumo']);

        $resposta->assertSeeInOrder([
            'Em 2 meses (Janeiro/2027)',
            'Começa Seguro moto: -R$ 306,00',
            'Termina Mac mini (11/11): seu fluxo melhora em R$ 505,00 · em Fevereiro/2027, Despesas Fixas passam a R$ 306,00',
            'Efeito no fluxo: +R$ 199,00',
            'Em 3 meses (Fevereiro/2027)',
            'Vence IPVA: -R$ 1.200,00',
        ]);
    }

    /** @test */
    public function marcos_e_alertas_fora_do_horizonte_nao_aparecem()
    {
        $this->cadastrarDespesaFixa(['nome_despesa' => 'Escola', 'valor' => '900,00', 'mes_inicio' => '2027-09']);

        $curta = $this->projecao();
        $this->assertEmpty($this->alertas($curta));
        $this->assertEmpty(collect($this->meses($curta))->pluck('marcos')->flatten(1));

        $longa = $this->projecao(['horizonte' => 12]);
        $this->assertEquals([['tipo' => 'comeca', 'descricao' => 'Escola começa', 'efeito' => 'atencao']], $this->marcosDoMes($longa, 10)->all());
        $this->assertSame(10, $this->alertas($longa)->first()['distancia']);
    }

    /** @test */
    public function com_horizonte_12_o_ultimo_mes_tem_as_recorrencias_sem_fim()
    {
        $this->cadastrarProventoFixo(['descricao' => 'Salário', 'valor' => '5.000,00']);
        $this->cadastrarDespesaFixa(['nome_despesa' => 'Aluguel']);

        $ultimo = $this->meses($this->projecao(['horizonte' => 12]))[12];

        $this->assertSame(['11', '2027'], [$ultimo['mes'], $ultimo['ano']]);
        $this->assertEquals(5000, $ultimo['proventos']);
        $this->assertEquals(1000, $ultimo['fixas']);
    }

    /** @test */
    public function depois_de_um_encerramento_o_marco_usa_o_nome_da_ultima_ocorrencia()
    {
        $this->cadastrarDespesaFixa(['nome_despesa' => 'Cartão', 'parcelas' => 10]);
        $this->cadastrarDespesaFixa(['nome_despesa' => 'Netflix', 'valor' => '50,00']);

        foreach (\App\Models\DespesaFixa::all() as $fixa) {
            $this->actingAs($this->user)
                ->putJson(route('despesasFixas.encerrar', $fixa->id), ['mes_fim' => '2027-02'])
                ->assertJsonFragment(['error' => false]);
        }

        $this->actingAs($this->user)->get(route('despesas.index', ['mes' => 2, 'ano' => 2027]))
            ->assertSee('Cartão (4/10)');

        $this->assertEquals([
            ['tipo' => 'termina', 'descricao' => 'Cartão (4/10) termina', 'efeito' => 'ganho'],
            ['tipo' => 'termina', 'descricao' => 'Netflix termina', 'efeito' => 'ganho'],
        ], $this->marcosDoMes($this->projecao(), 3)->all());
    }
}

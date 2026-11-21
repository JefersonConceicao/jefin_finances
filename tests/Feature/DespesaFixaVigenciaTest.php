<?php

namespace Tests\Feature;

use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\User;
use App\Models\Despesa;

class DespesaFixaVigenciaTest extends TestCase
{
    use RefreshDatabase;

    protected $user;

    protected function setUp(): void
    {
        parent::setUp();

        $this->withoutMiddleware(\App\Http\Middleware\VerifyCsrfToken::class);

        $this->hoje(2026, 2, 10);
        $this->user = factory(User::class)->create();
    }

    protected function tearDown(): void
    {
        Carbon::setTestNow();
        parent::tearDown();
    }

    private function hoje($ano, $mes, $dia)
    {
        Carbon::setTestNow(Carbon::create($ano, $mes, $dia, 10, 0, 0));
    }

    private function cadastrarDespesaFixa(array $dados)
    {
        return $this->actingAs($this->user)->postJson(route('despesasFixas.store'), array_merge([
            'nome_despesa' => 'Aluguel',
            'valor' => '1.500,00',
            'ativo' => 1,
            'mes_inicio' => '2026-02',
            'dia_vencimento' => 10,
        ], $dados));
    }

    private function despesasDoMes($mes, $ano)
    {
        return $this->actingAs($this->user)->get(route('despesas.index', ['mes' => $mes, 'ano' => $ano]));
    }

    /** @test */
    public function fixa_de_10_parcelas_a_partir_de_marco_gera_10_ocorrencias_numeradas_com_os_vencimentos()
    {
        $this->cadastrarDespesaFixa([
            'nome_despesa' => 'Financiamento',
            'mes_inicio' => '2026-03',
            'parcelas' => 10,
            'dia_vencimento' => 15,
        ])->assertJsonFragment(['error' => false]);

        $this->despesasDoMes(2, 2026)->assertDontSee('Financiamento');

        for ($parcela = 1; $parcela <= 10; $parcela++) {
            $mes = $parcela + 2;
            $this->despesasDoMes($mes, 2026)
                ->assertSee("Financiamento ({$parcela}/10)")
                ->assertSee(sprintf('15/%02d/2026', $mes));
        }

        $this->despesasDoMes(1, 2027)->assertDontSee('Financiamento');
    }

    private function ocorrenciasNoMes($mes, $ano, $nome = 'Aluguel')
    {
        return substr_count($this->despesasDoMes($mes, $ano)->getContent(), $nome);
    }

    /** @test */
    public function fixa_sem_fim_grava_12_meses_e_ao_avancar_um_mes_a_janela_ganha_exatamente_um_mes()
    {
        $this->cadastrarDespesaFixa(['mes_inicio' => '2026-02'])->assertJsonFragment(['error' => false]);

        $this->assertSame(1, $this->ocorrenciasNoMes(2, 2026));
        $this->assertSame(1, $this->ocorrenciasNoMes(1, 2027));
        $this->assertSame(0, $this->ocorrenciasNoMes(2, 2027));

        $this->hoje(2026, 3, 5);
        $this->actingAs($this->user)->get(route('despesasFixas.index'))->assertOk();

        $this->assertSame(1, $this->ocorrenciasNoMes(2, 2027));
        $this->assertSame(0, $this->ocorrenciasNoMes(3, 2027));
        $this->assertSame(1, $this->ocorrenciasNoMes(1, 2027));
    }

    /** @test */
    public function fixa_com_inicio_no_passado_so_gera_do_mes_atual_em_diante()
    {
        $this->cadastrarDespesaFixa([
            'nome_despesa' => 'Financiamento',
            'mes_inicio' => '2025-11',
            'parcelas' => 10,
        ])->assertJsonFragment(['error' => false]);

        $this->assertSame(0, $this->ocorrenciasNoMes(1, 2026, 'Financiamento'));
        $this->despesasDoMes(2, 2026)->assertSee('Financiamento (4/10)');
        $this->despesasDoMes(8, 2026)->assertSee('Financiamento (10/10)');
        $this->assertSame(0, $this->ocorrenciasNoMes(9, 2026, 'Financiamento'));
    }

    /** @test */
    public function dia_31_vence_no_ultimo_dia_de_fevereiro()
    {
        $this->cadastrarDespesaFixa(['mes_inicio' => '2026-02', 'dia_vencimento' => 31])
            ->assertJsonFragment(['error' => false]);

        $this->despesasDoMes(2, 2026)->assertSee('28/02/2026');
        $this->despesasDoMes(3, 2026)->assertSee('31/03/2026');
        $this->despesasDoMes(4, 2026)->assertSee('30/04/2026');
    }

    /** @test */
    public function ocorrencia_excluida_do_meio_nao_e_recriada_ao_completar_a_janela()
    {
        $this->cadastrarDespesaFixa(['mes_inicio' => '2026-02'])->assertJsonFragment(['error' => false]);
        //SÓ PARA SABER O id DA OCORRÊNCIA DE JULHO; A EXCLUSÃO E O RESULTADO PASSAM PELAS ROTAS
        $julho = Despesa::where('data_vencimento', '2026-07-10')->first();

        $this->actingAs($this->user)
            ->deleteJson(route('despesas.delete', $julho->id))
            ->assertJsonFragment(['error' => false]);

        $this->hoje(2026, 3, 5);
        $this->actingAs($this->user)->get(route('despesasFixas.index'))->assertOk();

        $this->assertSame(0, $this->ocorrenciasNoMes(7, 2026));
        $this->assertSame(1, $this->ocorrenciasNoMes(2, 2027));
    }

    /** @test */
    public function se_uma_ocorrencia_falhar_nem_a_fixa_nem_as_outras_ocorrencias_ficam()
    {
        $gravadas = 0;
        Despesa::creating(function () use (&$gravadas) {
            if (++$gravadas === 5) {
                throw new \RuntimeException('Falha simulada no banco');
            }
        });

        $this->cadastrarDespesaFixa(['mes_inicio' => '2026-02'])->assertJsonFragment(['error' => true]);

        $this->assertSame(0, $this->ocorrenciasNoMes(2, 2026));
        $this->actingAs($this->user)->get(route('despesasFixas.index'))->assertDontSee('Aluguel');
    }

    /** @test */
    public function a_lista_de_despesas_fixas_mostra_vigencia_valor_previsto_e_dia_de_vencimento()
    {
        $this->cadastrarDespesaFixa(['nome_despesa' => 'Aluguel', 'mes_inicio' => '2026-02', 'dia_vencimento' => 10]);
        $this->cadastrarDespesaFixa([
            'nome_despesa' => 'Financiamento',
            'valor' => '800,00',
            'mes_inicio' => '2026-03',
            'parcelas' => 10,
            'dia_vencimento' => 15,
        ]);

        $this->actingAs($this->user)->get(route('despesasFixas.index'))
            ->assertSeeInOrder(['Aluguel', '02/2026', 'sem fim', '1.500,00', 'Dia 10'])
            ->assertSeeInOrder(['Financiamento', '03/2026', '12/2026', '800,00', 'Dia 15']);
    }

    /** @test */
    public function as_telas_de_cadastro_e_edicao_da_despesa_fixa_mostram_a_vigencia()
    {
        $this->actingAs($this->user)->get(route('despesasFixas.create'))
            ->assertOk()
            ->assertSee('name="mes_inicio"', false)
            ->assertSee('name="parcelas"', false)
            ->assertSee('name="dia_vencimento"', false);

        $this->cadastrarDespesaFixa(['mes_inicio' => '2026-03', 'parcelas' => 10, 'dia_vencimento' => 15]);
        $fixa = \App\Models\DespesaFixa::first();

        $this->actingAs($this->user)->get(route('despesasFixas.edit', $fixa->id))
            ->assertOk()
            ->assertSeeInOrder(['03/2026', '12/2026', 'Dia 15']);
    }
}

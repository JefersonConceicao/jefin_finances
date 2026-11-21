<?php

namespace Tests\Feature;

use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\User;
use App\Models\Proventos;

class ProventoFixoTest extends TestCase
{
    use RefreshDatabase;

    protected $user;

    protected function setUp(): void
    {
        parent::setUp();

        $this->withoutMiddleware(\App\Http\Middleware\VerifyCsrfToken::class);

        $this->hoje(2026, 11, 10);
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

    private function cadastrarProventoFixo(array $dados = [])
    {
        return $this->actingAs($this->user)->postJson(route('proventosFixos.store'), array_merge([
            'descricao' => 'Salário',
            'valor' => '12.500,00',
            'mes_inicio' => '2026-11',
        ], $dados));
    }

    private function proventosDoMes($mes, $ano)
    {
        return $this->actingAs($this->user)->get(route('proventos.index', ['mes' => $mes, 'ano' => $ano]));
    }

    private function ocorrenciasNoMes($mes, $ano)
    {
        return substr_count($this->proventosDoMes($mes, $ano)->getContent(), 'Salário');
    }

    /** @test */
    public function provento_fixo_sem_fim_criado_em_novembro_gera_de_novembro_ate_12_meses_a_frente()
    {
        $this->cadastrarProventoFixo()->assertJsonFragment(['error' => false]);

        $this->assertSame(0, $this->ocorrenciasNoMes(10, 2026));
        $this->proventosDoMes(11, 2026)->assertSee('01/11/2026')->assertSee('12.500,00');
        $this->proventosDoMes(11, 2027)->assertSee('01/11/2027');
        $this->assertSame(1, $this->ocorrenciasNoMes(11, 2027));
        $this->assertSame(0, $this->ocorrenciasNoMes(12, 2027));
    }

    /** @test */
    public function abrir_a_tela_de_proventos_completa_a_janela_com_exatamente_um_mes_a_mais()
    {
        $this->cadastrarProventoFixo();

        $this->hoje(2026, 12, 3);
        $this->proventosDoMes(12, 2026)->assertOk();

        $this->assertSame(1, $this->ocorrenciasNoMes(12, 2027));
        $this->assertSame(0, $this->ocorrenciasNoMes(1, 2028));
        $this->assertSame(1, $this->ocorrenciasNoMes(11, 2027));
    }

    /** @test */
    public function o_cadastro_de_provento_nao_oferece_mais_o_provento_anual()
    {
        $this->actingAs($this->user)->get(route('proventos.create'))
            ->assertOk()
            ->assertDontSee('value="anual"', false);

        $this->actingAs($this->user)->postJson(route('proventos.store'), [
            'descricao_provento' => 'Bônus',
            'valor_provento' => '1.000,00',
            'period_type' => 'anual',
        ])
            ->assertStatus(422)
            ->assertJsonValidationErrors('period_type');
    }

    /** @test */
    public function proventos_anuais_ja_existentes_continuam_aparecendo()
    {
        //CRIADOS PELA ANTIGA OPÇÃO "ANUAL": PROVENTOS COMUNS, UM POR MÊS DO ANO
        foreach ([1, 6, 12] as $mes) {
            Proventos::create([
                'user_id' => $this->user->id,
                'descricao_provento' => 'Aluguel recebido',
                'valor_provento' => 900,
                'data_provento' => sprintf('2026-%02d-01', $mes),
            ]);
        }

        $this->proventosDoMes(6, 2026)->assertSee('Aluguel recebido')->assertSee('900,00');
    }

    /** @test */
    public function a_lista_de_proventos_fixos_mostra_vigencia_e_valor_previsto()
    {
        $this->cadastrarProventoFixo();
        $this->cadastrarProventoFixo(['descricao' => 'Freela', 'valor' => '2.000,00', 'mes_fim' => '2027-03']);

        $this->actingAs($this->user)->get(route('proventosFixos.index'))
            ->assertOk()
            ->assertSeeInOrder(['Salário', '11/2026', 'sem fim', '12.500,00'])
            ->assertSeeInOrder(['Freela', '11/2026', '03/2027', '2.000,00']);

        $this->actingAs($this->user)->get(route('proventosFixos.create'))->assertOk()->assertSee('name="mes_inicio"', false);
    }
}

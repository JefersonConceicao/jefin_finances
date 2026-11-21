<?php

namespace Tests\Feature;

use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\User;
use App\Models\Proventos;
use App\Models\ProventoFixo;

class OcorrenciaProventoFixoEscopoTest extends TestCase
{
    use RefreshDatabase;

    protected $user;
    protected $fixo;

    protected function setUp(): void
    {
        parent::setUp();

        $this->withoutMiddleware(\App\Http\Middleware\VerifyCsrfToken::class);

        $this->hoje(2026, 11, 10);
        $this->user = factory(User::class)->create();

        //SALÁRIO DE R$ 5.000,00, SEM FIM: OCORRÊNCIAS DE NOV/2026 A OUT/2027
        $this->actingAs($this->user)->postJson(route('proventosFixos.store'), [
            'descricao' => 'Salário',
            'valor' => '5.000,00',
            'mes_inicio' => '2026-11',
        ])->assertJsonFragment(['error' => false]);

        $this->fixo = ProventoFixo::first();
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

    //SÓ PARA SABER O id DA OCORRÊNCIA DO MÊS; ALTERAÇÃO E RESULTADO PASSAM PELAS ROTAS
    private function ocorrencia($mes, $ano)
    {
        return Proventos::where('provento_fixo_id', $this->fixo->id)
            ->whereMonth('data_provento', $mes)
            ->whereYear('data_provento', $ano)
            ->first();
    }

    private function alterarOcorrencia($mes, $ano, $valor, $seguintes, $user = null)
    {
        return $this->actingAs($user ?: $this->user)->putJson(route('proventos.update', $this->ocorrencia($mes, $ano)->id), array_filter([
            'descricao_provento' => 'Salário',
            'valor_provento' => $valor,
            'data_provento' => sprintf('01/%02d/%d', $mes, $ano),
            'update_subsequent' => $seguintes ? 'true' : null,
        ]));
    }

    private function excluirOcorrencia($mes, $ano, $seguintes)
    {
        return $this->actingAs($this->user)->deleteJson(route('proventos.delete', $this->ocorrencia($mes, $ano)->id), [
            'delete_subsequent' => $seguintes ? 'true' : 'false',
        ]);
    }

    private function proventosDoMes($mes, $ano)
    {
        return $this->actingAs($this->user)->get(route('proventos.index', ['mes' => $mes, 'ano' => $ano]));
    }

    private function ocorrenciasNoMes($mes, $ano)
    {
        return substr_count($this->proventosDoMes($mes, $ano)->getContent(), 'Salário');
    }

    private function listaDeFixos()
    {
        return $this->actingAs($this->user)->get(route('proventosFixos.index'));
    }

    /** @test */
    public function ajustar_so_um_mes_nao_muda_o_provento_fixo_nem_os_outros_meses()
    {
        $this->alterarOcorrencia(12, 2026, '6.200,00', false)->assertJsonFragment(['error' => false]);

        $this->proventosDoMes(12, 2026)->assertSee('6.200,00');
        $this->proventosDoMes(11, 2026)->assertSee('5.000,00');
        $this->proventosDoMes(1, 2027)->assertSee('5.000,00');
        $this->listaDeFixos()->assertSee('5.000,00')->assertDontSee('6.200,00');
    }

    /** @test */
    public function reajuste_muda_aquele_mes_e_os_seguintes_inclusive_os_ajustados_sem_mudar_os_anteriores()
    {
        $this->alterarOcorrencia(2, 2027, '6.200,00', false);

        $this->alterarOcorrencia(1, 2027, '5.500,00', true)->assertJsonFragment(['error' => false]);

        $this->proventosDoMes(12, 2026)->assertSee('5.000,00')->assertDontSee('5.500,00');
        foreach ([[1, 2027], [2, 2027], [10, 2027]] as [$mes, $ano]) {
            $this->proventosDoMes($mes, $ano)->assertSee('5.500,00')->assertDontSee('6.200,00');
        }
        $this->listaDeFixos()->assertSee('5.500,00');
    }

    /** @test */
    public function excluir_so_um_mes_mantem_os_outros()
    {
        $this->excluirOcorrencia(3, 2027, false)->assertJsonFragment(['error' => false]);

        $this->assertSame(0, $this->ocorrenciasNoMes(3, 2027));
        $this->assertSame(1, $this->ocorrenciasNoMes(4, 2027));
        $this->listaDeFixos()->assertSee('sem fim');
    }

    /** @test */
    public function excluir_aquele_mes_e_os_seguintes_encerra_no_mes_anterior_e_a_janela_nao_os_recria()
    {
        $this->excluirOcorrencia(3, 2027, true)->assertJsonFragment(['error' => false]);

        $this->assertSame(1, $this->ocorrenciasNoMes(2, 2027));
        $this->assertSame(0, $this->ocorrenciasNoMes(3, 2027));
        $this->assertSame(0, $this->ocorrenciasNoMes(10, 2027));
        $this->listaDeFixos()->assertSeeInOrder(['11/2026', '02/2027']);

        $this->hoje(2026, 12, 3);
        $this->listaDeFixos();
        $this->assertSame(0, $this->ocorrenciasNoMes(11, 2027));
        $this->assertSame(0, $this->ocorrenciasNoMes(3, 2027));
    }

    /** @test */
    public function reajustar_e_encerrar_pela_tela_do_provento_fixo()
    {
        $this->actingAs($this->user)
            ->putJson(route('proventosFixos.reajustar', $this->fixo->id), ['a_partir_de' => '2027-01', 'valor' => '5.800,00'])
            ->assertJsonFragment(['error' => false]);

        $this->proventosDoMes(12, 2026)->assertSee('5.000,00');
        $this->proventosDoMes(1, 2027)->assertSee('5.800,00');

        $this->actingAs($this->user)
            ->putJson(route('proventosFixos.encerrar', $this->fixo->id), ['mes_fim' => '2027-06'])
            ->assertJsonFragment(['error' => false]);

        $this->assertSame(1, $this->ocorrenciasNoMes(6, 2027));
        $this->assertSame(0, $this->ocorrenciasNoMes(7, 2027));
        $this->listaDeFixos()->assertSeeInOrder(['11/2026', '06/2027', '5.800,00']);
    }

    /** @test */
    public function outro_usuario_nao_altera_nem_exclui_a_ocorrencia()
    {
        $outro = factory(User::class)->create();
        $dezembro = $this->ocorrencia(12, 2026);

        $this->alterarOcorrencia(12, 2026, '1,00', true, $outro)->assertNotFound();
        $this->actingAs($outro)->deleteJson(route('proventos.delete', $dezembro->id), ['delete_subsequent' => 'true'])
            ->assertNotFound();

        $this->proventosDoMes(12, 2026)->assertSee('5.000,00');
        $this->assertSame(1, $this->ocorrenciasNoMes(1, 2027));
    }
}

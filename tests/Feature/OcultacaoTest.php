<?php

namespace Tests\Feature;

use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\User;
use App\Models\Despesa;
use App\Models\DespesaFixa;
use App\Models\Proventos;
use App\Models\ProventoFixo;

/**
 * Ocultação (issue #31): a Despesa Fixa ou o Provento Fixo oculto some de todo o app, com todas as
 * Ocorrências (inclusive as pagas), como se nunca tivesse existido; desocultar traz tudo de volta.
 */
class OcultacaoTest extends TestCase
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

    private function cadastrarDespesaFixa($nome, $valor, $inicio = '2026-11', array $dados = [])
    {
        $this->actingAs($this->user)->postJson(route('despesasFixas.store'), array_merge([
            'nome_despesa' => $nome,
            'valor' => $valor,
            'ativo' => 1,
            'mes_inicio' => $inicio,
            'dia_vencimento' => 10,
        ], $dados))->assertJsonFragment(['error' => false]);

        return DespesaFixa::where('nome_despesa', $nome)->firstOrFail();
    }

    private function cadastrarProventoFixo($descricao, $valor, $inicio = '2026-11')
    {
        $this->actingAs($this->user)->postJson(route('proventosFixos.store'), [
            'descricao' => $descricao,
            'valor' => $valor,
            'mes_inicio' => $inicio,
        ])->assertJsonFragment(['error' => false]);

        return ProventoFixo::where('descricao', $descricao)->firstOrFail();
    }

    private function ocultarDespesaFixa(DespesaFixa $fixa, $oculta = 1)
    {
        $this->actingAs($this->user)->putJson(route('despesasFixas.ocultar', $fixa->id), ['oculta' => $oculta])
            ->assertOk()->assertJsonFragment(['error' => false]);
    }

    private function ocultarProventoFixo(ProventoFixo $fixo, $oculta = 1)
    {
        $this->actingAs($this->user)->putJson(route('proventosFixos.ocultar', $fixo->id), ['oculta' => $oculta])
            ->assertOk()->assertJsonFragment(['error' => false]);
    }

    private function projecao()
    {
        return $this->actingAs($this->user)->get(route('projecao.index', ['horizonte' => 3]))->assertOk();
    }

    /** @test */
    public function despesa_fixa_oculta_sai_dos_totais_da_projecao_dos_marcos_e_dos_alertas()
    {
        $this->cadastrarProventoFixo('Salário', '5.000,00');
        $this->cadastrarDespesaFixa('Aluguel', '1.000,00');
        $seguro = $this->cadastrarDespesaFixa('Seguro moto', '300,00', '2027-01');

        $this->ocultarDespesaFixa($seguro);

        $resposta = $this->projecao();
        $meses = $resposta->viewData('meses');

        $this->assertEquals([1000, 1000, 1000, 1000], array_column($meses, 'fixas'));
        $this->assertEquals([4000, 4000, 4000, 4000], array_column($meses, 'saldo'));
        $this->assertSame([], collect($meses[2]['marcos'])->pluck('descricao')->all());
        $this->assertFalse(collect($resposta->viewData('alertas'))->contains(function($alerta){
            return strpos($alerta['descricao'], 'Seguro moto') !== false;
        }));
    }

    /** @test */
    public function desocultar_traz_tudo_de_volta()
    {
        $seguro = $this->cadastrarDespesaFixa('Seguro moto', '300,00', '2027-01');

        $this->ocultarDespesaFixa($seguro);
        $this->ocultarDespesaFixa($seguro, 0);

        $resposta = $this->projecao();
        $meses = $resposta->viewData('meses');

        $this->assertEquals([0, 0, 300, 300], array_column($meses, 'fixas'));
        $this->assertSame(['Seguro moto começa'], collect($meses[2]['marcos'])->pluck('descricao')->all());
    }

    /** @test */
    public function ocorrencias_da_oculta_somem_da_lista_do_mes_inclusive_as_pagas()
    {
        $claro = $this->cadastrarDespesaFixa('Claro', '120,00');
        $ocorrencia = Despesa::where('despesa_fixa_id', $claro->id)->orderBy('data_vencimento')->firstOrFail();
        $this->actingAs($this->user)->putJson(route('despesa.pay', $ocorrencia->id))->assertJsonFragment(['error' => false]);

        $this->ocultarDespesaFixa($claro);

        $this->actingAs($this->user)->get(route('despesas.index', ['mes' => 11, 'ano' => 2026]))
            ->assertOk()
            ->assertDontSee('Claro')
            ->assertViewHas('totalValor', 0);

        //A OCORRÊNCIA OCULTA É TRATADA COMO INEXISTENTE
        $this->actingAs($this->user)->get(route('despesas.edit', $ocorrencia->id))->assertNotFound();

        $this->ocultarDespesaFixa($claro, 0);

        $this->actingAs($this->user)->get(route('despesas.index', ['mes' => 11, 'ano' => 2026]))
            ->assertSee('Claro')
            ->assertViewHas('totalValor', 120);
        $this->assertSame(1, (int) $ocorrencia->fresh()->pago);
    }

    /** @test */
    public function ocultar_nao_altera_nem_exclui_nada()
    {
        $claro = $this->cadastrarDespesaFixa('Claro', '120,00', '2026-11', ['parcelas' => 3]);
        $antes = Despesa::withoutGlobalScopes()->where('despesa_fixa_id', $claro->id)->get()->toArray();

        $this->ocultarDespesaFixa($claro);

        $this->assertSame($antes, Despesa::withoutGlobalScopes()->where('despesa_fixa_id', $claro->id)->get()->toArray());
        $this->assertEquals(120, $claro->fresh()->valor);
        $this->assertSame('2027-01', $claro->fresh()->mes_fim->format('Y-m'));
    }

    /** @test */
    public function despesa_fixa_oculta_nao_aparece_na_lista_nem_no_total_a_menos_que_mostre_as_ocultas()
    {
        $this->cadastrarDespesaFixa('Aluguel', '1.000,00');
        $claro = $this->cadastrarDespesaFixa('Claro', '120,00');
        $this->ocultarDespesaFixa($claro);

        $this->actingAs($this->user)->get(route('despesasFixas.index'))
            ->assertOk()
            ->assertSee('Aluguel')
            ->assertDontSee('Claro')
            ->assertViewHas('totalVigentes', 1000);

        $this->actingAs($this->user)->get(route('despesasFixas.index', ['mostrar_ocultas' => 1]))
            ->assertOk()
            ->assertSeeInOrder(['Aluguel', 'Claro', 'Oculta', 'Desocultar'])
            ->assertViewHas('totalVigentes', 1000);
    }

    /** @test */
    public function oculta_nao_grava_ocorrencias_novas_e_ao_desocultar_completa_a_janela()
    {
        $academia = $this->cadastrarDespesaFixa('Academia', '100,00');
        $this->ocultarDespesaFixa($academia);
        $gravadas = Despesa::withoutGlobalScopes()->where('despesa_fixa_id', $academia->id)->count();

        Carbon::setTestNow(Carbon::create(2027, 1, 10, 10, 0, 0));
        $this->projecao();
        $this->assertSame($gravadas, Despesa::withoutGlobalScopes()->where('despesa_fixa_id', $academia->id)->count());

        $this->ocultarDespesaFixa($academia, 0);
        $this->projecao();
        $this->assertSame($gravadas + 2, Despesa::withoutGlobalScopes()->where('despesa_fixa_id', $academia->id)->count());
    }

    /** @test */
    public function provento_fixo_oculto_sai_da_projecao_e_da_lista_de_proventos()
    {
        $bico = $this->cadastrarProventoFixo('Bico', '800,00');
        $this->cadastrarProventoFixo('Salário', '5.000,00');

        $this->ocultarProventoFixo($bico);

        $meses = $this->projecao()->viewData('meses');
        $this->assertEquals([5000, 5000, 5000, 5000], array_column($meses, 'proventos'));
        $this->assertSame(['Salário começa'], collect($meses[0]['marcos'])->pluck('descricao')->all());

        $this->actingAs($this->user)->get(route('proventos.index', ['mes' => 11, 'ano' => 2026]))
            ->assertOk()
            ->assertSee('Salário')
            ->assertDontSee('Bico');

        $this->actingAs($this->user)->get(route('proventosFixos.index'))
            ->assertDontSee('Bico');
        $this->actingAs($this->user)->get(route('proventosFixos.index', ['mostrar_ocultas' => 1]))
            ->assertSeeInOrder(['Salário', 'Bico', 'Oculto', 'Desocultar']);

        $this->ocultarProventoFixo($bico, 0);
        $this->assertEquals(5800, $this->projecao()->viewData('meses')[0]['proventos']);
    }

    /** @test */
    public function avulsos_nao_sao_afetados_pela_ocultacao()
    {
        $claro = $this->cadastrarDespesaFixa('Claro', '120,00');
        Despesa::create(['user_id' => $this->user->id, 'nome_despesa' => 'Farmácia', 'valor_total' => 80, 'data_vencimento' => '2026-11-15', 'ativo' => 1]);
        Proventos::create(['user_id' => $this->user->id, 'descricao_provento' => 'Venda', 'valor_provento' => 200, 'data_provento' => '2026-11-05']);

        $this->ocultarDespesaFixa($claro);

        $mes = $this->projecao()->viewData('meses')[0];
        $this->assertEquals(80, $mes['total']);
        $this->assertEquals(200, $mes['proventos']);
    }

    /** @test */
    public function outro_usuario_nao_oculta()
    {
        $claro = $this->cadastrarDespesaFixa('Claro', '120,00');
        $bico = $this->cadastrarProventoFixo('Bico', '800,00');
        $outro = factory(User::class)->create();

        $this->actingAs($outro)->putJson(route('despesasFixas.ocultar', $claro->id), ['oculta' => 1])->assertNotFound();
        $this->actingAs($outro)->putJson(route('proventosFixos.ocultar', $bico->id), ['oculta' => 1])->assertNotFound();
        $this->assertSame(0, (int) $claro->fresh()->oculta);
        $this->assertSame(0, (int) $bico->fresh()->oculta);
    }

    /** @test */
    public function encerrar_uma_oculta_continua_funcionando()
    {
        $claro = $this->cadastrarDespesaFixa('Claro', '120,00');
        $this->ocultarDespesaFixa($claro);

        $this->actingAs($this->user)->putJson(route('despesasFixas.encerrar', $claro->id), ['mes_fim' => '2026-12'])
            ->assertJsonFragment(['error' => false]);

        $this->assertSame(2, Despesa::withoutGlobalScopes()->where('despesa_fixa_id', $claro->id)->count());
    }
}

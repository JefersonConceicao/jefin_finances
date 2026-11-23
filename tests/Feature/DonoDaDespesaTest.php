<?php

namespace Tests\Feature;

use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\User;
use App\Models\Despesa;
use App\Models\DespesaFixa;
use App\Models\TiposDespesa;

class DonoDaDespesaTest extends TestCase
{
    use RefreshDatabase;

    protected $dono;
    protected $outro;
    protected $despesa;

    protected function setUp(): void
    {
        parent::setUp();

        $this->withoutMiddleware(\App\Http\Middleware\VerifyCsrfToken::class);

        Carbon::setTestNow(Carbon::create(2026, 11, 21, 10, 0, 0));

        $this->dono = factory(User::class)->create();
        $this->outro = factory(User::class)->create();

        $tipo = TiposDespesa::create(['nome' => 'Moradia', 'ativo' => 1, 'user_id' => $this->dono->id]);

        $this->actingAs($this->dono)->postJson(route('despesas.store'), [
            'nome_despesa' => 'Aluguel',
            'despesa_tipo_id' => $tipo->id,
            'valor_total' => '1.200,00',
            'ativo' => 1,
            'data_vencimento' => '05/11/2026',
        ])->assertJsonFragment(['error' => false]);

        $this->despesa = Despesa::where('nome_despesa', 'Aluguel')->first();
    }

    protected function tearDown(): void
    {
        Carbon::setTestNow();
        parent::tearDown();
    }

    private function listaDoDono()
    {
        return $this->actingAs($this->dono)->get(route('despesas.index', ['mes' => '11', 'ano' => '2026']));
    }

    /** @test */
    public function outro_usuario_nao_abre_a_edicao_da_despesa()
    {
        $this->actingAs($this->outro)
            ->get(route('despesas.edit', $this->despesa->id))
            ->assertNotFound();
    }

    /** @test */
    public function outro_usuario_nao_altera_a_despesa()
    {
        $this->actingAs($this->outro)->putJson(route('despesas.update', $this->despesa->id), [
            'nome_despesa' => 'Invadido',
            'despesa_tipo_id' => $this->despesa->despesa_tipo_id,
            'valor_total' => '1,00',
            'ativo' => 1,
            'data_vencimento' => '05/11/2026',
        ])->assertNotFound();

        $this->listaDoDono()
            ->assertSee('Aluguel')
            ->assertSee('1.200,00')
            ->assertDontSee('Invadido');
    }

    /** @test */
    public function editar_a_despesa_nao_transfere_ela_para_outro_usuario()
    {
        $this->actingAs($this->dono)->putJson(route('despesas.update', $this->despesa->id), [
            'nome_despesa' => 'Aluguel',
            'despesa_tipo_id' => $this->despesa->despesa_tipo_id,
            'valor_total' => '1.200,00',
            'ativo' => 1,
            'data_vencimento' => '05/11/2026',
            'user_id' => $this->outro->id,
        ])->assertStatus(200);

        $this->listaDoDono()->assertSee('Aluguel');

        $this->actingAs($this->outro)
            ->get(route('despesas.index', ['mes' => '11', 'ano' => '2026']))
            ->assertDontSee('Aluguel');
    }

    /** @test */
    public function editar_a_despesa_nao_muda_o_pagamento_nem_a_despesa_fixa_de_origem()
    {
        //DESPESA FIXA DE OUTRO USUÁRIO, PARA TENTAR LIGAR A ELA
        $tipoDoOutro = TiposDespesa::create(['nome' => 'Fixa', 'ativo' => 1, 'user_id' => $this->outro->id]);
        $fixaDoOutro = DespesaFixa::create([
            'user_id' => $this->outro->id,
            'nome_despesa' => 'Internet',
            'despesa_tipo_id' => $tipoDoOutro->id,
            'valor' => 100,
            'ativo' => 1,
            'mes_inicio' => '2026-11-01',
            'dia_vencimento' => 5,
        ]);

        $this->actingAs($this->dono)->putJson(route('despesas.update', $this->despesa->id), [
            'nome_despesa' => 'Aluguel',
            'despesa_tipo_id' => $this->despesa->despesa_tipo_id,
            'valor_total' => '1.200,00',
            'ativo' => 1,
            'data_vencimento' => '05/11/2026',
            'pago' => 1,
            'despesa_fixa_id' => $fixaDoOutro->id,
        ])->assertStatus(200);

        //PAGAMENTO SÓ PELA ROTA DE PAGAMENTO (QUE GERA O LANÇAMENTO); DESPESA VARIÁVEL NÃO VIRA FIXA
        $this->listaDoDono()
            ->assertSee('Declarar pagamento')
            ->assertDontSee('Remover Pagamento')
            ->assertDontSee('bg-light-primary');
    }

    /** @test */
    public function outro_usuario_nao_exclui_a_despesa()
    {
        $this->actingAs($this->outro)
            ->deleteJson(route('despesas.delete', $this->despesa->id))
            ->assertNotFound();

        $this->listaDoDono()->assertSee('Aluguel');
    }

    /** @test */
    public function outro_usuario_nao_declara_o_pagamento_da_despesa()
    {
        $this->actingAs($this->outro)
            ->putJson(route('despesa.pay', $this->despesa->id))
            ->assertNotFound();

        $this->listaDoDono()
            ->assertSee('Declarar pagamento')
            ->assertDontSee('Remover Pagamento');
    }

    /** @test */
    public function dono_declara_o_pagamento_da_propria_despesa()
    {
        $this->actingAs($this->dono)
            ->putJson(route('despesa.pay', $this->despesa->id))
            ->assertStatus(200)
            ->assertJsonFragment(['msg' => 'Pagamento registrado']);

        $this->listaDoDono()->assertSee('Remover Pagamento');
    }

    /** @test */
    public function dono_exclui_a_propria_despesa()
    {
        $this->actingAs($this->dono)
            ->deleteJson(route('despesas.delete', $this->despesa->id))
            ->assertStatus(200)
            ->assertJsonFragment(['error' => false]);

        $this->listaDoDono()->assertDontSee('Aluguel');
    }
}

<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\User;
use App\Models\Despesa;
use App\Models\Lancamento;
use App\Models\TiposDespesa;

class DonoDoLancamentoTest extends TestCase
{
    use RefreshDatabase;

    protected $dono;
    protected $outro;
    protected $despesa;

    protected function setUp(): void
    {
        parent::setUp();

        $this->withoutMiddleware(\App\Http\Middleware\VerifyCsrfToken::class);

        $this->dono = factory(User::class)->create();
        $this->outro = factory(User::class)->create();

        $this->despesa = $this->cadastrarDespesa($this->dono, 'Aluguel');
    }

    private function cadastrarDespesa($user, $nome)
    {
        $tipo = TiposDespesa::create(['nome' => 'Moradia', 'ativo' => 1, 'user_id' => $user->id]);

        $this->actingAs($user)->postJson(route('despesas.store'), [
            'nome_despesa' => $nome,
            'despesa_tipo_id' => $tipo->id,
            'valor_total' => '1.200,00',
            'ativo' => 1,
            'data_vencimento' => '05/11/2026',
        ])->assertJsonFragment(['error' => false]);

        return Despesa::where('nome_despesa', $nome)->first();
    }

    private function pagarDespesa($user, $despesa)
    {
        $this->actingAs($user)
            ->putJson(route('despesa.pay', $despesa->id))
            ->assertJsonFragment(['msg' => 'Pagamento registrado']);

        return Lancamento::where('despesa_id', $despesa->id)->first();
    }

    private function listaDeDespesas($user)
    {
        return $this->actingAs($user)->get(route('despesas.index', ['mes' => '11', 'ano' => '2026']));
    }

    private function listaDeLancamentos($user)
    {
        //O PAGAMENTO LANÇA NA DATA DE HOJE
        return $this->actingAs($user)->get(route('lancamentos.index', ['mes' => date('m'), 'ano' => date('Y')]));
    }

    /** @test */
    public function outro_usuario_nao_exclui_o_lancamento()
    {
        $lancamento = $this->pagarDespesa($this->dono, $this->despesa);

        $this->actingAs($this->outro)
            ->deleteJson(route('lancamentos.delete', $lancamento->id))
            ->assertNotFound();

        $this->listaDeLancamentos($this->dono)->assertSee('Aluguel');
        $this->listaDeDespesas($this->dono)->assertSee('Remover Pagamento');
    }

    /** @test */
    public function lancamento_novo_nao_paga_despesa_de_outro_usuario()
    {
        $this->actingAs($this->outro)->postJson(route('lancamentos.store'), [
            'descricao' => 'Pagando o aluguel alheio',
            'valor' => '1.200,00',
            'data_lancamento' => '21/11/2026',
            'despesa_id' => $this->despesa->id,
        ])
            ->assertStatus(422)
            ->assertJsonValidationErrors('despesa_id');

        $this->listaDeDespesas($this->dono)
            ->assertSee('Declarar pagamento')
            ->assertDontSee('Remover Pagamento');
    }

    //DADO ANTIGO: ANTES DA #15 QUALQUER USUÁRIO LIGAVA SEU LANÇAMENTO À DESPESA DE OUTRO; NENHUMA ROTA CRIA ISSO HOJE
    private function lancamentoAntigoDoOutroNaDespesaDoDono()
    {
        return Lancamento::create([
            'user_id' => $this->outro->id,
            'despesa_id' => $this->despesa->id,
            'valor' => 1200,
            'descricao' => 'Lançamento antigo do outro',
            'data_lancamento' => date('Y-m-d H:i:s'),
        ]);
    }

    /** @test */
    public function excluir_lancamento_antigo_ligado_a_despesa_de_outro_usuario_nao_muda_o_pagamento_dela()
    {
        $this->pagarDespesa($this->dono, $this->despesa);
        $antigo = $this->lancamentoAntigoDoOutroNaDespesaDoDono();

        $this->actingAs($this->outro)
            ->deleteJson(route('lancamentos.delete', $antigo->id))
            ->assertStatus(200);

        $this->listaDeDespesas($this->dono)->assertSee('Remover Pagamento');
    }

    /** @test */
    public function remover_o_pagamento_nao_apaga_lancamento_antigo_de_outro_usuario()
    {
        $this->pagarDespesa($this->dono, $this->despesa);
        $this->lancamentoAntigoDoOutroNaDespesaDoDono();

        $this->actingAs($this->dono)
            ->putJson(route('despesa.pay', $this->despesa->id))
            ->assertJsonFragment(['msg' => 'Remoção de Pagamento efetuado com sucesso!']);

        $this->listaDeLancamentos($this->outro)->assertSee('Lançamento antigo do outro');
    }

    /** @test */
    public function lancamento_novo_com_despesa_id_que_nao_e_um_numero_e_recusado()
    {
        $this->actingAs($this->dono)->postJson(route('lancamentos.store'), [
            'descricao' => 'Aluguel de novembro',
            'valor' => '1.200,00',
            'data_lancamento' => date('d/m/Y'),
            'despesa_id' => [$this->despesa->id],
        ])
            ->assertStatus(422)
            ->assertJsonValidationErrors('despesa_id');
    }

    /** @test */
    public function dono_exclui_o_proprio_lancamento_e_a_despesa_volta_a_ficar_a_pagar()
    {
        $lancamento = $this->pagarDespesa($this->dono, $this->despesa);

        $this->actingAs($this->dono)
            ->deleteJson(route('lancamentos.delete', $lancamento->id))
            ->assertStatus(200)
            ->assertJsonFragment(['error' => false]);

        $this->listaDeLancamentos($this->dono)->assertDontSee('Aluguel');
        $this->listaDeDespesas($this->dono)->assertSee('Declarar pagamento');
    }

    /** @test */
    public function dono_lanca_o_pagamento_da_propria_despesa()
    {
        $this->actingAs($this->dono)->postJson(route('lancamentos.store'), [
            'descricao' => 'Aluguel de novembro',
            'valor' => '1.200,00',
            'data_lancamento' => date('d/m/Y'),
            'despesa_id' => $this->despesa->id,
        ])
            ->assertStatus(200)
            ->assertJsonFragment(['error' => false]);

        $this->listaDeLancamentos($this->dono)->assertSee('Aluguel de novembro');
        $this->listaDeDespesas($this->dono)->assertSee('Remover Pagamento');
    }
}

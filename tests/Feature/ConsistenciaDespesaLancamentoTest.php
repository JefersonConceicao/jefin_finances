<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\User;
use App\Models\Despesa;
use App\Models\Lancamento;
use App\Models\TiposDespesa;
use Illuminate\Support\Facades\Log;

class ConsistenciaDespesaLancamentoTest extends TestCase
{
    use RefreshDatabase;

    protected $user;
    protected $despesa;

    protected function setUp(): void
    {
        parent::setUp();

        $this->withoutMiddleware(\App\Http\Middleware\VerifyCsrfToken::class);

        $this->user = factory(User::class)->create();
        $this->despesa = $this->cadastrarDespesa('Aluguel', '1.200,00');
    }

    private function cadastrarDespesa($nome, $valor)
    {
        $tipo = TiposDespesa::create(['nome' => 'Moradia', 'ativo' => 1, 'user_id' => $this->user->id]);

        $this->actingAs($this->user)->postJson(route('despesas.store'), [
            'nome_despesa' => $nome,
            'despesa_tipo_id' => $tipo->id,
            'valor_total' => $valor,
            'ativo' => 1,
            'data_vencimento' => '05/11/2026',
        ])->assertJsonFragment(['error' => false]);

        return Despesa::where('nome_despesa', $nome)->first();
    }

    private function pagarDespesa($despesa)
    {
        return $this->actingAs($this->user)->putJson(route('despesa.pay', $despesa->id));
    }

    private function listaDeDespesas()
    {
        return $this->actingAs($this->user)->get(route('despesas.index', ['mes' => '11', 'ano' => '2026']));
    }

    private function listaDeLancamentos()
    {
        //O PAGAMENTO LANÇA NA DATA DE HOJE
        return $this->actingAs($this->user)->get(route('lancamentos.index', ['mes' => date('m'), 'ano' => date('Y')]));
    }

    /** @test */
    public function excluir_despesa_paga_exclui_o_lancamento_dela_e_o_total_do_mes_nao_conta_mais_o_valor()
    {
        $this->pagarDespesa($this->despesa)->assertJsonFragment(['msg' => 'Pagamento registrado']);
        $this->listaDeLancamentos()->assertSee('Gastos: R$ 1.200,00');

        $this->actingAs($this->user)
            ->deleteJson(route('despesas.delete', $this->despesa->id))
            ->assertJsonFragment(['error' => false]);

        $this->listaDeLancamentos()
            ->assertDontSee('Aluguel')
            ->assertSee('Gastos: R$ 0,00');
    }

    private function falharAo($model, $evento)
    {
        $model::$evento(function () {
            throw new \RuntimeException('Falha simulada no banco');
        });
    }

    /** @test */
    public function se_a_exclusao_da_despesa_falhar_o_lancamento_dela_continua()
    {
        $this->pagarDespesa($this->despesa)->assertJsonFragment(['msg' => 'Pagamento registrado']);
        $this->falharAo(Despesa::class, 'deleting');

        $this->actingAs($this->user)
            ->deleteJson(route('despesas.delete', $this->despesa->id))
            ->assertJsonFragment(['error' => true])
            ->assertDontSee('Falha simulada');

        $this->listaDeLancamentos()
            ->assertSee('Aluguel')
            ->assertSee('Gastos: R$ 1.200,00');
    }

    /** @test */
    public function se_o_lancamento_do_pagamento_falhar_a_despesa_continua_a_pagar()
    {
        $this->falharAo(Lancamento::class, 'creating');

        $this->pagarDespesa($this->despesa)->assertJsonFragment(['error' => true])
            ->assertDontSee('Falha simulada');

        $this->listaDeDespesas()
            ->assertSee('Declarar pagamento')
            ->assertDontSee('Remover Pagamento');
    }

    /** @test */
    public function se_a_remocao_do_pagamento_falhar_a_despesa_continua_paga_com_o_lancamento()
    {
        $this->pagarDespesa($this->despesa)->assertJsonFragment(['msg' => 'Pagamento registrado']);
        //FALHA DEPOIS DE GRAVAR O pago = 0
        $this->falharAo(Despesa::class, 'updated');

        $this->pagarDespesa($this->despesa)->assertJsonFragment(['error' => true])
            ->assertDontSee('Falha simulada');

        $this->listaDeDespesas()->assertSee('Remover Pagamento');
        $this->listaDeLancamentos()->assertSee('Gastos: R$ 1.200,00');
    }

    /** @test */
    public function lancamento_novo_para_despesa_ja_paga_e_recusado()
    {
        $this->pagarDespesa($this->despesa)->assertJsonFragment(['msg' => 'Pagamento registrado']);

        $this->actingAs($this->user)->postJson(route('lancamentos.store'), [
            'descricao' => 'Aluguel pago de novo',
            'valor' => '1.200,00',
            'data_lancamento' => date('d/m/Y'),
            'despesa_id' => $this->despesa->id,
        ])
            ->assertStatus(422)
            ->assertJsonValidationErrors('despesa_id');

        $this->listaDeLancamentos()
            ->assertDontSee('Aluguel pago de novo')
            ->assertSee('Gastos: R$ 1.200,00');
    }

    /** @test */
    public function se_a_exclusao_do_lancamento_falhar_a_despesa_continua_paga()
    {
        $this->pagarDespesa($this->despesa)->assertJsonFragment(['msg' => 'Pagamento registrado']);
        $lancamento = Lancamento::where('despesa_id', $this->despesa->id)->first();
        $this->falharAo(Lancamento::class, 'deleting');

        $this->actingAs($this->user)
            ->deleteJson(route('lancamentos.delete', $lancamento->id))
            ->assertJsonFragment(['error' => true])
            ->assertDontSee('Falha simulada');

        $this->listaDeDespesas()->assertSee('Remover Pagamento');
        $this->listaDeLancamentos()->assertSee('Gastos: R$ 1.200,00');
    }

    //DADO ANTIGO: LANÇAMENTO LIGADO À DESPESA SEM PASSAR PELO PAGAMENTO; NENHUMA ROTA CRIA ISSO HOJE
    private function lancamentoAntigo($user, $descricao)
    {
        return Lancamento::create([
            'user_id' => $user->id,
            'despesa_id' => $this->despesa->id,
            'valor' => 1200,
            'descricao' => $descricao,
            'data_lancamento' => date('Y-m-d H:i:s'),
        ]);
    }

    /** @test */
    public function lancamento_novo_para_despesa_que_ja_tem_lancamento_e_recusado_mesmo_marcada_a_pagar()
    {
        $this->lancamentoAntigo($this->user, 'Aluguel lançado antes');

        $this->actingAs($this->user)->postJson(route('lancamentos.store'), [
            'descricao' => 'Aluguel pago de novo',
            'valor' => '1.200,00',
            'data_lancamento' => date('d/m/Y'),
            'despesa_id' => $this->despesa->id,
        ])
            ->assertStatus(422)
            ->assertJsonValidationErrors('despesa_id');

        $this->listaDeLancamentos()->assertSee('Gastos: R$ 1.200,00');
    }

    /** @test */
    public function declarar_pagamento_de_despesa_que_ja_tem_lancamento_nao_lanca_de_novo()
    {
        $this->lancamentoAntigo($this->user, 'Aluguel lançado antes');

        $this->pagarDespesa($this->despesa)->assertJsonFragment(['msg' => 'Pagamento registrado']);

        $this->listaDeDespesas()->assertSee('Remover Pagamento');
        $this->listaDeLancamentos()->assertSee('Gastos: R$ 1.200,00');
    }

    /** @test */
    public function se_marcar_a_despesa_como_paga_falhar_o_lancamento_novo_nao_fica()
    {
        $this->falharAo(Despesa::class, 'updated');

        $this->actingAs($this->user)->postJson(route('lancamentos.store'), [
            'descricao' => 'Aluguel de novembro',
            'valor' => '1.200,00',
            'data_lancamento' => date('d/m/Y'),
            'despesa_id' => $this->despesa->id,
        ])->assertJsonFragment(['error' => true])
            ->assertDontSee('Falha simulada');

        $this->listaDeDespesas()->assertSee('Declarar pagamento');
        $this->listaDeLancamentos()
            ->assertDontSee('Aluguel de novembro')
            ->assertSee('Gastos: R$ 0,00');
    }

    /** @test */
    public function excluir_um_lancamento_enquanto_resta_outro_da_despesa_nao_muda_o_pagamento()
    {
        $this->pagarDespesa($this->despesa)->assertJsonFragment(['msg' => 'Pagamento registrado']);
        $duplicado = $this->lancamentoAntigo($this->user, 'Aluguel duplicado');

        $this->actingAs($this->user)
            ->deleteJson(route('lancamentos.delete', $duplicado->id))
            ->assertJsonFragment(['error' => false]);

        $this->listaDeDespesas()->assertSee('Remover Pagamento');
    }

    /** @test */
    public function excluir_despesa_mantem_lancamento_antigo_de_outro_usuario_ligado_a_ela()
    {
        $outro = factory(User::class)->create();
        $this->pagarDespesa($this->despesa)->assertJsonFragment(['msg' => 'Pagamento registrado']);
        $this->lancamentoAntigo($outro, 'Lançamento antigo do outro');

        $this->actingAs($this->user)
            ->deleteJson(route('despesas.delete', $this->despesa->id))
            ->assertJsonFragment(['error' => false]);

        $this->actingAs($outro)
            ->get(route('lancamentos.index', ['mes' => date('m'), 'ano' => date('Y')]))
            ->assertSee('Lançamento antigo do outro')
            ->assertSee('Gastos: R$ 1.200,00');
    }

    /** @test */
    public function lancamento_para_despesa_paga_de_outro_usuario_nao_revela_que_ela_esta_paga()
    {
        $outro = factory(User::class)->create();
        $this->pagarDespesa($this->despesa)->assertJsonFragment(['msg' => 'Pagamento registrado']);

        $resposta = $this->actingAs($outro)->postJson(route('lancamentos.store'), [
            'descricao' => 'Sondando a despesa alheia',
            'valor' => '1,00',
            'data_lancamento' => date('d/m/Y'),
            'despesa_id' => $this->despesa->id,
        ])->assertStatus(422);

        //A MESMA RESPOSTA DE UMA DESPESA QUE NÃO EXISTE
        $this->assertSame(['Despesa inválida'], $resposta->json('errors.despesa_id'));
    }

    /** @test */
    public function despesa_nova_nasce_a_pagar_mesmo_se_o_formulario_mandar_pago()
    {
        $tipo = TiposDespesa::create(['nome' => 'Lazer', 'ativo' => 1, 'user_id' => $this->user->id]);

        $this->actingAs($this->user)->postJson(route('despesas.store'), [
            'nome_despesa' => 'Cinema',
            'despesa_tipo_id' => $tipo->id,
            'valor_total' => '50,00',
            'ativo' => 1,
            'pago' => 1,
            'data_vencimento' => '05/11/2026',
        ])->assertJsonFragment(['error' => false]);

        $this->listaDeDespesas()->assertDontSee('Remover Pagamento');
    }

    /** @test */
    public function falha_no_pagamento_registra_a_causa_no_log()
    {
        Log::spy();
        $this->falharAo(Lancamento::class, 'creating');

        $this->pagarDespesa($this->despesa)->assertJsonFragment(['error' => true]);

        Log::shouldHaveReceived('error')->once();
    }
}

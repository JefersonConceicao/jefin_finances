<?php

namespace Tests\Feature;

use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\User;
use App\Models\Despesa;
use App\Models\DespesaFixa;
use App\Models\Lancamento;
use App\Models\Proventos;

/**
 * Tela de Despesas Fixas (issue #30): Comprometimento da Renda e exclusão com todas as Ocorrências.
 */
class DespesasFixasTelaTest extends TestCase
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

    private function cadastrarDespesaFixa($nome, $valor, array $dados = [])
    {
        $this->actingAs($this->user)->postJson(route('despesasFixas.store'), array_merge([
            'nome_despesa' => $nome,
            'valor' => $valor,
            'ativo' => 1,
            'mes_inicio' => '2026-11',
            'dia_vencimento' => 10,
        ], $dados))->assertJsonFragment(['error' => false]);

        return DespesaFixa::where('nome_despesa', $nome)->firstOrFail();
    }

    private function provento($valor, $data = '2026-11-05')
    {
        Proventos::create(['user_id' => $this->user->id, 'descricao_provento' => 'Salário', 'valor_provento' => $valor, 'data_provento' => $data]);
    }

    private function tela()
    {
        return $this->actingAs($this->user)->get(route('despesasFixas.index'))->assertOk();
    }

    /** @test */
    public function comprometimento_da_renda_com_despesas_fixas_no_topo_e_em_cada_linha()
    {
        $this->provento(4000);
        $this->provento(9999, '2026-12-05');
        $this->cadastrarDespesaFixa('Aluguel', '1.000,00');
        $this->cadastrarDespesaFixa('Internet', '200,00');

        $this->tela()
            ->assertViewHas('proventosDoMes', 4000)
            ->assertViewHas('comprometimento', 30)
            ->assertSee('Comprometimento da Renda com Despesas Fixas (30,0%)')
            ->assertSeeInOrder(['Aluguel', '25,0%', 'Internet', '5,0%']);
    }

    /** @test */
    public function sem_proventos_no_mes_nao_divide_por_zero()
    {
        $this->cadastrarDespesaFixa('Aluguel', '1.000,00');

        $this->tela()
            ->assertViewHas('comprometimento', null)
            ->assertSee('sem Proventos no mês');
    }

    /** @test */
    public function excluir_apaga_a_despesa_fixa_e_todas_as_ocorrencias_quando_nenhuma_esta_paga()
    {
        $financiamento = $this->cadastrarDespesaFixa('Financiamento', '500,00', ['mes_inicio' => '2026-12', 'parcelas' => 10]);
        $this->assertSame(10, Despesa::where('despesa_fixa_id', $financiamento->id)->count());

        $this->actingAs($this->user)->deleteJson(route('despesasFixas.delete', $financiamento->id))
            ->assertOk()->assertJsonFragment(['error' => false]);

        $this->assertNull(DespesaFixa::find($financiamento->id));
        $this->assertSame(0, Despesa::withoutGlobalScopes()->where('nome_despesa', 'LIKE', 'Financiamento%')->count());
    }

    /** @test */
    public function com_ocorrencia_paga_nada_e_apagado_e_a_mensagem_indica_encerrar()
    {
        $financiamento = $this->cadastrarDespesaFixa('Financiamento', '500,00', ['parcelas' => 10]);
        $primeira = Despesa::where('despesa_fixa_id', $financiamento->id)->orderBy('data_vencimento')->firstOrFail();
        $this->actingAs($this->user)->putJson(route('despesa.pay', $primeira->id))->assertJsonFragment(['error' => false]);

        $this->actingAs($this->user)->deleteJson(route('despesasFixas.delete', $financiamento->id))
            ->assertOk()
            ->assertJsonFragment(['error' => true, 'msg' => DespesaFixa::MSG_EXCLUIR_COM_PAGA]);

        $this->assertNotNull(DespesaFixa::find($financiamento->id));
        $this->assertSame(10, Despesa::where('despesa_fixa_id', $financiamento->id)->count());
        $this->assertSame(1, Lancamento::where('despesa_id', $primeira->id)->count());
    }

    /** @test */
    public function o_botao_de_excluir_aparece_tambem_para_quem_tem_ocorrencias()
    {
        $this->cadastrarDespesaFixa('Financiamento', '500,00', ['parcelas' => 10]);

        $this->tela()->assertSee('btnDeleteDespesaFixa', false)->assertSee('data-ocorrencias="10"', false);
    }

    /** @test */
    public function outro_usuario_nao_exclui()
    {
        $aluguel = $this->cadastrarDespesaFixa('Aluguel', '1.000,00');

        $this->actingAs(factory(User::class)->create())->deleteJson(route('despesasFixas.delete', $aluguel->id))->assertNotFound();
        $this->assertNotNull(DespesaFixa::find($aluguel->id));
    }

    /** @test */
    public function percentual_por_linha_so_para_as_vigentes_no_mes_atual()
    {
        $this->provento(4000);
        $this->cadastrarDespesaFixa('Aluguel', '1.000,00');
        $this->cadastrarDespesaFixa('Seguro', '400,00', ['mes_inicio' => '2027-01']);
        DespesaFixa::create(['user_id' => $this->user->id, 'nome_despesa' => 'Curso', 'valor' => 800, 'ativo' => 1,
            'mes_inicio' => '2026-01-01', 'mes_fim' => '2026-03-01', 'dia_vencimento' => 5]);

        $this->tela()
            ->assertSee('Comprometimento da Renda com Despesas Fixas (25,0%)')
            ->assertSeeInOrder(['Aluguel', '25,0%'])
            ->assertDontSee('10,0%')
            ->assertDontSee('20,0%');
    }
}

<?php

namespace Tests\Feature;

use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\User;
use App\Models\Despesa;
use App\Models\TiposDespesa;

class VencimentoDespesaTest extends TestCase
{
    use RefreshDatabase;

    protected $user;
    protected $tipo;

    protected function setUp(): void
    {
        parent::setUp();

        $this->withoutMiddleware(\App\Http\Middleware\VerifyCsrfToken::class);

        Carbon::setTestNow(Carbon::create(2026, 11, 21, 10, 0, 0));

        $this->user = factory(User::class)->create();
        $this->tipo = TiposDespesa::create(['nome' => 'Moradia', 'ativo' => 1, 'user_id' => $this->user->id]);
    }

    protected function tearDown(): void
    {
        Carbon::setTestNow();
        parent::tearDown();
    }

    private function cadastrarDespesaVariavel(array $dados = [])
    {
        return $this->actingAs($this->user)->postJson(route('despesas.store'), array_merge([
            'nome_despesa' => 'Conta de luz',
            'despesa_tipo_id' => $this->tipo->id,
            'valor_total' => '150,00',
            'ativo' => 1,
        ], $dados));
    }

    private function listarDespesas($mes, $ano)
    {
        return $this->actingAs($this->user)->get(route('despesas.index', ['mes' => $mes, 'ano' => $ano]));
    }

    /** @test */
    public function despesa_variavel_cadastrada_sem_data_recebe_vencimento_de_hoje()
    {
        $this->cadastrarDespesaVariavel()
            ->assertStatus(200)
            ->assertJsonFragment(['error' => false]);

        $this->listarDespesas('11', '2026')
            ->assertStatus(200)
            ->assertSeeInOrder(['Vencimento', '21/11/2026', 'Conta de luz']);
    }

    /** @test */
    public function despesa_com_vencimento_em_janeiro_aparece_em_janeiro_e_nao_no_mes_do_cadastro()
    {
        $this->cadastrarDespesaVariavel(['nome_despesa' => 'IPVA', 'data_vencimento' => '15/01/2027'])
            ->assertStatus(200)
            ->assertJsonFragment(['error' => false]);

        $this->listarDespesas('01', '2027')
            ->assertSeeInOrder(['15/01/2027', 'IPVA']);

        $this->listarDespesas('11', '2026')
            ->assertDontSee('IPVA');
    }

    /** @test */
    public function alterar_o_vencimento_move_a_despesa_para_o_mes_do_novo_vencimento()
    {
        $this->cadastrarDespesaVariavel(['nome_despesa' => 'Seguro', 'data_vencimento' => '21/11/2026']);
        $despesa = Despesa::where('nome_despesa', 'Seguro')->first();

        $this->actingAs($this->user)->putJson(route('despesas.update', $despesa->id), [
            'nome_despesa' => 'Seguro',
            'despesa_tipo_id' => $this->tipo->id,
            'valor_total' => '150,00',
            'ativo' => 1,
            'data_vencimento' => '10/12/2026',
        ])
            ->assertStatus(200)
            ->assertJsonFragment(['error' => false]);

        $this->listarDespesas('12', '2026')
            ->assertSeeInOrder(['10/12/2026', 'Seguro']);

        $this->listarDespesas('11', '2026')
            ->assertDontSee('Seguro');
    }

    /** @test */
    public function despesa_antiga_sem_vencimento_continua_no_mes_da_data_de_cadastro()
    {
        //REGISTRO ANTERIOR AO VENCIMENTO: NENHUMA ROTA CRIA MAIS DESPESA SEM ELE
        Despesa::create([
            'user_id' => $this->user->id,
            'nome_despesa' => 'Mercado antigo',
            'despesa_tipo_id' => $this->tipo->id,
            'valor_total' => 80,
            'ativo' => 1,
            'created_at' => '2026-03-10 12:00:00',
        ]);

        $this->listarDespesas('03', '2026')
            ->assertSeeInOrder(['10/03/2026', 'Mercado antigo']);

        $this->listarDespesas('11', '2026')
            ->assertDontSee('Mercado antigo');
    }
}

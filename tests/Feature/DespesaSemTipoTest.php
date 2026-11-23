<?php

namespace Tests\Feature;

use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\User;
use App\Models\Despesa;
use App\Models\DespesaFixa;

/**
 * Uma Despesa é só Fixa ou Variável: não há Tipo de Despesa (issue #26).
 */
class DespesaSemTipoTest extends TestCase
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

    /** @test */
    public function cadastra_despesa_variavel_sem_tipo()
    {
        $this->actingAs($this->user)->postJson(route('despesas.store'), [
            'nome_despesa' => 'Farmácia',
            'valor_total' => '80,00',
            'ativo' => 1,
            'data_vencimento' => '15/11/2026',
        ])->assertOk()->assertJson(['error' => false]);

        $despesa = Despesa::where('nome_despesa', 'Farmácia')->firstOrFail();
        $this->assertNull($despesa->despesa_tipo_id);
    }

    /** @test */
    public function altera_despesa_variavel_sem_tipo()
    {
        $despesa = Despesa::create([
            'user_id' => $this->user->id,
            'nome_despesa' => 'Farmácia',
            'valor_total' => 80,
            'data_vencimento' => '2026-11-15',
            'ativo' => 1,
        ]);

        $this->actingAs($this->user)->putJson(route('despesas.update', $despesa->id), [
            'nome_despesa' => 'Farmácia e drogaria',
            'valor_total' => '90,00',
            'ativo' => 1,
            'data_vencimento' => '15/11/2026',
        ])->assertOk()->assertJson(['error' => false]);

        $this->assertSame('Farmácia e drogaria', $despesa->fresh()->nome_despesa);
    }

    /** @test */
    public function cadastra_despesa_fixa_e_ocorrencias_sem_tipo()
    {
        $this->actingAs($this->user)->postJson(route('despesasFixas.store'), [
            'nome_despesa' => 'Aluguel',
            'valor' => '1.500,00',
            'ativo' => 1,
            'mes_inicio' => '2026-11',
            'dia_vencimento' => 10,
            'parcelas' => 2,
        ])->assertOk()->assertJson(['error' => false]);

        $fixa = DespesaFixa::firstOrFail();
        $this->assertNull($fixa->despesa_tipo_id);
        $this->assertSame(2, Despesa::where('despesa_fixa_id', $fixa->id)->whereNull('despesa_tipo_id')->count());
    }

    /** @test */
    public function o_cadastro_de_tipos_nao_existe_mais()
    {
        $this->actingAs($this->user)->get('/tiposDespesas')->assertNotFound();
    }

    /** @test */
    public function telas_nao_mostram_tipo()
    {
        Despesa::create([
            'user_id' => $this->user->id,
            'nome_despesa' => 'Farmácia',
            'valor_total' => 80,
            'data_vencimento' => '2026-11-15',
            'ativo' => 1,
        ]);

        $this->actingAs($this->user)->get(route('despesas.index', ['mes' => 11, 'ano' => 2026]))
            ->assertOk()
            ->assertSee('Farmácia')
            ->assertDontSee('<th> Tipo </th>', false)
            ->assertDontSee('despesa_tipo_id', false);

        $this->actingAs($this->user)->get(route('despesas.create'))
            ->assertOk()
            ->assertDontSee('despesa_tipo_id', false);
    }
}

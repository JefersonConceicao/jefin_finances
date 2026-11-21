<?php

namespace Tests\Feature;

use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\User;
use App\Models\Despesa;
use App\Models\TiposDespesa;

class ProjecaoTest extends TestCase
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

    private function projecao(array $parametros = [])
    {
        return $this->actingAs($this->user)->get(route('projecao.index', $parametros))->assertOk();
    }

    private function cadastrarDespesaVariavel($nome, $valor, $vencimento)
    {
        $tipo = TiposDespesa::firstOrCreate(['nome' => 'Impostos', 'user_id' => $this->user->id], ['ativo' => 1]);

        $this->actingAs($this->user)->postJson(route('despesas.store'), [
            'nome_despesa' => $nome,
            'despesa_tipo_id' => $tipo->id,
            'valor_total' => $valor,
            'ativo' => 1,
            'data_vencimento' => $vencimento,
        ])->assertJsonFragment(['error' => false]);
    }

    private function cadastrarDespesaFixa(array $dados)
    {
        $this->actingAs($this->user)->postJson(route('despesasFixas.store'), array_merge([
            'valor' => '1.000,00',
            'ativo' => 1,
            'mes_inicio' => '2026-11',
            'dia_vencimento' => 10,
        ], $dados))->assertJsonFragment(['error' => false]);
    }

    private function cadastrarProventoFixo(array $dados)
    {
        $this->actingAs($this->user)->postJson(route('proventosFixos.store'), array_merge([
            'mes_inicio' => '2026-11',
        ], $dados))->assertJsonFragment(['error' => false]);
    }

    private function meses($resposta)
    {
        return $resposta->viewData('meses');
    }

    /** @test */
    public function sem_parametro_mostra_o_mes_atual_e_mais_6_meses_e_com_12_o_mes_atual_e_mais_12()
    {
        $meses = $this->meses($this->projecao());
        $this->assertCount(7, $meses);
        $this->assertSame(['11', '2026'], [$meses[0]['mes'], $meses[0]['ano']]);
        $this->assertSame(['05', '2027'], [$meses[6]['mes'], $meses[6]['ano']]);

        $this->assertCount(13, $this->meses($this->projecao(['horizonte' => 12])));
        $this->assertCount(4, $this->meses($this->projecao(['horizonte' => 3])));
        $this->assertCount(7, $this->meses($this->projecao(['horizonte' => 5])));
    }

    /** @test */
    public function despesa_variavel_com_vencimento_em_3_meses_aparece_no_total_e_na_lista_daquele_mes()
    {
        $this->cadastrarDespesaVariavel('IPVA', '1.200,00', '15/02/2027');

        $fevereiro = $this->meses($this->projecao())[3];

        $this->assertSame('02', $fevereiro['mes']);
        $this->assertEquals(1200, $fevereiro['variaveis']);
        $this->assertEquals(1200, $fevereiro['total']);
        $this->assertSame(['IPVA'], $fevereiro['listaVariaveis']->pluck('nome_despesa')->all());
    }

    /** @test */
    public function ocorrencia_excluida_de_um_mes_futuro_nao_entra_no_total_daquele_mes()
    {
        $this->cadastrarDespesaFixa(['nome_despesa' => 'Academia']);
        $marco = Despesa::where('data_vencimento', '2027-03-10')->first();
        $this->actingAs($this->user)->deleteJson(route('despesas.delete', $marco->id))->assertJsonFragment(['error' => false]);

        $meses = $this->meses($this->projecao());

        $this->assertEquals(1000, $meses[3]['fixas']);
        $this->assertEquals(0, $meses[4]['fixas']);
        $this->assertEquals(0, $meses[4]['total']);
        $this->assertEquals(1000, $meses[5]['fixas']);
    }

    /** @test */
    public function saldo_do_mes_e_saldo_acumulado_somando_desde_o_mes_atual()
    {
        $this->cadastrarProventoFixo(['descricao' => 'Salário', 'valor' => '5.000,00']);
        $this->cadastrarDespesaFixa(['nome_despesa' => 'Aluguel']);
        $this->cadastrarDespesaVariavel('Presentes', '300,00', '20/12/2026');

        $meses = $this->meses($this->projecao(['horizonte' => 3]));

        $this->assertEquals([5000, 5000, 5000, 5000], array_column($meses, 'proventos'));
        $this->assertEquals([4000, 3700, 4000, 4000], array_column($meses, 'saldo'));
        $this->assertEquals([4000, 7700, 11700, 15700], array_column($meses, 'saldoAcumulado'));
        $this->assertArrayNotHasKey('receitas', $meses[0]);
    }
}

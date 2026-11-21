<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\User;
use App\Models\TiposDespesa;

class TipoDespesaReservadoTest extends TestCase
{
    use RefreshDatabase;

    protected $user;

    protected function setUp(): void
    {
        parent::setUp();

        $this->withoutMiddleware(\App\Http\Middleware\VerifyCsrfToken::class);

        $this->user = factory(User::class)->create();
    }

    private function cadastrarDespesaFixa($nome)
    {
        $this->actingAs($this->user)->postJson(route('despesasFixas.store'), [
            'nome_despesa' => $nome,
            'valor' => '1.500,00',
            'ativo' => 1,
            'mes_inicio' => date('Y-m'),
            'dia_vencimento' => 10,
        ])->assertJsonFragment(['error' => false]);
    }

    private function despesasDoMesComTipo($tipoId)
    {
        return $this->actingAs($this->user)->get(route('despesas.index', [
            'mes' => date('m'),
            'ano' => date('Y'),
            'despesa_tipo_id' => $tipoId,
        ]));
    }

    /** @test */
    public function despesa_fixa_usa_o_tipo_reservado_e_nao_um_tipo_do_usuario_com_nome_parecido()
    {
        $fixoCasa = TiposDespesa::create(['nome' => 'Fixo Casa', 'ativo' => 1, 'user_id' => $this->user->id]);

        $this->cadastrarDespesaFixa('Aluguel');

        $this->despesasDoMesComTipo($fixoCasa->id)->assertDontSee('Aluguel');
        $this->despesasDoMesComTipo(null)->assertSee('<b> Fixa </b>', false);
    }

    private function tipoReservado()
    {
        $this->cadastrarDespesaFixa('Aluguel');

        //SÓ PARA SABER O id; O COMPORTAMENTO É CONFERIDO PELAS ROTAS
        return TiposDespesa::where('user_id', $this->user->id)->where('nome', 'Fixa')->first();
    }

    /** @test */
    public function excluir_o_tipo_reservado_e_recusado()
    {
        $fixa = $this->tipoReservado();

        $this->actingAs($this->user)
            ->deleteJson(route('tiposDespesas.delete', $fixa->id))
            ->assertJsonFragment(['error' => true, 'msg' => 'O tipo "Fixa" é reservado às Despesas Fixas e não pode ser alterado nem excluído']);

        $this->actingAs($this->user)->get(route('tiposDespesas.index'))->assertSee('Fixa');
    }

    /** @test */
    public function renomear_o_tipo_reservado_e_recusado()
    {
        $fixa = $this->tipoReservado();

        $this->actingAs($this->user)
            ->putJson(route('tiposDespesas.update', $fixa->id), ['nome' => 'Contas', 'ativo' => 1])
            ->assertJsonFragment(['error' => true, 'msg' => 'O tipo "Fixa" é reservado às Despesas Fixas e não pode ser alterado nem excluído']);

        $this->actingAs($this->user)->get(route('tiposDespesas.index'))
            ->assertSee('Fixa')
            ->assertDontSee('Contas');
    }

    /** @test */
    public function o_tipo_reservado_nao_aparece_nas_opcoes_da_despesa_variavel()
    {
        $fixa = $this->tipoReservado();
        $moradia = TiposDespesa::create(['nome' => 'Moradia', 'ativo' => 1, 'user_id' => $this->user->id]);

        $this->actingAs($this->user)->get(route('despesas.create'))
            ->assertSee('<option value="' . $moradia->id . '"> Moradia </option>', false)
            ->assertDontSee('<option value="' . $fixa->id . '"> Fixa </option>', false);

        $this->actingAs($this->user)->getJson(route('tiposDespesas.optionsDespesas'))
            ->assertJsonFragment(['nome' => 'Moradia'])
            ->assertJsonMissing(['nome' => 'Fixa']);
    }

    /** @test */
    public function despesa_variavel_nao_aceita_o_tipo_reservado()
    {
        $fixa = $this->tipoReservado();

        $this->actingAs($this->user)->postJson(route('despesas.store'), [
            'nome_despesa' => 'Mercado',
            'despesa_tipo_id' => $fixa->id,
            'valor_total' => '300,00',
            'ativo' => 1,
            'data_vencimento' => '10/11/2026',
        ])
            ->assertStatus(422)
            ->assertJsonValidationErrors('despesa_tipo_id');
    }
}

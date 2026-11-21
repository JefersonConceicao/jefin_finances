<?php

namespace Tests\Feature;

use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\User;
use App\Models\Despesa;
use App\Models\DespesaFixa;

class OcorrenciaDespesaFixaEscopoTest extends TestCase
{
    use RefreshDatabase;

    protected $user;
    protected $fixa;

    protected function setUp(): void
    {
        parent::setUp();

        $this->withoutMiddleware(\App\Http\Middleware\VerifyCsrfToken::class);

        $this->hoje(2026, 1, 5);
        $this->user = factory(User::class)->create();

        //ALUGUEL DE R$ 1.500,00, SEM FIM, VENCE DIA 10: OCORRÊNCIAS DE JAN/2026 A DEZ/2026
        $this->actingAs($this->user)->postJson(route('despesasFixas.store'), [
            'nome_despesa' => 'Aluguel',
            'valor' => '1.500,00',
            'ativo' => 1,
            'mes_inicio' => '2026-01',
            'dia_vencimento' => 10,
        ])->assertJsonFragment(['error' => false]);

        $this->fixa = DespesaFixa::first();
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
    private function ocorrencia($mes, $ano = 2026)
    {
        return Despesa::where('despesa_fixa_id', $this->fixa->id)
            ->whereMonth('data_vencimento', $mes)
            ->whereYear('data_vencimento', $ano)
            ->first();
    }

    private function alterarOcorrencia($mes, $escopo, array $dados = [])
    {
        $ocorrencia = $this->ocorrencia($mes);

        return $this->actingAs($this->user)->putJson(route('despesas.update', $ocorrencia->id), array_merge([
            'nome_despesa' => 'Aluguel',
            'despesa_tipo_id' => $ocorrencia->despesa_tipo_id,
            'valor_total' => '1.500,00',
            'ativo' => 1,
            'data_vencimento' => sprintf('10/%02d/2026', $mes),
            'escopo' => $escopo,
        ], $dados));
    }

    private function despesasDoMes($mes, $ano = 2026)
    {
        return $this->actingAs($this->user)->get(route('despesas.index', ['mes' => $mes, 'ano' => $ano]));
    }

    private function listaDeFixas()
    {
        return $this->actingAs($this->user)->get(route('despesasFixas.index'));
    }

    /** @test */
    public function ajustar_so_marco_nao_muda_a_fixa_nem_os_outros_meses()
    {
        $this->alterarOcorrencia(3, 'so_esta', ['valor_total' => '230,00'])
            ->assertJsonFragment(['error' => false]);

        $this->despesasDoMes(3)->assertSee('230,00');
        $this->despesasDoMes(2)->assertSee('1.500,00');
        $this->despesasDoMes(4)->assertSee('1.500,00');
        $this->listaDeFixas()->assertSee('1.500,00')->assertDontSee('230,00');
    }

    /** @test */
    public function reajuste_de_fevereiro_em_diante_muda_fevereiro_e_seguintes_inclusive_marco_ajustado_e_nao_muda_janeiro()
    {
        $this->alterarOcorrencia(3, 'so_esta', ['valor_total' => '230,00']);

        $this->alterarOcorrencia(2, 'seguintes', ['valor_total' => '250,00'])
            ->assertJsonFragment(['error' => false]);

        $this->despesasDoMes(1)->assertSee('1.500,00')->assertDontSee('250,00');
        foreach ([2, 3, 4, 12] as $mes) {
            $this->despesasDoMes($mes)->assertSee('250,00')->assertDontSee('230,00');
        }
        $this->listaDeFixas()->assertSee('250,00')->assertDontSee('1.500,00');

        //A JANELA COMPLETADA DEPOIS JÁ USA O NOVO VALOR PREVISTO
        $this->hoje(2026, 2, 5);
        $this->listaDeFixas();
        $this->despesasDoMes(1, 2027)->assertSee('250,00');
    }

    /** @test */
    public function mudar_o_dia_de_vencimento_com_esta_e_as_seguintes_recalcula_os_vencimentos_daquele_mes_em_diante()
    {
        $this->alterarOcorrencia(3, 'seguintes', ['data_vencimento' => '31/03/2026'])
            ->assertJsonFragment(['error' => false]);

        $this->despesasDoMes(2)->assertSee('10/02/2026');
        $this->despesasDoMes(3)->assertSee('31/03/2026');
        $this->despesasDoMes(4)->assertSee('30/04/2026');
        $this->listaDeFixas()->assertSee('Dia 31');
    }

    private function ocorrenciasNoMes($mes, $ano = 2026)
    {
        return substr_count($this->despesasDoMes($mes, $ano)->getContent(), 'Aluguel');
    }

    private function excluirOcorrencia($mes, $escopo)
    {
        return $this->actingAs($this->user)
            ->deleteJson(route('despesas.delete', ['id' => $this->ocorrencia($mes)->id, 'escopo' => $escopo]));
    }

    /** @test */
    public function excluir_so_julho_mantem_os_outros_meses()
    {
        $this->excluirOcorrencia(7, 'so_esta')->assertJsonFragment(['error' => false]);

        $this->assertSame(0, $this->ocorrenciasNoMes(7));
        $this->assertSame(1, $this->ocorrenciasNoMes(6));
        $this->assertSame(1, $this->ocorrenciasNoMes(8));
        $this->listaDeFixas()->assertSee('sem fim');
    }

    /** @test */
    public function excluir_maio_e_as_seguintes_encerra_a_fixa_em_abril_e_a_janela_nao_as_recria()
    {
        $this->excluirOcorrencia(5, 'seguintes')->assertJsonFragment(['error' => false]);

        $this->assertSame(1, $this->ocorrenciasNoMes(4));
        $this->assertSame(0, $this->ocorrenciasNoMes(5));
        $this->assertSame(0, $this->ocorrenciasNoMes(12));
        $this->listaDeFixas()->assertSee('04/2026')->assertDontSee('sem fim');

        $this->hoje(2026, 3, 5);
        $this->listaDeFixas();
        $this->assertSame(0, $this->ocorrenciasNoMes(5));
        $this->assertSame(0, $this->ocorrenciasNoMes(2, 2027));
    }

    /** @test */
    public function encerrar_em_abril_pela_tela_da_fixa_deixa_fim_em_abril_e_sem_ocorrencias_depois()
    {
        $this->actingAs($this->user)
            ->putJson(route('despesasFixas.encerrar', $this->fixa->id), ['mes_fim' => '2026-04'])
            ->assertJsonFragment(['error' => false]);

        $this->assertSame(1, $this->ocorrenciasNoMes(4));
        $this->assertSame(0, $this->ocorrenciasNoMes(5));
        $this->listaDeFixas()->assertSeeInOrder(['01/2026', '04/2026']);

        $this->hoje(2026, 2, 5);
        $this->listaDeFixas();
        $this->assertSame(0, $this->ocorrenciasNoMes(1, 2027));
    }

    /** @test */
    public function encerrar_num_mes_passado_e_recusado()
    {
        $this->hoje(2026, 3, 5);

        $this->actingAs($this->user)
            ->putJson(route('despesasFixas.encerrar', $this->fixa->id), ['mes_fim' => '2026-02'])
            ->assertStatus(422)
            ->assertJsonValidationErrors('mes_fim');
    }

    /** @test */
    public function reajustar_pela_tela_da_fixa_a_partir_de_um_mes()
    {
        $this->actingAs($this->user)
            ->putJson(route('despesasFixas.reajustar', $this->fixa->id), [
                'a_partir_de' => '2026-06',
                'valor' => '1.650,00',
                'dia_vencimento' => 5,
            ])
            ->assertJsonFragment(['error' => false]);

        $this->despesasDoMes(5)->assertSee('1.500,00')->assertSee('10/05/2026');
        $this->despesasDoMes(6)->assertSee('1.650,00')->assertSee('05/06/2026');
        $this->despesasDoMes(12)->assertSee('1.650,00');
        $this->listaDeFixas()->assertSee('1.650,00')->assertSee('Dia 5');
    }

    /** @test */
    public function fixa_com_ocorrencias_pode_ser_alterada_sem_trava()
    {
        $this->actingAs($this->user)
            ->putJson(route('despesasFixas.update', $this->fixa->id), [
                'nome_despesa' => 'Aluguel do apartamento',
                'valor' => '1.500,00',
                'ativo' => 1,
            ])
            ->assertJsonFragment(['error' => false]);

        $this->listaDeFixas()->assertSee('Aluguel do apartamento');
        $this->actingAs($this->user)->get(route('despesasFixas.edit', $this->fixa->id))
            ->assertDontSee('só pode ser')
            ->assertSee('Reajustar')
            ->assertSee('Encerrar');
    }

    private function pagar($mes)
    {
        $this->actingAs($this->user)
            ->putJson(route('despesa.pay', $this->ocorrencia($mes)->id))
            ->assertJsonFragment(['msg' => 'Pagamento registrado']);
    }

    /** @test */
    public function esta_e_as_seguintes_a_partir_de_um_mes_passado_e_recusado()
    {
        $this->hoje(2026, 3, 5);

        $this->alterarOcorrencia(2, 'seguintes', ['valor_total' => '250,00'])
            ->assertJsonFragment(['error' => true, 'msg' => '"Esta e as seguintes" vale só a partir do mês atual']);
        $this->excluirOcorrencia(2, 'seguintes')
            ->assertJsonFragment(['error' => true, 'msg' => '"Esta e as seguintes" vale só a partir do mês atual']);

        $this->despesasDoMes(2)->assertSee('1.500,00');
        $this->despesasDoMes(4)->assertSee('1.500,00');
        $this->listaDeFixas()->assertSee('sem fim');
    }

    /** @test */
    public function encerrar_mantem_a_ocorrencia_ja_paga_depois_do_fim()
    {
        $this->pagar(6);

        $this->actingAs($this->user)
            ->putJson(route('despesasFixas.encerrar', $this->fixa->id), ['mes_fim' => '2026-04'])
            ->assertJsonFragment(['error' => false]);

        $this->despesasDoMes(6)->assertSee('Remover Pagamento');
        $this->assertSame(0, $this->ocorrenciasNoMes(7));
    }

    /** @test */
    public function reajuste_nao_muda_a_ocorrencia_ja_paga()
    {
        $this->pagar(3);

        $this->alterarOcorrencia(2, 'seguintes', ['valor_total' => '250,00'])->assertJsonFragment(['error' => false]);

        $this->despesasDoMes(3)->assertSee('1.500,00')->assertDontSee('250,00');
        $this->despesasDoMes(4)->assertSee('250,00');
    }

    /** @test */
    public function reajuste_que_comeca_depois_da_janela_gravada_so_vale_a_partir_do_mes_escolhido()
    {
        //A JANELA VAI ATÉ JAN/2027; O REAJUSTE COMEÇA EM JUN/2027
        $this->actingAs($this->user)
            ->putJson(route('despesasFixas.reajustar', $this->fixa->id), [
                'a_partir_de' => '2027-06',
                'valor' => '2.000,00',
                'dia_vencimento' => 10,
            ])
            ->assertJsonFragment(['error' => false]);

        $this->hoje(2026, 7, 5);
        $this->listaDeFixas();

        $this->despesasDoMes(2, 2027)->assertSee('1.500,00')->assertDontSee('2.000,00');
        $this->despesasDoMes(5, 2027)->assertSee('1.500,00')->assertDontSee('2.000,00');
        $this->despesasDoMes(6, 2027)->assertSee('2.000,00');
        $this->despesasDoMes(7, 2027)->assertSee('2.000,00');
    }

    /** @test */
    public function com_esta_e_as_seguintes_o_nome_novo_vale_para_esta_ocorrencia_e_mudar_de_mes_e_recusado()
    {
        $this->alterarOcorrencia(3, 'seguintes', ['nome_despesa' => 'Aluguel + condomínio', 'valor_total' => '1.800,00'])
            ->assertJsonFragment(['error' => false]);

        $this->despesasDoMes(3)->assertSee('Aluguel + condomínio')->assertSee('1.800,00');
        $this->despesasDoMes(4)->assertSee('1.800,00');

        $this->alterarOcorrencia(5, 'seguintes', ['data_vencimento' => '10/06/2026'])
            ->assertJsonFragment(['error' => true, 'msg' => 'Para mudar o mês do Vencimento, use "Só esta"']);
        $this->despesasDoMes(5)->assertSee('10/05/2026');
    }

    /** @test */
    public function reajuste_numa_ocorrencia_com_o_dia_cortado_no_fim_do_mes_nao_muda_o_dia_da_fixa()
    {
        $this->alterarOcorrencia(3, 'seguintes', ['data_vencimento' => '31/03/2026']);
        $this->despesasDoMes(4)->assertSee('30/04/2026');

        //ABRIL VENCE DIA 30 PORQUE O MÊS É CURTO; MUDAR SÓ O VALOR NÃO PODE TROCAR O DIA 31 POR 30
        $this->alterarOcorrencia(4, 'seguintes', ['valor_total' => '1.700,00', 'data_vencimento' => '30/04/2026'])
            ->assertJsonFragment(['error' => false]);

        $this->despesasDoMes(5)->assertSee('31/05/2026')->assertSee('1.700,00');
        $this->listaDeFixas()->assertSee('Dia 31');
    }

    /** @test */
    public function esta_e_as_seguintes_numa_ocorrencia_ja_paga_e_recusado()
    {
        $this->pagar(3);
        $mensagem = 'Esta Ocorrência já está paga. Remova o pagamento antes, ou use "Reajustar" ou "Encerrar" na Despesa Fixa.';

        $this->alterarOcorrencia(3, 'seguintes', ['valor_total' => '1.700,00'])
            ->assertJsonFragment(['error' => true, 'msg' => $mensagem]);
        $this->excluirOcorrencia(3, 'seguintes')
            ->assertJsonFragment(['error' => true, 'msg' => $mensagem]);

        $this->despesasDoMes(4)->assertSee('1.500,00');
        $this->listaDeFixas()->assertSee('sem fim');
    }

    /** @test */
    public function encerrar_depois_do_fim_atual_e_recusado()
    {
        $this->actingAs($this->user)
            ->putJson(route('despesasFixas.encerrar', $this->fixa->id), ['mes_fim' => '2026-04'])
            ->assertJsonFragment(['error' => false]);

        $this->actingAs($this->user)
            ->putJson(route('despesasFixas.encerrar', $this->fixa->id), ['mes_fim' => '2026-08'])
            ->assertJsonFragment(['error' => true, 'msg' => 'A Vigência já termina em 04/2026. Para estender, cadastre uma nova Despesa Fixa.']);

        $this->assertSame(0, $this->ocorrenciasNoMes(5));
        $this->listaDeFixas()->assertSeeInOrder(['01/2026', '04/2026']);
    }

    /** @test */
    public function encerrar_desde_o_inicio_com_uma_ocorrencia_paga_depois_mantem_o_fim_nela()
    {
        $this->pagar(3);

        $this->excluirOcorrencia(1, 'seguintes')->assertJsonFragment(['error' => false]);

        $this->assertSame(0, $this->ocorrenciasNoMes(1));
        $this->assertSame(0, $this->ocorrenciasNoMes(2));
        $this->despesasDoMes(3)->assertSee('Remover Pagamento');
        $this->assertSame(0, $this->ocorrenciasNoMes(4));
        $this->listaDeFixas()->assertSeeInOrder(['01/2026', '03/2026']);
    }

    /** @test */
    public function renomear_a_fixa_nao_muda_o_nome_da_ocorrencia_paga()
    {
        $this->pagar(1);

        $this->actingAs($this->user)
            ->putJson(route('despesasFixas.update', $this->fixa->id), ['nome_despesa' => 'Aluguel apto'])
            ->assertJsonFragment(['error' => false]);

        $this->despesasDoMes(1)->assertDontSee('Aluguel apto');
        $this->despesasDoMes(2)->assertSee('Aluguel apto');
    }
}

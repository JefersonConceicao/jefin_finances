<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Carbon\Carbon;
use App\Models\User;
use App\Models\Proventos;

//"REPETIR PROVENTOS DO MÊS ANTERIOR": POST /proventos/copyProventos COM O mes E O ano DO FILTRO DA TELA
class RepetirProventosTest extends TestCase
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

    private function provento($user, $descricao, $valor, $data)
    {
        return factory(Proventos::class)->create([
            'user_id' => $user->id,
            'descricao_provento' => $descricao,
            'valor_provento' => $valor,
            'data_provento' => $data,
        ]);
    }

    private function repetir($mes, $ano)
    {
        return $this->actingAs($this->user)
            ->postJson(route('proventos.copy'), ['mes' => $mes, 'ano' => $ano]);
    }

    private function doMes($user, $mes, $ano)
    {
        return Proventos::where('user_id', $user->id)
            ->whereMonth('data_provento', $mes)
            ->whereYear('data_provento', $ano)
            ->orderBy('descricao_provento')
            ->get();
    }

    /** @test */
    public function copia_so_os_proventos_do_usuario_logado()
    {
        $outro = factory(User::class)->create();
        $this->provento($this->user, 'Bônus', 300.00, '2026-10-15');
        $this->provento($outro, 'Freela', 800.00, '2026-10-20');

        $this->repetir(11, 2026)->assertOk()->assertJsonFragment(['error' => false]);

        $meus = $this->doMes($this->user, 11, 2026);
        $this->assertCount(1, $meus);
        $this->assertEquals('Bônus', $meus[0]->descricao_provento);
        $this->assertEquals(300.00, $meus[0]->valor_provento);

        $this->assertCount(0, $this->doMes($outro, 11, 2026));
    }

    /** @test */
    public function nao_copia_as_ocorrencias_de_provento_fixo()
    {
        //SALÁRIO FIXO: JÁ TEM OCORRÊNCIA EM NOV E EM DEZ/2026
        $this->actingAs($this->user)->postJson(route('proventosFixos.store'), [
            'descricao' => 'Salário',
            'valor' => '5.000,00',
            'mes_inicio' => '2026-11',
        ])->assertJsonFragment(['error' => false]);
        $this->provento($this->user, 'Bônus', 300.00, '2026-11-05');

        $this->repetir(12, 2026)->assertJsonFragment(['error' => false]);

        $dezembro = $this->doMes($this->user, 12, 2026);
        $this->assertEquals(['Bônus', 'Salário'], $dezembro->pluck('descricao_provento')->all());
        $this->assertNull($dezembro[0]->provento_fixo_id);
        $this->assertNotNull($dezembro[1]->provento_fixo_id);
    }

    /** @test */
    public function em_janeiro_copia_os_proventos_de_dezembro_do_ano_anterior()
    {
        $this->provento($this->user, 'Décimo terceiro', 2500.00, '2026-12-20');

        $this->repetir(1, 2027)->assertJsonFragment(['error' => false]);

        $janeiro = $this->doMes($this->user, 1, 2027);
        $this->assertCount(1, $janeiro);
        $this->assertEquals('Décimo terceiro', $janeiro[0]->descricao_provento);
        $this->assertEquals('2027-01-20', Carbon::parse($janeiro[0]->data_provento)->format('Y-m-d'));
    }

    /** @test */
    public function o_dia_que_nao_existe_no_mes_vira_o_ultimo_dia_dele()
    {
        $this->provento($this->user, 'Aluguel recebido', 1200.00, '2027-01-31');

        $this->repetir(2, 2027)->assertJsonFragment(['error' => false]);

        $fevereiro = $this->doMes($this->user, 2, 2027);
        $this->assertCount(1, $fevereiro);
        $this->assertEquals('2027-02-28', Carbon::parse($fevereiro[0]->data_provento)->format('Y-m-d'));
    }

    /** @test */
    public function sem_mes_e_ano_no_filtro_nao_copia_nada()
    {
        $this->provento($this->user, 'Bônus', 300.00, '2026-10-15');

        $this->repetir('', 2026)
            ->assertOk()
            ->assertJsonFragment(['error' => true, 'msg' => 'Escolha o mês e o ano para onde copiar']);

        $this->assertEquals(1, Proventos::count());
    }
}

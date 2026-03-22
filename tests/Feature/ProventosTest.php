<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\User;
use App\Models\Proventos;

class ProventosTest extends TestCase
{
    use RefreshDatabase; // Garante que o banco em memória seja limpo e as migrações rodadas

    protected $user;

    protected function setUp(): void
    {
        parent::setUp();

        // Desabilita CSRF para os testes
        $this->withoutMiddleware(\App\Http\Middleware\VerifyCsrfToken::class);

        // Cria um usuário para ser usado nos testes
        $this->user = factory(User::class)->create();
    }

    /** @test */
    public function deve_somar_valor_ao_provento_existente_no_mesmo_mes_e_ano()
    {
        $data1 = [
            'descricao_provento' => 'Salário',
            'valor_provento' => '1.500,00',
            'data_provento' => '10/03/2026'
        ];

        $data2 = [
            'descricao_provento' => 'Salário',
            'valor_provento' => '500,00',
            'data_provento' => '15/03/2026' // Dia diferente, mas mesmo mês/ano
        ];

        // Primeiro cadastro
        $response1 = $this->actingAs($this->user)->postJson(route('proventos.store'), $data1);
        $response1->assertStatus(200);

        // Segundo cadastro (deve somar)
        $response2 = $this->actingAs($this->user)->postJson(route('proventos.store'), $data2);
        $response2->assertStatus(200);
        $response2->assertJsonFragment(['msg' => 'Valor somado ao provento existente!']);

        // Verifica no banco se o valor total é 2000.00
        $this->assertDatabaseHas('proventos', [
            'user_id' => $this->user->id,
            'descricao_provento' => 'Salário',
            'valor_provento' => 2000.00
        ]);

        // Verifica se só existe UM registro de 'Salário' no banco
        $this->assertEquals(1, Proventos::where('descricao_provento', 'Salário')->count());
    }

    /** @test */
    public function deve_alterar_provento_e_registros_subsequentes()
    {
        // Cria 3 registros: Março, Abril, Maio
        $p1 = factory(Proventos::class)->create([
            'user_id' => $this->user->id,
            'descricao_provento' => 'Salário',
            'valor_provento' => 1000.00,
            'data_provento' => '2026-03-01'
        ]);
        $p2 = factory(Proventos::class)->create([
            'user_id' => $this->user->id,
            'descricao_provento' => 'Salário',
            'valor_provento' => 1000.00,
            'data_provento' => '2026-04-01'
        ]);
        $p3 = factory(Proventos::class)->create([
            'user_id' => $this->user->id,
            'descricao_provento' => 'Salário',
            'valor_provento' => 1000.00,
            'data_provento' => '2026-05-01'
        ]);

        // Atualiza Março pedindo para atualizar subsequentes
        $updateData = [
            'descricao_provento' => 'Salário Reajustado',
            'valor_provento' => '1.200,00',
            'data_provento' => '01/03/2026',
            'update_subsequent' => 'true'
        ];

        $response = $this->actingAs($this->user)->putJson(route('proventos.update', $p1->id), $updateData);
        $response->assertStatus(200);

        // Verifica se todos os 3 registros foram alterados
        $this->assertEquals(3, Proventos::where('descricao_provento', 'Salário Reajustado')->count());
        $this->assertEquals(0, Proventos::where('descricao_provento', 'Salário')->count());
        $this->assertEquals(3, Proventos::where('valor_provento', 1200.00)->count());
    }

    /** @test */
    public function deve_excluir_provento_e_registros_subsequentes()
    {
        // Cria 3 registros: Março, Abril, Maio
        $p1 = factory(Proventos::class)->create([
            'user_id' => $this->user->id,
            'descricao_provento' => 'Salário',
            'data_provento' => '2026-03-01'
        ]);
        factory(Proventos::class)->create([
            'user_id' => $this->user->id,
            'descricao_provento' => 'Salário',
            'data_provento' => '2026-04-01'
        ]);
        factory(Proventos::class)->create([
            'user_id' => $this->user->id,
            'descricao_provento' => 'Outro Provento', // Não deve ser excluído
            'data_provento' => '2026-04-15'
        ]);

        // Deleta Março com subsequentes
        $response = $this->actingAs($this->user)->deleteJson(route('proventos.delete', $p1->id), [
            'delete_subsequent' => 'true'
        ]);

        $response->assertStatus(200);

        // Deve sobrar apenas o 'Outro Provento'
        $this->assertEquals(1, Proventos::count());
        $this->assertDatabaseHas('proventos', ['descricao_provento' => 'Outro Provento']);
        $this->assertDatabaseMissing('proventos', ['descricao_provento' => 'Salário']);
    }
}

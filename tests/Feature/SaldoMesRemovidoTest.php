<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\User;

class SaldoMesRemovidoTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function o_endpoint_saldo_mes_nao_existe_mais()
    {
        $user = factory(User::class)->create();

        $this->actingAs($user)
            ->getJson('/api/auth/proventos/saldo-mes?mes=11&ano=2026')
            ->assertNotFound();
    }
}

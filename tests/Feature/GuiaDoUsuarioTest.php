<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\User;

class GuiaDoUsuarioTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function a_tela_de_login_tem_o_link_para_o_guia_do_usuario()
    {
        $this->get(route('auth.renderViewLogin'))
            ->assertOk()
            ->assertSee('href="' . route('guia.index') . '"', false)
            ->assertSee('Guia do usuário');
    }

    /** @test */
    public function o_guia_abre_sem_login()
    {
        $this->get(route('guia.index'))
            ->assertOk()
            ->assertSee('Como o Jefin Finances organiza o seu dinheiro')
            ->assertSee('Primeiros passos')
            ->assertSee('Esta e as seguintes')
            ->assertSee('Saldo Acumulado');
    }

    /** @test */
    public function o_guia_tambem_abre_para_quem_esta_logado()
    {
        $this->actingAs(factory(User::class)->create())
            ->get(route('guia.index'))
            ->assertOk()
            ->assertSee('Como o Jefin Finances organiza o seu dinheiro');
    }
}

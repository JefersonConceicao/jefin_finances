<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\User;

class MenuTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function home_leva_para_a_projecao()
    {
        $this->actingAs(factory(User::class)->create())
            ->get('/home')
            ->assertRedirect('/projecao');
    }

    /** @test */
    public function quem_ja_esta_logado_e_abre_o_login_vai_para_a_projecao()
    {
        $this->actingAs(factory(User::class)->create())
            ->get(route('auth.renderViewLogin'))
            ->assertRedirect('/projecao');
    }

    /** @test */
    public function o_menu_tem_projecao_e_os_submenus_de_proventos_e_despesas()
    {
        $this->actingAs(factory(User::class)->create())
            ->get(route('projecao.index'))
            ->assertOk()
            ->assertSeeInOrder(['Projeção', 'Proventos', 'Proventos Fixos', 'Despesas', 'Despesas Fixas'])
            ->assertDontSee('Calculadora de Proventos')
            ->assertDontSee('Despesas Tipos')
            ->assertDontSee('Dívidas');
    }

    /** @test */
    public function o_menu_tem_o_link_do_guia_do_usuario()
    {
        $this->actingAs(factory(User::class)->create())
            ->get(route('projecao.index'))
            ->assertOk()
            ->assertSee('href="/guia"', false)
            ->assertSee('Guia do Usuário');
    }

    /** @test */
    public function dividas_nao_existe_mais()
    {
        $this->actingAs(factory(User::class)->create())
            ->get('/dividas')
            ->assertNotFound();
    }

    /** @test */
    public function a_tela_de_proventos_se_chama_proventos()
    {
        $this->actingAs(factory(User::class)->create())
            ->get(route('proventos.index'))
            ->assertOk()
            ->assertSee('<h3> Proventos </h3>', false)
            ->assertDontSee('Receitas');
    }
}

<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\User;

class TemaEscuroTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function o_tema_e_aplicado_no_head_antes_das_folhas_de_estilo()
    {
        $this->actingAs(factory(User::class)->create())
            ->get(route('projecao.index'))
            ->assertOk()
            ->assertSeeInOrder([
                "localStorage.getItem('jefin-tema')",
                "prefers-color-scheme: dark",
                "setAttribute('data-theme', tema)",
                'css/tema-escuro.css',
                '</head>',
            ], false);
    }

    /** @test */
    public function a_barra_superior_tem_o_botao_de_alternar_o_tema()
    {
        $this->actingAs(factory(User::class)->create())
            ->get(route('projecao.index'))
            ->assertSee('id="btnAlternarTema"', false);
    }

    /** @test */
    public function a_tela_de_login_tambem_segue_o_tema()
    {
        $this->get(route('auth.renderViewLogin'))
            ->assertOk()
            ->assertSee('css/tema-escuro.css', false);
    }
}

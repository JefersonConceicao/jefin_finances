<?php

namespace Tests\Unit;

use Tests\TestCase;
use Tests\TravaBancoDeTeste;

class TravaBancoDeTesteTest extends TestCase
{
    /** @test */
    public function aborta_quando_a_conexao_padrao_e_mysql()
    {
        config([
            'database.default' => 'mysql',
            'database.connections.mysql' => ['driver' => 'mysql', 'host' => 'db', 'database' => 'jefin_finances'],
        ]);

        $this->expectException(\RuntimeException::class);

        TravaBancoDeTeste::garantir($this->app);
    }

    /** @test */
    public function aborta_quando_o_sqlite_aponta_para_um_arquivo()
    {
        config([
            'database.default' => 'sqlite',
            'database.connections.sqlite.database' => '/var/www/database/database.sqlite',
        ]);

        $this->expectException(\RuntimeException::class);

        TravaBancoDeTeste::garantir($this->app);
    }

    /** @test */
    public function aborta_quando_a_url_da_conexao_aponta_para_mysql()
    {
        config([
            'database.default' => 'sqlite',
            'database.connections.sqlite.database' => ':memory:',
            'database.connections.sqlite.url' => 'mysql://root:segredo@db:3306/jefin_finances',
        ]);

        $this->expectException(\RuntimeException::class);

        TravaBancoDeTeste::garantir($this->app);
    }

    /** @test */
    public function outras_conexoes_nao_ficam_disponiveis_nos_testes()
    {
        $this->expectException(\InvalidArgumentException::class);

        \DB::connection('mysql');
    }

    /** @test */
    public function permite_sqlite_em_memoria()
    {
        config([
            'database.default' => 'sqlite',
            'database.connections.sqlite.database' => ':memory:',
        ]);

        TravaBancoDeTeste::garantir($this->app);

        $this->assertSame('sqlite', config('database.default'));
    }
}

<?php

namespace Tests\Unit;

use Tests\TestCase;
use Tests\TravaBancoDeTeste;

class TravaBancoDeTesteTest extends TestCase
{
    /** @test */
    public function aborta_quando_a_conexao_padrao_e_mysql()
    {
        config(['database.default' => 'mysql']);

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

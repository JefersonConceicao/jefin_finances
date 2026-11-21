<?php

namespace Tests;

use Illuminate\Contracts\Foundation\Application;

/**
 * Impede que os testes rodem contra um banco real.
 * O RefreshDatabase apaga TODAS as tabelas da conexão; fora do SQLite em memória
 * isso destrói o banco de desenvolvimento. Lê a config já resolvida, então
 * também cobre o caso de config em cache (config:cache), que ignora o phpunit.xml.
 */
class TravaBancoDeTeste
{
    public static function garantir(Application $app)
    {
        $config = $app['config'];
        $conexao = $config->get('database.default');
        $driver = $config->get("database.connections.{$conexao}.driver");
        $banco = $config->get("database.connections.{$conexao}.database");

        if ($driver !== 'sqlite' || $banco !== ':memory:') {
            throw new \RuntimeException(sprintf(
                'Testes abortados: a conexão "%s" (driver "%s", banco "%s") não é SQLite em memória. '
                . 'Rodar os testes nela apagaria o banco. Remova o cache de config (php artisan config:clear) '
                . 'e confira o phpunit.xml.',
                $conexao,
                $driver,
                $banco
            ));
        }
    }
}

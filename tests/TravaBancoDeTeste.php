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

        //CONFERE A CONEXÃO COMO O LARAVEL A MONTA (A url SOBRESCREVE driver/database). NÃO ABRE O PDO.
        $app['db']->purge($conexao);
        $resolvida = $app['db']->connection($conexao);
        $driver = $resolvida->getDriverName();
        $banco = $resolvida->getDatabaseName();

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

        //REMOVE AS DEMAIS CONEXÕES (EX.: mysql COM AS CREDENCIAIS DO .env) PARA NENHUM TESTE ALCANÇÁ-LAS
        foreach (array_keys($config->get('database.connections')) as $nome) {
            if ($nome !== $conexao) {
                $app['db']->purge($nome);
            }
        }
        $config->set('database.connections', [$conexao => $config->get("database.connections.{$conexao}")]);
    }
}

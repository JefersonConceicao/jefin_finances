<?php

namespace Tests;

use Illuminate\Contracts\Console\Kernel;

trait CreatesApplication
{
    /**
     * Creates the application.
     *
     * @return \Illuminate\Foundation\Application
     */
    public function createApplication()
    {
        $app = require __DIR__.'/../bootstrap/app.php';

        $app->make(Kernel::class)->bootstrap();

        TravaBancoDeTeste::garantir($app);

        //SCHEMA DE TESTE FICA FORA DE database/migrations PARA NUNCA RODAR EM PRODUÇÃO
        $app['migrator']->path(__DIR__.'/migrations');

        return $app;
    }
}

<?php

return [

    /*
     * Proxies confiáveis, lidos pelo middleware TrustProxies.
     *
     * Em produção o Caddy fica na frente do nginx e termina o HTTPS, então o Laravel
     * só sabe que a requisição veio por https pelo cabeçalho X-Forwarded-Proto.
     * Lá TRUSTED_PROXIES=* é seguro porque o nginx não publica porta: só o Caddy,
     * pela rede interna do Docker, chega nele. Em dev fica vazio.
     */
    'proxies' => env('TRUSTED_PROXIES'),

];

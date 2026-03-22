<?php

/** @var \Illuminate\Database\Eloquent\Factory $factory */

use App\Models\Proventos;
use Faker\Generator as Faker;

$factory->define(Proventos::class, function (Faker $faker) {
    return [
        'user_id' => function () {
            // Em vez de factory(User::class)->create()->id, 
            // usamos apenas factory(User::class) para o Laravel injetar o ID automaticamente ao criar
            return factory(App\Models\User::class);
        },
        'descricao_provento' => $faker->word,
        'valor_provento' => $faker->randomFloat(2, 100, 5000),
        'data_provento' => $faker->date('Y-m-d'),
    ];
});

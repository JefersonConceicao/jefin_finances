<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateLancamentosTable extends Migration
{
    public function up()
    {
        Schema::create('lancamentos', function (Blueprint $table) {
            $table->increments('id');
            $table->datetime('data_lancamento');
            $table->unsignedInteger('despesa_id')->nullable();
            $table->decimal('valor', 6, 2)->nullable();
            $table->string('descricao', 125)->nullable();
            $table->unsignedInteger('user_id')->nullable();

            $table->foreign('despesa_id')->references('id')->on('despesas');
            $table->foreign('user_id')->references('id')->on('users');
        });
    }

    public function down()
    {
        Schema::dropIfExists('lancamentos');
    }
}

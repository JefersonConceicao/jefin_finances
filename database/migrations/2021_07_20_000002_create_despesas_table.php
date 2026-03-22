<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateDespesasTable extends Migration
{
    public function up()
    {
        Schema::create('despesas', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('user_id');
            $table->string('nome_despesa', 100);
            $table->unsignedInteger('despesa_tipo_id');
            $table->decimal('valor_total', 6, 2);
            $table->tinyInteger('ativo')->unsigned()->default(1);
            $table->datetime('created_at')->nullable();
            $table->datetime('updated_at')->nullable();
            $table->tinyInteger('pago')->default(0);

            $table->foreign('user_id')->references('id')->on('users');
            $table->foreign('despesa_tipo_id')->references('id')->on('despesas_tipos');
        });
    }

    public function down()
    {
        Schema::dropIfExists('despesas');
    }
}

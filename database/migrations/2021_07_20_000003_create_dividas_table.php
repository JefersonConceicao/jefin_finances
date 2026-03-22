<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateDividasTable extends Migration
{
    public function up()
    {
        Schema::create('dividas', function (Blueprint $table) {
            $table->increments('id');
            $table->string('descricao_divida', 255)->default('0');
            $table->integer('qtd_parcela_total')->default(0);
            $table->integer('qtd_parcela_parcial')->default(0);
            $table->float('valor_total', 11, 2)->default(0.00);
            $table->float('valor_parcial', 6, 2)->nullable()->default(0.00);
            $table->float('valor_parcela', 6, 2)->default(0.00);
            $table->datetime('created_at')->nullable();
            $table->datetime('updated_at')->nullable();
            $table->date('data_fim_divida')->nullable();
            $table->date('data_inicial_divida')->nullable();
            $table->integer('pago')->default(0);
            $table->unsignedInteger('user_id')->nullable();

            $table->foreign('user_id')->references('id')->on('users')->onDelete('set null')->onUpdate('set null');
        });
    }

    public function down()
    {
        Schema::dropIfExists('dividas');
    }
}

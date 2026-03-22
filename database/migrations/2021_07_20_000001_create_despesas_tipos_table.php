<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateDespesasTiposTable extends Migration
{
    public function up()
    {
        Schema::create('despesas_tipos', function (Blueprint $table) {
            $table->increments('id');
            $table->string('nome', 120)->default('0');
            $table->integer('ativo')->default(1);
            $table->unsignedInteger('user_id')->nullable();
            
            // Chave estrangeira para users (usando unsignedInteger conforme o dump)
            $table->foreign('user_id')->references('id')->on('users')->onDelete('no action')->onUpdate('no action');
        });
    }

    public function down()
    {
        Schema::dropIfExists('despesas_tipos');
    }
}

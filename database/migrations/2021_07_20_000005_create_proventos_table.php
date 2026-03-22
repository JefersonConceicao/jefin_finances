<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateProventosTable extends Migration
{
    public function up()
    {
        Schema::create('proventos', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('user_id');
            $table->decimal('valor_provento', 6, 2)->nullable();
            $table->date('data_provento');
            $table->datetime('created_at')->nullable();
            $table->datetime('updated_at')->nullable();
            $table->string('descricao_provento', 255);

            $table->foreign('user_id')->references('id')->on('users');
        });
    }

    public function down()
    {
        Schema::dropIfExists('proventos');
    }
}

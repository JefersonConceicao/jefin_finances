<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Schema usado SOMENTE nos testes (SQLite em memória).
 * A fonte do schema de produção são os DDLs em database/ddl e o banco MySQL existente.
 * Toda mudança de schema deve atualizar os dois.
 */
class CriarSchemaDeTeste extends Migration
{
    public function up()
    {
        Schema::create('users', function (Blueprint $table) {
            $table->increments('id');
            $table->string('email', 50)->default('0');
            $table->char('name', 50)->default('0');
            $table->char('last_name', 50)->default('0');
            $table->string('mail_token_confirm')->nullable();
            $table->string('password_token_reset')->nullable();
            $table->string('password')->default('0');
            $table->dateTime('created_at');
            $table->dateTime('updated_at');
            $table->integer('ativo')->default(1);
            $table->integer('temp_user')->nullable()->default(0);
            $table->dateTime('last_login')->nullable();
        });

        Schema::create('despesas_tipos', function (Blueprint $table) {
            $table->increments('id');
            $table->string('nome', 120)->default('0');
            $table->integer('ativo')->default(1);
            $table->unsignedInteger('user_id')->nullable();
            $table->foreign('user_id')->references('id')->on('users');
        });

        Schema::create('despesas_fixas', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('user_id');
            $table->string('nome_despesa', 100);
            $table->unsignedInteger('despesa_tipo_id');
            $table->decimal('valor', 6, 2);
            $table->unsignedTinyInteger('ativo')->default(1);
            $table->dateTime('created_at')->nullable();
            $table->dateTime('updated_at')->nullable();
            $table->foreign('user_id')->references('id')->on('users');
            $table->foreign('despesa_tipo_id')->references('id')->on('despesas_tipos');
        });

        Schema::create('despesas', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('user_id');
            $table->string('nome_despesa', 100);
            $table->unsignedInteger('despesa_tipo_id');
            $table->unsignedInteger('despesa_fixa_id')->nullable();
            $table->decimal('valor_total', 6, 2);
            $table->unsignedTinyInteger('ativo')->default(1);
            $table->dateTime('created_at')->nullable();
            $table->dateTime('updated_at')->nullable();
            $table->tinyInteger('pago')->default(0);
            $table->foreign('user_id')->references('id')->on('users');
            $table->foreign('despesa_tipo_id')->references('id')->on('despesas_tipos');
            $table->foreign('despesa_fixa_id')->references('id')->on('despesas_fixas')->onDelete('set null');
        });

        Schema::create('proventos', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('user_id');
            $table->decimal('valor_provento', 6, 2)->nullable();
            $table->date('data_provento');
            $table->dateTime('created_at')->nullable();
            $table->dateTime('updated_at')->nullable();
            $table->string('descricao_provento');
            $table->foreign('user_id')->references('id')->on('users');
        });

        Schema::create('lancamentos', function (Blueprint $table) {
            $table->increments('id');
            $table->dateTime('data_lancamento');
            $table->unsignedInteger('despesa_id')->nullable();
            $table->decimal('valor', 6, 2)->nullable();
            $table->string('descricao', 125)->nullable();
            $table->unsignedInteger('user_id')->nullable();
            $table->foreign('despesa_id')->references('id')->on('despesas');
            $table->foreign('user_id')->references('id')->on('users');
        });

        Schema::create('dividas', function (Blueprint $table) {
            $table->increments('id');
            $table->string('descricao_divida')->default('0');
            $table->integer('qtd_parcela_total')->default(0);
            $table->integer('qtd_parcela_parcial')->default(0);
            $table->float('valor_total', 11, 2)->default(0);
            $table->float('valor_parcial', 6, 2)->nullable()->default(0);
            $table->float('valor_parcela', 6, 2)->default(0);
            $table->dateTime('created_at')->nullable();
            $table->dateTime('updated_at')->nullable();
            $table->date('data_fim_divida')->nullable();
            $table->date('data_inicial_divida')->nullable();
            $table->integer('pago')->default(0);
            $table->unsignedInteger('user_id')->nullable();
            $table->foreign('user_id')->references('id')->on('users')->onDelete('set null');
        });
    }

    public function down()
    {
        foreach (['dividas', 'lancamentos', 'proventos', 'despesas', 'despesas_fixas', 'despesas_tipos', 'users'] as $tabela) {
            Schema::dropIfExists($tabela);
        }
    }
}

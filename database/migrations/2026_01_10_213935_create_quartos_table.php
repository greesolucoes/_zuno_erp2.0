<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateQuartosTable extends Migration
{
    public function up()
    {
        Schema::create('quartos', function (Blueprint $table) {
            $table->increments('id');

            $table->unsignedInteger('empresa_id');
            $table->foreign('empresa_id')->references('id')->on('empresas')->onDelete('cascade');

            $table->unsignedInteger('colaborador_id')->nullable();
            $table->foreign('colaborador_id')->references('id')->on('funcionarios')->nullOnDelete();

            $table->string('nome', 120);
            $table->string('descricao', 255)->nullable();
            $table->string('tipo', 60)->nullable();
            $table->unsignedInteger('capacidade')->default(0);
            $table->string('status', 30)->default('disponivel');

            $table->timestamps();

            $table->index(['empresa_id']);
            $table->index(['colaborador_id']);
            $table->index(['status']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('quartos');
    }
}


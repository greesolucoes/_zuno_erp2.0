<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateQuartoEventosTable extends Migration
{
    public function up()
    {
        Schema::create('quarto_eventos', function (Blueprint $table) {
            $table->increments('id');

            $table->unsignedInteger('quarto_id');
            $table->foreign('quarto_id')->references('id')->on('quartos')->onDelete('cascade');

            $table->unsignedInteger('servico_id')->nullable();
            $table->foreign('servico_id')->references('id')->on('servicos')->nullOnDelete();

            $table->unsignedInteger('prestador_id')->nullable();
            $table->foreign('prestador_id')->references('id')->on('funcionarios')->nullOnDelete();

            $table->unsignedInteger('fornecedor_id')->nullable();
            $table->foreign('fornecedor_id')->references('id')->on('fornecedors')->nullOnDelete();

            $table->dateTime('inicio');
            $table->dateTime('fim');
            $table->string('descricao', 255)->nullable();

            $table->timestamps();

            $table->index(['quarto_id']);
            $table->index(['servico_id']);
            $table->index(['prestador_id']);
            $table->index(['fornecedor_id']);
            $table->index(['inicio']);
            $table->index(['fim']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('quarto_eventos');
    }
}

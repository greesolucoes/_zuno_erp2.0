<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('petshop_hoteis', function (Blueprint $table) {
            $table->increments('id');

            $table->unsignedInteger('empresa_id');
            $table->foreign('empresa_id')->references('id')->on('empresas')->onDelete('cascade');

            $table->unsignedInteger('animal_id')->nullable();

            $table->unsignedInteger('cliente_id')->nullable();
            $table->foreign('cliente_id')->references('id')->on('clientes')->nullOnDelete();

            $table->unsignedInteger('quarto_id')->nullable();
            $table->foreign('quarto_id')->references('id')->on('quartos')->nullOnDelete();

            $table->unsignedInteger('servico_id')->nullable();
            $table->foreign('servico_id')->references('id')->on('servicos')->nullOnDelete();

            $table->unsignedInteger('colaborador_id')->nullable();
            $table->foreign('colaborador_id')->references('id')->on('funcionarios')->nullOnDelete();

            $table->unsignedInteger('plano_id')->nullable();

            $table->unsignedInteger('ordem_servico_id')->nullable();
            $table->foreign('ordem_servico_id')->references('id')->on('ordem_servicos')->nullOnDelete();

            $table->unsignedInteger('diarias')->default(1);
            $table->string('descricao', 255)->nullable();
            $table->dateTime('checkin')->nullable();
            $table->dateTime('checkout')->nullable();
            $table->decimal('valor', 10, 2)->default(0);
            $table->string('estado', 64)->nullable();
            $table->boolean('situacao_checklist')->default(false);

            $table->timestamps();

            $table->index(['empresa_id']);
            $table->index(['animal_id']);
            $table->index(['cliente_id']);
            $table->index(['quarto_id']);
            $table->index(['servico_id']);
            $table->index(['colaborador_id']);
            $table->index(['plano_id']);
            $table->index(['ordem_servico_id']);
            $table->index(['estado']);
            $table->index(['checkin']);
            $table->index(['checkout']);
        });

        if (Schema::hasTable('petshop_animais')) {
            Schema::table('petshop_hoteis', function (Blueprint $table) {
                $table->foreign('animal_id')->references('id')->on('petshop_animais')->nullOnDelete();
            });
        }

        if (Schema::hasTable('petshop_planos')) {
            Schema::table('petshop_hoteis', function (Blueprint $table) {
                $table->foreign('plano_id')->references('id')->on('petshop_planos')->nullOnDelete();
            });
        }

        Schema::create('petshop_hotel_checklists', function (Blueprint $table) {
            $table->increments('id');

            $table->unsignedInteger('hotel_id');
            $table->foreign('hotel_id')->references('id')->on('petshop_hoteis')->onDelete('cascade');

            $table->unsignedInteger('empresa_id')->nullable();
            $table->foreign('empresa_id')->references('id')->on('empresas')->nullOnDelete();

            $table->string('tipo', 191)->default('entrada');
            $table->json('checklist')->nullable();

            $table->timestamps();

            $table->index(['hotel_id']);
            $table->index(['empresa_id']);
            $table->index(['tipo']);
        });

        Schema::create('petshop_hoteis_clientes_enderecos', function (Blueprint $table) {
            $table->increments('id');

            $table->unsignedInteger('hotel_id');
            $table->foreign('hotel_id')->references('id')->on('petshop_hoteis')->onDelete('cascade');

            $table->unsignedInteger('cliente_id');
            $table->foreign('cliente_id')->references('id')->on('clientes')->onDelete('cascade');

            $table->unsignedInteger('cidade_id');
            $table->foreign('cidade_id')->references('id')->on('cidades')->onDelete('cascade');

            $table->string('cep', 9)->nullable();
            $table->string('rua', 255);
            $table->string('bairro', 255);
            $table->string('numero', 20);
            $table->text('complemento')->nullable();

            $table->timestamps();

            $table->index(['hotel_id']);
            $table->index(['cliente_id']);
            $table->index(['cidade_id']);
        });

        Schema::create('petshop_hotel_servico', function (Blueprint $table) {
            $table->increments('id');

            $table->unsignedInteger('hotel_id');
            $table->foreign('hotel_id')->references('id')->on('petshop_hoteis')->onDelete('cascade');

            $table->unsignedInteger('servico_id');
            $table->foreign('servico_id')->references('id')->on('servicos')->onDelete('cascade');

            $table->date('data_servico')->nullable();
            $table->time('hora_servico')->nullable();
            $table->decimal('valor_servico', 10, 2)->default(0);

            $table->index(['hotel_id']);
            $table->index(['servico_id']);
            $table->index(['data_servico']);
        });

        Schema::create('petshop_hotel_produto', function (Blueprint $table) {
            $table->unsignedInteger('hotel_id');
            $table->foreign('hotel_id')->references('id')->on('petshop_hoteis')->onDelete('cascade');

            $table->unsignedInteger('produto_id');
            $table->foreign('produto_id')->references('id')->on('produtos')->onDelete('cascade');

            $table->decimal('quantidade', 10, 2)->default(1);

            $table->primary(['hotel_id', 'produto_id']);
            $table->index(['hotel_id']);
            $table->index(['produto_id']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('petshop_hotel_produto');
        Schema::dropIfExists('petshop_hotel_servico');
        Schema::dropIfExists('petshop_hoteis_clientes_enderecos');
        Schema::dropIfExists('petshop_hotel_checklists');
        Schema::dropIfExists('petshop_hoteis');
    }
};

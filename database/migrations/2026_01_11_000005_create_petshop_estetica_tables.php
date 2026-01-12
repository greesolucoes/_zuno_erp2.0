<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('petshop_esteticas', function (Blueprint $table) {
            $table->increments('id');

            $table->unsignedInteger('empresa_id');
            $table->foreign('empresa_id')->references('id')->on('empresas')->onDelete('cascade');

            $table->unsignedInteger('animal_id')->nullable();

            $table->unsignedInteger('cliente_id')->nullable();
            $table->foreign('cliente_id')->references('id')->on('clientes')->nullOnDelete();

            $table->unsignedInteger('colaborador_id')->nullable();
            $table->foreign('colaborador_id')->references('id')->on('funcionarios')->nullOnDelete();

            $table->unsignedInteger('servico_id')->nullable();
            $table->foreign('servico_id')->references('id')->on('servicos')->nullOnDelete();

            $table->unsignedInteger('plano_id')->nullable();

            $table->unsignedInteger('ordem_servico_id')->nullable();
            $table->foreign('ordem_servico_id')->references('id')->on('ordem_servicos')->nullOnDelete();

            $table->text('descricao')->nullable();
            $table->decimal('valor', 10, 2)->nullable();
            $table->string('estado', 191)->default('agendado');

            $table->date('data_agendamento')->nullable();
            $table->time('horario_agendamento')->nullable();
            $table->time('horario_saida')->nullable();

            $table->timestamps();

            $table->index(['empresa_id']);
            $table->index(['animal_id']);
            $table->index(['cliente_id']);
            $table->index(['colaborador_id']);
            $table->index(['servico_id']);
            $table->index(['plano_id']);
            $table->index(['ordem_servico_id']);
            $table->index(['estado']);
            $table->index(['data_agendamento']);
        });

        if (Schema::hasTable('petshop_animais')) {
            Schema::table('petshop_esteticas', function (Blueprint $table) {
                $table->foreign('animal_id')->references('id')->on('petshop_animais')->nullOnDelete();
            });
        }

        if (Schema::hasTable('petshop_planos')) {
            Schema::table('petshop_esteticas', function (Blueprint $table) {
                $table->foreign('plano_id')->references('id')->on('petshop_planos')->nullOnDelete();
            });
        }

        Schema::create('petshop_esteticas_clientes_enderecos', function (Blueprint $table) {
            $table->increments('id');

            $table->unsignedInteger('estetica_id');
            $table->foreign('estetica_id')->references('id')->on('petshop_esteticas')->onDelete('cascade');

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

            $table->index(['estetica_id']);
            $table->index(['cliente_id']);
            $table->index(['cidade_id']);
        });

        Schema::create('petshop_estetica_produtos', function (Blueprint $table) {
            $table->increments('id');

            $table->unsignedInteger('estetica_id');
            $table->foreign('estetica_id')->references('id')->on('petshop_esteticas')->onDelete('cascade');

            $table->unsignedInteger('produto_id');
            $table->foreign('produto_id')->references('id')->on('produtos')->onDelete('cascade');

            $table->unsignedInteger('quantidade')->default(1);
            $table->decimal('valor', 10, 2)->default(0);
            $table->decimal('subtotal', 10, 2)->default(0);

            $table->timestamps();

            $table->index(['estetica_id']);
            $table->index(['produto_id']);
        });

        Schema::create('petshop_estetica_servicos', function (Blueprint $table) {
            $table->increments('id');

            $table->unsignedInteger('estetica_id');
            $table->foreign('estetica_id')->references('id')->on('petshop_esteticas')->onDelete('cascade');

            $table->unsignedInteger('servico_id');
            $table->foreign('servico_id')->references('id')->on('servicos')->onDelete('cascade');

            $table->decimal('subtotal', 10, 2)->default(0);

            $table->timestamps();

            $table->index(['estetica_id']);
            $table->index(['servico_id']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('petshop_estetica_servicos');
        Schema::dropIfExists('petshop_estetica_produtos');
        Schema::dropIfExists('petshop_esteticas_clientes_enderecos');
        Schema::dropIfExists('petshop_esteticas');
    }
};


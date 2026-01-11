<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('petshop_turmas', function (Blueprint $table) {
            $table->increments('id');

            $table->unsignedInteger('empresa_id')->nullable();
            $table->foreign('empresa_id')->references('id')->on('empresas')->onDelete('cascade');

            $table->unsignedInteger('colaborador_id')->nullable();
            $table->foreign('colaborador_id')->references('id')->on('funcionarios')->nullOnDelete();

            $table->string('nome', 191);
            $table->string('status', 20)->nullable();
            $table->string('tipo', 20)->nullable();
            $table->unsignedInteger('capacidade')->default(0);
            $table->text('descricao')->nullable();

            $table->timestamps();

            $table->index(['empresa_id']);
            $table->index(['colaborador_id']);
            $table->index(['status']);
        });

        Schema::create('petshop_turma_eventos', function (Blueprint $table) {
            $table->increments('id');

            $table->unsignedInteger('turma_id');
            $table->foreign('turma_id')->references('id')->on('petshop_turmas')->onDelete('cascade');

            $table->unsignedInteger('servico_id');
            $table->foreign('servico_id')->references('id')->on('servicos')->onDelete('cascade');

            $table->unsignedInteger('prestador_id')->nullable();
            $table->foreign('prestador_id')->references('id')->on('funcionarios')->nullOnDelete();

            $table->unsignedInteger('fornecedor_id')->nullable();
            $table->foreign('fornecedor_id')->references('id')->on('fornecedors')->nullOnDelete();

            $table->dateTime('inicio')->nullable();
            $table->dateTime('fim')->nullable();
            $table->text('descricao')->nullable();

            $table->timestamps();

            $table->index(['turma_id']);
            $table->index(['servico_id']);
            $table->index(['prestador_id']);
            $table->index(['fornecedor_id']);
            $table->index(['inicio']);
            $table->index(['fim']);
        });

        Schema::create('petshop_creches', function (Blueprint $table) {
            $table->increments('id');

            $table->unsignedInteger('empresa_id');
            $table->foreign('empresa_id')->references('id')->on('empresas')->onDelete('cascade');

            $table->unsignedInteger('animal_id')->nullable();

            $table->unsignedInteger('cliente_id')->nullable();
            $table->foreign('cliente_id')->references('id')->on('clientes')->nullOnDelete();

            $table->unsignedInteger('turma_id')->nullable();
            $table->foreign('turma_id')->references('id')->on('petshop_turmas')->nullOnDelete();

            $table->unsignedInteger('colaborador_id')->nullable();
            $table->foreign('colaborador_id')->references('id')->on('funcionarios')->nullOnDelete();

            $table->unsignedInteger('ordem_servico_id')->nullable();
            $table->foreign('ordem_servico_id')->references('id')->on('ordem_servicos')->nullOnDelete();

            $table->dateTime('data_entrada')->nullable();
            $table->dateTime('data_saida')->nullable();
            $table->string('descricao', 255)->nullable();
            $table->decimal('valor', 10, 2)->default(0);
            $table->string('estado', 64)->nullable();
            $table->boolean('situacao_checklist')->default(false);

            $table->timestamps();

            $table->index(['empresa_id']);
            $table->index(['animal_id']);
            $table->index(['cliente_id']);
            $table->index(['turma_id']);
            $table->index(['colaborador_id']);
            $table->index(['ordem_servico_id']);
            $table->index(['estado']);
            $table->index(['data_entrada']);
            $table->index(['data_saida']);
        });

        if (Schema::hasTable('petshop_animais')) {
            Schema::table('petshop_creches', function (Blueprint $table) {
                $table->foreign('animal_id')->references('id')->on('petshop_animais')->nullOnDelete();
            });
        }

        Schema::create('petshop_creches_clientes_enderecos', function (Blueprint $table) {
            $table->increments('id');

            $table->unsignedInteger('creche_id');
            $table->foreign('creche_id')->references('id')->on('petshop_creches')->onDelete('cascade');

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

            $table->index(['creche_id']);
            $table->index(['cliente_id']);
            $table->index(['cidade_id']);
        });

        Schema::create('petshop_creche_checklists', function (Blueprint $table) {
            $table->increments('id');

            $table->unsignedInteger('creche_id');
            $table->foreign('creche_id')->references('id')->on('petshop_creches')->onDelete('cascade');

            $table->unsignedInteger('empresa_id')->nullable();
            $table->foreign('empresa_id')->references('id')->on('empresas')->nullOnDelete();

            $table->string('tipo', 191)->default('entrada');
            $table->json('checklist')->nullable();

            $table->timestamps();

            $table->index(['creche_id']);
            $table->index(['empresa_id']);
            $table->index(['tipo']);
        });

        Schema::create('petshop_creche_servico', function (Blueprint $table) {
            $table->increments('id');

            $table->unsignedInteger('creche_id');
            $table->foreign('creche_id')->references('id')->on('petshop_creches')->onDelete('cascade');

            $table->unsignedInteger('servico_id');
            $table->foreign('servico_id')->references('id')->on('servicos')->onDelete('cascade');

            $table->date('data_servico')->nullable();
            $table->time('hora_servico')->nullable();
            $table->decimal('valor_servico', 10, 2)->default(0);

            $table->index(['creche_id']);
            $table->index(['servico_id']);
            $table->index(['data_servico']);
        });

        Schema::create('petshop_creche_produto', function (Blueprint $table) {
            $table->unsignedInteger('creche_id');
            $table->foreign('creche_id')->references('id')->on('petshop_creches')->onDelete('cascade');

            $table->unsignedInteger('produto_id');
            $table->foreign('produto_id')->references('id')->on('produtos')->onDelete('cascade');

            $table->decimal('quantidade', 10, 2)->default(1);

            $table->primary(['creche_id', 'produto_id']);
            $table->index(['creche_id']);
            $table->index(['produto_id']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('petshop_creche_produto');
        Schema::dropIfExists('petshop_creche_servico');
        Schema::dropIfExists('petshop_creche_checklists');
        Schema::dropIfExists('petshop_creches_clientes_enderecos');
        Schema::dropIfExists('petshop_creches');
        Schema::dropIfExists('petshop_turma_eventos');
        Schema::dropIfExists('petshop_turmas');
    }
};


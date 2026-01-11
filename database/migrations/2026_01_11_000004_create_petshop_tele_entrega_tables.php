<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('petshop_tipos_tele_entregas', function (Blueprint $table) {
            $table->increments('id');

            $table->unsignedInteger('empresa_id');
            $table->foreign('empresa_id')->references('id')->on('empresas')->onDelete('cascade');

            $table->string('nome', 200);

            $table->timestamps();
            $table->softDeletes();

            $table->index(['empresa_id']);
        });

        Schema::create('petshop_tele_entregas', function (Blueprint $table) {
            $table->increments('id');

            $table->unsignedInteger('empresa_id');
            $table->foreign('empresa_id')->references('id')->on('empresas')->onDelete('cascade');

            $table->unsignedInteger('tipo_id');
            $table->foreign('tipo_id')->references('id')->on('petshop_tipos_tele_entregas')->onDelete('cascade');

            $table->unsignedInteger('cliente_id');
            $table->foreign('cliente_id')->references('id')->on('clientes')->onDelete('cascade');

            $table->unsignedInteger('cidade_id');
            $table->foreign('cidade_id')->references('id')->on('cidades')->onDelete('cascade');

            $table->string('rua', 200);
            $table->string('numero', 20);
            $table->string('cep', 20);
            $table->string('bairro', 200);
            $table->string('complemento', 200)->nullable();
            $table->string('motorista_nome', 200)->nullable();

            $table->dateTime('datahora_entrega');
            $table->decimal('valor', 12, 2);
            $table->boolean('foi_pago')->default(false);
            $table->enum('status', ['pendente', 'entregue', 'cancelado'])->default('pendente');
            $table->string('observacao', 200)->nullable();

            $table->timestamps();
            $table->softDeletes();

            $table->index(['empresa_id']);
            $table->index(['tipo_id']);
            $table->index(['cliente_id']);
            $table->index(['cidade_id']);
            $table->index(['datahora_entrega']);
            $table->index(['status']);
            $table->index(['foi_pago']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('petshop_tele_entregas');
        Schema::dropIfExists('petshop_tipos_tele_entregas');
    }
};


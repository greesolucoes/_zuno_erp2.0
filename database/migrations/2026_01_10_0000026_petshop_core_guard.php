<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Guard migration:
 * Some environments may have `2026_01_10_000002_petshop_core_tables` marked as executed,
 * but core Pet Shop tables are missing in the database (e.g. DB restore).
 *
 * This ensures core tables exist before vet/vacinação create FKs.
 *
 * `down()` is intentionally a no-op to avoid accidental data loss.
 */
return new class extends Migration
{
    public function up(): void
    {
        $this->createMedicos();
        $this->createSalasAtendimento();
        $this->createSalasInternacao();
    }

    public function down(): void
    {
        // no-op (guard migration)
    }

    private function createMedicos(): void
    {
        if (Schema::hasTable('petshop_medicos')) {
            return;
        }

        Schema::create('petshop_medicos', function (Blueprint $table) {
            $table->increments('id');

            $table->unsignedInteger('empresa_id');
            $table->foreign('empresa_id')->references('id')->on('empresas')->onDelete('cascade');

            $table->unsignedInteger('funcionario_id');
            $table->foreign('funcionario_id')->references('id')->on('funcionarios')->onDelete('cascade');

            $table->string('crmv', 30);
            $table->string('especialidade', 191)->nullable();
            $table->string('telefone', 30)->nullable();
            $table->string('email', 191)->nullable();
            $table->text('observacoes')->nullable();
            $table->enum('status', ['ativo', 'inativo'])->default('ativo');

            $table->timestamps();
        });
    }

    private function createSalasAtendimento(): void
    {
        if (Schema::hasTable('petshop_salas_atendimento')) {
            return;
        }

        Schema::create('petshop_salas_atendimento', function (Blueprint $table) {
            $table->increments('id');

            $table->unsignedInteger('empresa_id');
            $table->foreign('empresa_id')->references('id')->on('empresas')->onDelete('cascade');

            $table->string('nome', 191);
            $table->string('identificador', 50)->nullable();
            $table->string('tipo', 100)->nullable();
            $table->unsignedInteger('capacidade')->nullable();
            $table->string('equipamentos', 191)->nullable();
            $table->text('observacoes')->nullable();
            $table->enum('status', ['disponivel', 'manutencao', 'indisponivel'])->default('disponivel');

            $table->timestamps();
        });
    }

    private function createSalasInternacao(): void
    {
        if (Schema::hasTable('petshop_salas_internacao')) {
            return;
        }

        Schema::create('petshop_salas_internacao', function (Blueprint $table) {
            $table->increments('id');

            $table->unsignedInteger('empresa_id');
            $table->foreign('empresa_id')->references('id')->on('empresas')->onDelete('cascade');

            $table->string('nome', 191);
            $table->string('identificador', 50)->nullable();
            $table->string('tipo', 100)->nullable();
            $table->unsignedInteger('capacidade')->nullable();
            $table->string('equipamentos', 191)->nullable();
            $table->text('observacoes')->nullable();
            $table->enum('status', ['disponivel', 'ocupada', 'reservada', 'manutencao'])->default('disponivel');

            $table->timestamps();
        });
    }
};


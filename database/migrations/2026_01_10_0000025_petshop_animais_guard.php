<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Guard migration:
 * - Some environments may have the Pet Shop migrations table marked as executed,
 *   but the underlying `petshop_animais_*` tables are missing (e.g. DB restore).
 * - This migration ensures the tables exist BEFORE vet/vacinação tables add FKs.
 *
 * IMPORTANT:
 * - It is intentionally safe/idempotent.
 * - `down()` is a no-op to avoid accidental data loss.
 */
return new class extends Migration
{
    private const TABLE_RENAMES = [
        'animais_especies' => 'petshop_animais_especies',
        'animais_racas' => 'petshop_animais_racas',
        'animais_pelagens' => 'petshop_animais_pelagens',
        'animais' => 'petshop_animais',
        'animais_exames' => 'petshop_animais_exames',
        'animais_diagnosticos' => 'petshop_animais_diagnosticos',
        'animais_consultas' => 'petshop_animais_consultas',
    ];

    public function up(): void
    {
        $this->renameLegacyTables();

        $this->createEspecies();
        $this->createRacas();
        $this->createPelagens();
        $this->createAnimais();
        $this->createExames();
        $this->createDiagnosticos();
        $this->createConsultas();
    }

    public function down(): void
    {
        // no-op (guard migration)
    }

    private function renameLegacyTables(): void
    {
        foreach (self::TABLE_RENAMES as $from => $to) {
            if (Schema::hasTable($from) && !Schema::hasTable($to)) {
                Schema::rename($from, $to);
            }
        }
    }

    private function createEspecies(): void
    {
        if (Schema::hasTable('petshop_animais_especies')) {
            return;
        }

        Schema::create('petshop_animais_especies', function (Blueprint $table) {
            $table->increments('id');

            $table->unsignedInteger('empresa_id')->nullable();
            $table->foreign('empresa_id')->references('id')->on('empresas')->onDelete('cascade');

            $table->string('nome', 100);

            $table->timestamps();
            $table->softDeletes();
        });
    }

    private function createRacas(): void
    {
        if (Schema::hasTable('petshop_animais_racas')) {
            return;
        }

        Schema::create('petshop_animais_racas', function (Blueprint $table) {
            $table->increments('id');

            $table->unsignedInteger('empresa_id')->nullable();
            $table->foreign('empresa_id')->references('id')->on('empresas')->onDelete('cascade');

            $table->unsignedInteger('especie_id')->nullable();
            $table->foreign('especie_id')->references('id')->on('petshop_animais_especies')->onDelete('cascade');

            $table->string('nome', 100);

            $table->timestamps();
            $table->softDeletes();
        });
    }

    private function createPelagens(): void
    {
        if (Schema::hasTable('petshop_animais_pelagens')) {
            return;
        }

        Schema::create('petshop_animais_pelagens', function (Blueprint $table) {
            $table->increments('id');

            $table->unsignedInteger('empresa_id')->nullable();
            $table->foreign('empresa_id')->references('id')->on('empresas')->onDelete('cascade');

            $table->string('nome', 100);

            $table->timestamps();
            $table->softDeletes();
        });
    }

    private function createAnimais(): void
    {
        if (Schema::hasTable('petshop_animais')) {
            return;
        }

        Schema::create('petshop_animais', function (Blueprint $table) {
            $table->increments('id');

            $table->unsignedInteger('cliente_id')->nullable();
            $table->foreign('cliente_id')->references('id')->on('clientes')->onDelete('cascade');

            $table->unsignedInteger('empresa_id')->nullable();
            $table->foreign('empresa_id')->references('id')->on('empresas')->onDelete('cascade');

            $table->unsignedInteger('especie_id')->nullable();
            $table->foreign('especie_id')->references('id')->on('petshop_animais_especies')->onDelete('set null');

            $table->unsignedInteger('raca_id')->nullable();
            $table->foreign('raca_id')->references('id')->on('petshop_animais_racas')->onDelete('set null');

            $table->unsignedInteger('pelagem_id')->nullable();
            $table->foreign('pelagem_id')->references('id')->on('petshop_animais_pelagens')->onDelete('set null');

            $table->string('cor', 20)->nullable();
            $table->string('nome', 100);
            $table->date('data_nascimento')->nullable();
            $table->decimal('peso', 10, 2)->nullable();
            $table->string('sexo', 1)->nullable();
            $table->string('idade', 20)->nullable();
            $table->string('chip', 20)->nullable();
            $table->boolean('tem_pedigree')->default(0);
            $table->string('pedigree', 20)->nullable();
            $table->string('porte', 20)->nullable();
            $table->string('origem', 20)->nullable();
            $table->string('observacao', 200)->nullable();

            $table->timestamps();
            $table->softDeletes();
        });
    }

    private function createExames(): void
    {
        if (Schema::hasTable('petshop_animais_exames')) {
            return;
        }

        Schema::create('petshop_animais_exames', function (Blueprint $table) {
            $table->increments('id');

            $table->unsignedInteger('empresa_id')->nullable();
            $table->foreign('empresa_id')->references('id')->on('empresas')->onDelete('cascade');

            $table->string('nome', 191);
            $table->text('descricao')->nullable();

            $table->timestamps();
            $table->softDeletes();
        });
    }

    private function createDiagnosticos(): void
    {
        if (Schema::hasTable('petshop_animais_diagnosticos')) {
            return;
        }

        Schema::create('petshop_animais_diagnosticos', function (Blueprint $table) {
            $table->increments('id');

            $table->unsignedInteger('empresa_id')->nullable();
            $table->foreign('empresa_id')->references('id')->on('empresas')->onDelete('cascade');

            $table->unsignedInteger('animal_id')->nullable();
            $table->foreign('animal_id')->references('id')->on('petshop_animais')->onDelete('cascade');

            $table->unsignedInteger('funcionario_id')->nullable();
            $table->foreign('funcionario_id')->references('id')->on('funcionarios')->onDelete('set null');

            $table->string('nome', 191)->nullable();
            $table->text('descricao')->nullable();
            $table->text('anamnese')->nullable();

            $table->timestamps();
            $table->softDeletes();
        });
    }

    private function createConsultas(): void
    {
        if (Schema::hasTable('petshop_animais_consultas')) {
            return;
        }

        Schema::create('petshop_animais_consultas', function (Blueprint $table) {
            $table->increments('id');

            $table->unsignedInteger('animal_id')->nullable();
            $table->foreign('animal_id')->references('id')->on('petshop_animais')->onDelete('cascade');

            $table->unsignedInteger('diagnostico_id')->nullable();
            $table->foreign('diagnostico_id')->references('id')->on('petshop_animais_diagnosticos')->onDelete('set null');

            $table->unsignedInteger('exame_id')->nullable();
            $table->foreign('exame_id')->references('id')->on('petshop_animais_exames')->onDelete('set null');

            $table->unsignedInteger('empresa_id')->nullable();
            $table->foreign('empresa_id')->references('id')->on('empresas')->onDelete('cascade');

            $table->dateTime('datahora_consulta')->nullable();
            $table->string('status', 30)->nullable();
            $table->text('observacao')->nullable();

            $table->timestamps();
            $table->softDeletes();
        });
    }
};


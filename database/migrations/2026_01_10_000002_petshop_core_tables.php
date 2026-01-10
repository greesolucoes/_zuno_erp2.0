<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $this->createConfiguracoes();
        $this->createHorariosAlternativos();

        $this->createMedicos();
        $this->createSalasAtendimento();
        $this->createSalasInternacao();

        $this->createPlanos();
        $this->createPlanoVersoes();
        $this->createPlanoServicos();
        $this->createPlanoProdutos();
        $this->createAssinaturas();
        $this->createConsumosServicos();
        $this->createConsumosProdutos();

        $this->normalizeFilialColumns();
    }

    public function down(): void
    {
        Schema::dropIfExists('petshop_consumos_produtos');
        Schema::dropIfExists('petshop_consumos_servicos');
        Schema::dropIfExists('petshop_assinaturas');
        Schema::dropIfExists('petshop_plano_produtos');
        Schema::dropIfExists('petshop_plano_servicos');
        Schema::dropIfExists('petshop_plano_versoes');
        Schema::dropIfExists('petshop_planos');

        Schema::dropIfExists('petshop_salas_internacao');
        Schema::dropIfExists('petshop_salas_atendimento');
        Schema::dropIfExists('petshop_medicos');

        Schema::dropIfExists('petshop_horarios_alternativos');
        Schema::dropIfExists('petshop_configs');
    }

    private function createConfiguracoes(): void
    {
        if (Schema::hasTable('petshop_configs')) {
            return;
        }

        Schema::create('petshop_configs', function (Blueprint $table) {
            $table->increments('id');

            $table->unsignedInteger('empresa_id');
            $table->foreign('empresa_id')->references('id')->on('empresas')->onDelete('cascade');

            $table->unsignedInteger('filial_id')->nullable();
            $table->foreign('filial_id')->references('id')->on('filials')->onDelete('cascade');

            $table->boolean('usar_agendamento_alternativo')->default(0);

            $table->timestamps();
        });
    }

    private function createHorariosAlternativos(): void
    {
        if (Schema::hasTable('petshop_horarios_alternativos')) {
            return;
        }

        Schema::create('petshop_horarios_alternativos', function (Blueprint $table) {
            $table->increments('id');

            $table->unsignedInteger('config_id');
            $table->foreign('config_id')->references('id')->on('petshop_configs')->onDelete('cascade');

            $table->tinyInteger('dia_semana');
            $table->time('hora_inicio');
            $table->time('hora_fim');

            $table->timestamps();
        });
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

    private function createPlanos(): void
    {
        if (Schema::hasTable('petshop_planos')) {
            return;
        }

        Schema::create('petshop_planos', function (Blueprint $table) {
            $table->increments('id');

            $table->string('slug', 100);
            $table->string('nome', 150);
            $table->text('descricao')->nullable();
            $table->boolean('ativo')->default(1);

            $table->enum('periodo', ['dia', 'semana', 'mes', 'ano']);
            $table->enum('frequencia_tipo', ['ilimitado', 'limitado'])->default('ilimitado');
            $table->integer('frequencia_qtd')->nullable();

            $table->decimal('preco_plano', 12, 2);
            $table->enum('multa_noshow_tipo', ['percentual', 'valor_fixo']);
            $table->decimal('multa_noshow_valor', 12, 2);
            $table->enum('bloquear_por_inadimplencia', ['sim', 'nao'])->default('nao');
            $table->integer('dias_tolerancia_atraso')->nullable();

            $table->unsignedInteger('empresa_id')->nullable();
            $table->foreign('empresa_id')->references('id')->on('empresas')->onDelete('cascade');

            $table->unsignedInteger('filial_id')->nullable();
            $table->foreign('filial_id')->references('id')->on('filials')->onDelete('cascade');

            $table->timestamps();
            $table->softDeletes();

            $table->unique(['empresa_id', 'filial_id', 'slug'], 'petshop_planos_empresa_filial_slug_unique');
        });
    }

    private function createPlanoVersoes(): void
    {
        if (Schema::hasTable('petshop_plano_versoes')) {
            return;
        }

        Schema::create('petshop_plano_versoes', function (Blueprint $table) {
            $table->increments('id');

            $table->unsignedInteger('plano_id');
            $table->foreign('plano_id')->references('id')->on('petshop_planos')->onDelete('cascade');

            $table->dateTime('vigente_desde')->nullable();
            $table->dateTime('vigente_ate')->nullable();

            $table->timestamps();
            $table->softDeletes();
        });
    }

    private function createPlanoServicos(): void
    {
        if (Schema::hasTable('petshop_plano_servicos')) {
            return;
        }

        Schema::create('petshop_plano_servicos', function (Blueprint $table) {
            $table->increments('id');

            $table->unsignedInteger('plano_versao_id');
            $table->foreign('plano_versao_id')->references('id')->on('petshop_plano_versoes')->onDelete('cascade');

            $table->unsignedInteger('servico_id')->nullable();
            $table->foreign('servico_id')->references('id')->on('servicos')->onDelete('set null');

            $table->integer('qtd_por_ciclo')->nullable();
            $table->decimal('valor_servico', 12, 2)->nullable();
            $table->string('coparticipacao_tipo', 30)->nullable();
            $table->decimal('coparticipacao_valor', 12, 2)->nullable();

            $table->timestamps();
            $table->softDeletes();
        });
    }

    private function createPlanoProdutos(): void
    {
        if (Schema::hasTable('petshop_plano_produtos')) {
            return;
        }

        Schema::create('petshop_plano_produtos', function (Blueprint $table) {
            $table->increments('id');

            $table->unsignedInteger('plano_versao_id');
            $table->foreign('plano_versao_id')->references('id')->on('petshop_plano_versoes')->onDelete('cascade');

            $table->unsignedInteger('produto_id')->nullable();
            $table->foreign('produto_id')->references('id')->on('produtos')->onDelete('set null');

            /**
             * No `bd.sql` não existe tabela padrão de variações.
             * Mantemos a coluna para compatibilidade com telas/inputs, mas só criamos FK se a tabela existir.
             */
            $table->unsignedInteger('variacao_id')->nullable();

            $table->integer('qtd_por_ciclo')->nullable();

            $table->timestamps();
            $table->softDeletes();
        });

        // FK opcional (apenas se o projeto tiver a tabela de variações).
        if (Schema::hasTable('produto_variacaos')) {
            Schema::table('petshop_plano_produtos', function (Blueprint $table) {
                $table->foreign('variacao_id')->references('id')->on('produto_variacaos')->onDelete('set null');
            });
        } elseif (Schema::hasTable('produto_variacoes')) {
            Schema::table('petshop_plano_produtos', function (Blueprint $table) {
                $table->foreign('variacao_id')->references('id')->on('produto_variacoes')->onDelete('set null');
            });
        }
    }

    private function createAssinaturas(): void
    {
        if (Schema::hasTable('petshop_assinaturas')) {
            return;
        }

        Schema::create('petshop_assinaturas', function (Blueprint $table) {
            $table->increments('id');

            $table->unsignedInteger('cliente_id')->nullable();
            $table->foreign('cliente_id')->references('id')->on('clientes')->onDelete('cascade');

            $table->unsignedInteger('plano_id')->nullable();
            $table->foreign('plano_id')->references('id')->on('petshop_planos')->onDelete('set null');

            $table->unsignedInteger('plano_versao_id')->nullable();
            $table->foreign('plano_versao_id')->references('id')->on('petshop_plano_versoes')->onDelete('set null');

            $table->string('status', 50)->nullable();
            $table->dateTime('started_at')->nullable();
            $table->dateTime('trial_end')->nullable();
            $table->dateTime('cancel_at')->nullable();
            $table->dateTime('canceled_at')->nullable();
            $table->string('billing_interval', 30)->nullable();
            $table->integer('interval_count')->nullable();
            $table->string('currency', 10)->nullable();
            $table->decimal('amount', 12, 2)->nullable();
            $table->dateTime('next_renewal_at')->nullable();
            $table->json('meta')->nullable();

            $table->timestamps();
            $table->softDeletes();
        });
    }

    private function createConsumosServicos(): void
    {
        if (Schema::hasTable('petshop_consumos_servicos')) {
            return;
        }

        Schema::create('petshop_consumos_servicos', function (Blueprint $table) {
            $table->increments('id');

            $table->unsignedInteger('assinatura_id');
            $table->foreign('assinatura_id')->references('id')->on('petshop_assinaturas')->onDelete('cascade');

            $table->unsignedInteger('servico_id');
            $table->foreign('servico_id')->references('id')->on('servicos')->onDelete('cascade');

            $table->dateTime('ciclo_inicio');
            $table->dateTime('ciclo_fim');
            $table->integer('quantidade_usada')->default(0);
            $table->dateTime('used_at');
            $table->json('meta')->nullable();

            $table->timestamp('created_at')->useCurrent();
        });
    }

    private function createConsumosProdutos(): void
    {
        if (Schema::hasTable('petshop_consumos_produtos')) {
            return;
        }

        Schema::create('petshop_consumos_produtos', function (Blueprint $table) {
            $table->increments('id');

            $table->unsignedInteger('assinatura_id');
            $table->foreign('assinatura_id')->references('id')->on('petshop_assinaturas')->onDelete('cascade');

            $table->unsignedInteger('produto_id');
            $table->foreign('produto_id')->references('id')->on('produtos')->onDelete('cascade');

            $table->dateTime('ciclo_inicio');
            $table->dateTime('ciclo_fim');
            $table->decimal('quantidade_usada', 10, 3)->default(0);
            $table->string('unidade', 10)->nullable();
            $table->dateTime('used_at');
            $table->json('meta')->nullable();

            $table->timestamp('created_at')->useCurrent();
        });
    }

    private function normalizeFilialColumns(): void
    {
        $this->normalizeFilialColumn('petshop_planos', 'local_id');
        $this->normalizeFilialColumn('petshop_configs', 'localizacao_id');
    }

    private function normalizeFilialColumn(string $table, string $legacyColumn): void
    {
        if (!Schema::hasTable($table)) {
            return;
        }

        if (Schema::hasColumn($table, 'filial_id')) {
            return;
        }

        if (Schema::hasColumn($table, $legacyColumn)) {
            Schema::table($table, function (Blueprint $tableBlueprint) {
                $tableBlueprint->unsignedInteger('filial_id')->nullable();
            });

            DB::table($table)->update([
                'filial_id' => DB::raw($legacyColumn),
            ]);

            $this->dropForeignKeyForColumnIfExists($table, $legacyColumn);

            Schema::table($table, function (Blueprint $tableBlueprint) use ($legacyColumn) {
                $tableBlueprint->dropColumn($legacyColumn);
            });
        } else {
            Schema::table($table, function (Blueprint $tableBlueprint) {
                $tableBlueprint->unsignedInteger('filial_id')->nullable();
            });
        }

        $this->ensureForeignKey($table, 'filial_id', 'filials', 'id', 'cascade');
    }

    private function dropForeignKeyForColumnIfExists(string $table, string $column): void
    {
        $database = DB::getDatabaseName();

        $constraints = DB::select(
            'SELECT CONSTRAINT_NAME as name
               FROM information_schema.KEY_COLUMN_USAGE
              WHERE TABLE_SCHEMA = ?
                AND TABLE_NAME = ?
                AND COLUMN_NAME = ?
                AND REFERENCED_TABLE_NAME IS NOT NULL',
            [$database, $table, $column]
        );

        foreach ($constraints as $constraint) {
            $name = $constraint->name ?? null;
            if (!is_string($name) || $name === '') {
                continue;
            }

            DB::statement(sprintf('ALTER TABLE `%s` DROP FOREIGN KEY `%s`', $table, $name));
        }
    }

    private function ensureForeignKey(
        string $table,
        string $column,
        string $referencedTable,
        string $referencedColumn,
        string $onDelete
    ): void {
        $database = DB::getDatabaseName();

        $constraints = DB::select(
            'SELECT CONSTRAINT_NAME as name
               FROM information_schema.KEY_COLUMN_USAGE
              WHERE TABLE_SCHEMA = ?
                AND TABLE_NAME = ?
                AND COLUMN_NAME = ?
                AND REFERENCED_TABLE_NAME = ?',
            [$database, $table, $column, $referencedTable]
        );

        if (!empty($constraints)) {
            return;
        }

        Schema::table($table, function (Blueprint $tableBlueprint) use ($column, $referencedTable, $referencedColumn, $onDelete) {
            $tableBlueprint->foreign($column)
                ->references($referencedColumn)
                ->on($referencedTable)
                ->onDelete($onDelete);
        });
    }
};

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $this->ensureFaturamentosTable();
        $this->ensureFaturamentoProdutosTable();
        $this->ensureFaturamentoServicosTable();

        $this->ensureForeignKeys();
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('petshop_vet_atendimento_faturamento_servicos');
        Schema::dropIfExists('petshop_vet_atendimento_faturamento_produtos');
        Schema::dropIfExists('petshop_vet_atendimento_faturamentos');
    }

    private function ensureFaturamentosTable(): void
    {
        if (Schema::hasTable('petshop_vet_atendimento_faturamentos')) {
            return;
        }

        Schema::create('petshop_vet_atendimento_faturamentos', function (Blueprint $table) {
            $table->increments('id');

            $table->unsignedInteger('empresa_id');
            $table->unsignedInteger('atendimento_id');

            $table->decimal('total_servicos', 12, 2)->default(0);
            $table->decimal('total_produtos', 12, 2)->default(0);
            $table->decimal('total_geral', 12, 2)->default(0);
            $table->text('observacoes')->nullable();

            $table->timestamps();
        });
    }

    private function ensureFaturamentoProdutosTable(): void
    {
        if (Schema::hasTable('petshop_vet_atendimento_faturamento_produtos')) {
            return;
        }

        Schema::create('petshop_vet_atendimento_faturamento_produtos', function (Blueprint $table) {
            $table->increments('id');

            $table->unsignedInteger('faturamento_id');
            $table->unsignedInteger('empresa_id');
            $table->unsignedInteger('produto_id')->nullable();

            $table->string('nome_produto', 191)->nullable();
            $table->decimal('quantidade', 12, 3)->default(0);
            $table->decimal('valor_unitario', 12, 2)->default(0);
            $table->decimal('subtotal', 12, 2)->default(0);

            $table->timestamps();
        });
    }

    private function ensureFaturamentoServicosTable(): void
    {
        if (Schema::hasTable('petshop_vet_atendimento_faturamento_servicos')) {
            return;
        }

        Schema::create('petshop_vet_atendimento_faturamento_servicos', function (Blueprint $table) {
            $table->increments('id');

            $table->unsignedInteger('faturamento_id');
            $table->unsignedInteger('empresa_id');
            $table->unsignedInteger('servico_id')->nullable();

            $table->string('nome_servico', 191)->nullable();
            $table->decimal('quantidade', 12, 3)->default(0);
            $table->decimal('valor_unitario', 12, 2)->default(0);
            $table->decimal('subtotal', 12, 2)->default(0);

            $table->timestamps();
        });
    }

    private function ensureForeignKeys(): void
    {
        if (!$this->canManageForeignKeys()) {
            return;
        }

        if (Schema::hasTable('petshop_vet_atendimento_faturamentos') && Schema::hasTable('empresas')) {
            $this->addForeignKeyIfMissing(
                'petshop_vet_atendimento_faturamentos',
                'petshop_vet_atendimento_faturamentos_empresa_id_foreign',
                'empresa_id',
                'empresas',
                'id',
                'cascade'
            );
        }

        if (Schema::hasTable('petshop_vet_atendimento_faturamentos') && Schema::hasTable('petshop_vet_atendimentos')) {
            $this->addForeignKeyIfMissing(
                'petshop_vet_atendimento_faturamentos',
                'petshop_vet_atendimento_faturamentos_atendimento_id_foreign',
                'atendimento_id',
                'petshop_vet_atendimentos',
                'id',
                'cascade'
            );
        }

        if (Schema::hasTable('petshop_vet_atendimento_faturamento_produtos') && Schema::hasTable('petshop_vet_atendimento_faturamentos')) {
            $this->addForeignKeyIfMissing(
                'petshop_vet_atendimento_faturamento_produtos',
                'petshop_vet_atendimento_faturamento_produtos_faturamento_id_foreign',
                'faturamento_id',
                'petshop_vet_atendimento_faturamentos',
                'id',
                'cascade'
            );
        }

        if (Schema::hasTable('petshop_vet_atendimento_faturamento_produtos') && Schema::hasTable('empresas')) {
            $this->addForeignKeyIfMissing(
                'petshop_vet_atendimento_faturamento_produtos',
                'petshop_vet_atendimento_faturamento_produtos_empresa_id_foreign',
                'empresa_id',
                'empresas',
                'id',
                'cascade'
            );
        }

        if (Schema::hasTable('petshop_vet_atendimento_faturamento_produtos') && Schema::hasTable('produtos')) {
            $this->addForeignKeyIfMissing(
                'petshop_vet_atendimento_faturamento_produtos',
                'petshop_vet_atendimento_faturamento_produtos_produto_id_foreign',
                'produto_id',
                'produtos',
                'id',
                'set null'
            );
        }

        if (Schema::hasTable('petshop_vet_atendimento_faturamento_servicos') && Schema::hasTable('petshop_vet_atendimento_faturamentos')) {
            $this->addForeignKeyIfMissing(
                'petshop_vet_atendimento_faturamento_servicos',
                'petshop_vet_atendimento_faturamento_servicos_faturamento_id_foreign',
                'faturamento_id',
                'petshop_vet_atendimento_faturamentos',
                'id',
                'cascade'
            );
        }

        if (Schema::hasTable('petshop_vet_atendimento_faturamento_servicos') && Schema::hasTable('empresas')) {
            $this->addForeignKeyIfMissing(
                'petshop_vet_atendimento_faturamento_servicos',
                'petshop_vet_atendimento_faturamento_servicos_empresa_id_foreign',
                'empresa_id',
                'empresas',
                'id',
                'cascade'
            );
        }

        if (Schema::hasTable('petshop_vet_atendimento_faturamento_servicos') && Schema::hasTable('servicos')) {
            $this->addForeignKeyIfMissing(
                'petshop_vet_atendimento_faturamento_servicos',
                'petshop_vet_atendimento_faturamento_servicos_servico_id_foreign',
                'servico_id',
                'servicos',
                'id',
                'set null'
            );
        }
    }

    private function canManageForeignKeys(): bool
    {
        try {
            DB::statement('SELECT 1');
            return true;
        } catch (\Throwable $exception) {
            return false;
        }
    }

    private function addForeignKeyIfMissing(
        string $table,
        string $foreignKeyName,
        string $column,
        string $referencesTable,
        string $referencesColumn,
        string $onDelete
    ): void {
        try {
            $exists = DB::table('information_schema.TABLE_CONSTRAINTS')
                ->where('CONSTRAINT_SCHEMA', DB::getDatabaseName())
                ->where('TABLE_NAME', $table)
                ->where('CONSTRAINT_NAME', $foreignKeyName)
                ->where('CONSTRAINT_TYPE', 'FOREIGN KEY')
                ->exists();
        } catch (\Throwable $exception) {
            return;
        }

        if ($exists) {
            return;
        }

        try {
            Schema::table($table, function (Blueprint $tableBlueprint) use (
                $column,
                $referencesTable,
                $referencesColumn,
                $onDelete
            ) {
                $tableBlueprint->foreign($column)->references($referencesColumn)->on($referencesTable)->onDelete($onDelete);
            });
        } catch (\Throwable $exception) {
            // ignore foreign key issues (legacy DB)
        }
    }
};

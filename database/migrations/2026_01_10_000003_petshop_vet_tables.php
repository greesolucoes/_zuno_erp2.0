<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $this->createVetCadastros();
        $this->createVetModelos();
        $this->createAtendimentos();
        $this->createProntuarios();
        $this->createPrescricoes();
        $this->createVetExames();
        $this->createInternacoes();
    }

    public function down(): void
    {
        Schema::dropIfExists('petshop_vet_internacao_status');
        Schema::dropIfExists('petshop_vet_internacoes');

        Schema::dropIfExists('petshop_vet_exame_analises');
        Schema::dropIfExists('petshop_vet_exame_anexos');
        Schema::dropIfExists('petshop_vet_exames');

        Schema::dropIfExists('petshop_vet_prescricao_condicao_cronica');
        Schema::dropIfExists('petshop_vet_prescricao_alergia');
        Schema::dropIfExists('petshop_vet_prescricao_canais');
        Schema::dropIfExists('petshop_vet_prescricao_medicamentos');
        Schema::dropIfExists('petshop_vet_prescricoes');

        Schema::dropIfExists('petshop_vet_prontuario_evolucoes');
        Schema::dropIfExists('petshop_vet_prontuarios');

        Schema::dropIfExists('petshop_vet_atendimento_faturamento_servicos');
        Schema::dropIfExists('petshop_vet_atendimento_faturamento_produtos');
        Schema::dropIfExists('petshop_vet_atendimento_faturamentos');
        Schema::dropIfExists('petshop_vet_atendimento_anexos');
        Schema::dropIfExists('petshop_vet_atendimentos');

        Schema::dropIfExists('petshop_vet_vacina_especies');
        Schema::dropIfExists('petshop_vet_vacinas');

        Schema::dropIfExists('petshop_vet_medicamento_especies');
        Schema::dropIfExists('petshop_vet_medicamentos');

        Schema::dropIfExists('petshop_vet_modelos_prescricao');
        Schema::dropIfExists('petshop_vet_modelos_avaliacao');
        Schema::dropIfExists('petshop_vet_modelos_atendimento');

        Schema::dropIfExists('petshop_vet_condicoes_cronicas');
        Schema::dropIfExists('petshop_vet_alergias');
        Schema::dropIfExists('petshop_vet_checklists');
    }

    private function createVetCadastros(): void
    {
        if (!Schema::hasTable('petshop_vet_checklists')) {
            Schema::create('petshop_vet_checklists', function (Blueprint $table) {
                $table->increments('id');
                $table->unsignedInteger('empresa_id');
                $table->foreign('empresa_id')->references('id')->on('empresas')->onDelete('cascade');

                $table->string('titulo', 191);
                $table->text('descricao')->nullable();
                $table->string('tipo', 50)->nullable();
                $table->json('itens')->nullable();
                $table->string('status', 30)->nullable();

                $table->timestamps();
            });
        }

        if (!Schema::hasTable('petshop_vet_alergias')) {
            Schema::create('petshop_vet_alergias', function (Blueprint $table) {
                $table->increments('id');
                $table->unsignedInteger('empresa_id');
                $table->foreign('empresa_id')->references('id')->on('empresas')->onDelete('cascade');

                $table->string('nome', 191);
                $table->text('descricao')->nullable();
                $table->text('orientacoes')->nullable();
                $table->enum('status', ['ativo', 'inativo'])->default('ativo');

                $table->timestamps();
            });
        }

        if (!Schema::hasTable('petshop_vet_condicoes_cronicas')) {
            Schema::create('petshop_vet_condicoes_cronicas', function (Blueprint $table) {
                $table->increments('id');
                $table->unsignedInteger('empresa_id');
                $table->foreign('empresa_id')->references('id')->on('empresas')->onDelete('cascade');

                $table->string('nome', 191);
                $table->text('descricao')->nullable();
                $table->text('orientacoes')->nullable();
                $table->enum('status', ['ativo', 'inativo'])->default('ativo');

                $table->timestamps();
            });
        }

        if (!Schema::hasTable('petshop_vet_medicamentos')) {
            Schema::create('petshop_vet_medicamentos', function (Blueprint $table) {
                $table->increments('id');

                $table->unsignedInteger('empresa_id')->nullable();
                $table->foreign('empresa_id')->references('id')->on('empresas')->onDelete('cascade');

                $table->unsignedInteger('produto_id')->nullable();
                $table->foreign('produto_id')->references('id')->on('produtos')->onDelete('set null');

                $table->string('nome_comercial', 191)->nullable();
                $table->string('nome_generico', 191)->nullable();
                $table->string('classe_terapeutica', 191)->nullable();
                $table->string('classe_farmacologica', 191)->nullable();
                $table->string('classificacao_controle', 191)->nullable();
                $table->string('via_administracao', 191)->nullable();
                $table->string('apresentacao', 191)->nullable();
                $table->string('concentracao', 191)->nullable();
                $table->string('forma_dispensacao', 191)->nullable();
                $table->string('dosagem', 191)->nullable();
                $table->string('frequencia', 191)->nullable();
                $table->string('duracao', 191)->nullable();
                $table->string('restricao_idade', 191)->nullable();
                $table->string('condicao_armazenamento', 191)->nullable();
                $table->string('validade', 191)->nullable();
                $table->string('fornecedor', 191)->nullable();
                $table->string('sku', 191)->nullable();
                $table->text('indicacoes')->nullable();
                $table->text('contraindicacoes')->nullable();
                $table->text('efeitos_adversos')->nullable();
                $table->text('interacoes')->nullable();
                $table->text('monitoramento')->nullable();
                $table->text('orientacoes_tutor')->nullable();
                $table->text('observacoes')->nullable();
                $table->string('status', 191)->nullable();

                $table->timestamps();
            });
        }

        if (!Schema::hasTable('petshop_vet_medicamento_especies')) {
            Schema::create('petshop_vet_medicamento_especies', function (Blueprint $table) {
                $table->increments('id');

                $table->unsignedInteger('medicamento_id');
                $table->foreign('medicamento_id')->references('id')->on('petshop_vet_medicamentos')->onDelete('cascade');

                $table->unsignedInteger('especie_id');
                $table->foreign('especie_id')->references('id')->on('petshop_animais_especies')->onDelete('cascade');

                $table->timestamps();
            });
        }

        if (!Schema::hasTable('petshop_vet_vacinas')) {
            Schema::create('petshop_vet_vacinas', function (Blueprint $table) {
                $table->increments('id');

                $table->unsignedInteger('empresa_id')->nullable();
                $table->foreign('empresa_id')->references('id')->on('empresas')->onDelete('cascade');

                $table->unsignedInteger('produto_id')->nullable();
                $table->foreign('produto_id')->references('id')->on('produtos')->onDelete('set null');

                $table->string('codigo', 191)->nullable();
                $table->string('nome', 191);
                $table->string('status', 30)->default('ativa');

                $table->string('grupo_vacinal', 50)->nullable();
                $table->string('categoria', 50)->nullable();
                $table->string('fabricante', 100)->nullable();
                $table->string('registro_mapa', 50)->nullable();
                $table->string('apresentacao', 50)->nullable();
                $table->string('concentracao', 50)->nullable();
                $table->string('idade_minima', 50)->nullable();
                $table->string('intervalo_reforco', 50)->nullable();
                $table->string('dosagem', 50)->nullable();
                $table->string('via_administracao', 50)->nullable();
                $table->string('local_aplicacao', 50)->nullable();

                $table->text('coberturas')->nullable();
                $table->text('protocolo_inicial')->nullable();
                $table->text('protocolo_reforco')->nullable();
                $table->text('protocolo_revacinar')->nullable();
                $table->text('requisitos_pre_vacinacao')->nullable();
                $table->text('orientacoes_pos_vacinacao')->nullable();
                $table->text('efeitos_adversos')->nullable();
                $table->text('contraindicacoes')->nullable();

                $table->string('validade_fechada', 50)->nullable();
                $table->string('validade_aberta', 50)->nullable();
                $table->string('condicao_armazenamento', 50)->nullable();
                $table->string('temperatura_armazenamento', 50)->nullable();
                $table->text('alertas_armazenamento')->nullable();
                $table->string('limite_perdas', 50)->nullable();
                $table->string('tempo_reposicao', 50)->nullable();

                $table->json('documentos')->nullable();
                $table->json('tags')->nullable();
                $table->text('observacoes')->nullable();

                $table->timestamps();
            });
        }

        if (!Schema::hasTable('petshop_vet_vacina_especies')) {
            Schema::create('petshop_vet_vacina_especies', function (Blueprint $table) {
                $table->increments('id');

                $table->unsignedInteger('vacina_id');
                $table->foreign('vacina_id')->references('id')->on('petshop_vet_vacinas')->onDelete('cascade');

                $table->unsignedInteger('especie_id');
                $table->foreign('especie_id')->references('id')->on('petshop_animais_especies')->onDelete('cascade');

                $table->timestamps();
            });
        }
    }

    private function createVetModelos(): void
    {
        if (!Schema::hasTable('petshop_vet_modelos_atendimento')) {
            Schema::create('petshop_vet_modelos_atendimento', function (Blueprint $table) {
                $table->increments('id');
                $table->unsignedInteger('empresa_id');
                $table->foreign('empresa_id')->references('id')->on('empresas')->onDelete('cascade');

                $table->unsignedBigInteger('created_by')->nullable();
                $table->foreign('created_by')->references('id')->on('users')->onDelete('set null');

                $table->unsignedBigInteger('updated_by')->nullable();
                $table->foreign('updated_by')->references('id')->on('users')->onDelete('set null');

                $table->string('title', 191);
                $table->string('category', 191)->nullable();
                $table->text('notes')->nullable();
                $table->longText('content')->nullable();
                $table->string('status', 30)->default('ativo');

                $table->timestamps();
            });
        }

        if (!Schema::hasTable('petshop_vet_modelos_avaliacao')) {
            Schema::create('petshop_vet_modelos_avaliacao', function (Blueprint $table) {
                $table->increments('id');
                $table->unsignedInteger('empresa_id');
                $table->foreign('empresa_id')->references('id')->on('empresas')->onDelete('cascade');

                $table->unsignedBigInteger('created_by')->nullable();
                $table->foreign('created_by')->references('id')->on('users')->onDelete('set null');

                $table->unsignedBigInteger('updated_by')->nullable();
                $table->foreign('updated_by')->references('id')->on('users')->onDelete('set null');

                $table->string('title', 191);
                $table->string('category', 191)->nullable();
                $table->text('notes')->nullable();
                $table->json('fields')->nullable();
                $table->string('status', 30)->default('ativo');

                $table->timestamps();
            });
        }

        if (!Schema::hasTable('petshop_vet_modelos_prescricao')) {
            Schema::create('petshop_vet_modelos_prescricao', function (Blueprint $table) {
                $table->increments('id');
                $table->unsignedInteger('empresa_id');
                $table->foreign('empresa_id')->references('id')->on('empresas')->onDelete('cascade');

                $table->unsignedBigInteger('created_by')->nullable();
                $table->foreign('created_by')->references('id')->on('users')->onDelete('set null');

                $table->unsignedBigInteger('updated_by')->nullable();
                $table->foreign('updated_by')->references('id')->on('users')->onDelete('set null');

                $table->string('title', 191);
                $table->string('category', 191)->nullable();
                $table->text('notes')->nullable();
                $table->json('fields')->nullable();
                $table->string('status', 30)->default('ativo');

                $table->timestamps();
            });
        }
    }

    private function createAtendimentos(): void
    {
        if (Schema::hasTable('petshop_vet_atendimentos')) {
            return;
        }

        Schema::create('petshop_vet_atendimentos', function (Blueprint $table) {
            $table->increments('id');

            $table->unsignedInteger('empresa_id');
            $table->foreign('empresa_id')->references('id')->on('empresas')->onDelete('cascade');

            $table->unsignedInteger('animal_id')->nullable();
            $table->foreign('animal_id')->references('id')->on('petshop_animais')->onDelete('set null');

            $table->unsignedInteger('tutor_id')->nullable();
            $table->foreign('tutor_id')->references('id')->on('clientes')->onDelete('set null');

            $table->string('tutor_nome', 191)->nullable();
            $table->string('contato_tutor', 50)->nullable();
            $table->string('email_tutor', 191)->nullable();

            $table->unsignedInteger('veterinario_id')->nullable();
            $table->foreign('veterinario_id')->references('id')->on('petshop_medicos')->onDelete('set null');

            $table->unsignedInteger('sala_id')->nullable();
            $table->foreign('sala_id')->references('id')->on('petshop_salas_atendimento')->onDelete('set null');

            $table->unsignedInteger('servico_id')->nullable();
            $table->foreign('servico_id')->references('id')->on('servicos')->onDelete('set null');

            $table->date('data_atendimento')->nullable();
            $table->string('horario', 10)->nullable();
            $table->string('status', 40)->default('agendado');
            $table->string('tipo_atendimento', 50)->nullable();

            $table->text('motivo_visita')->nullable();

            $table->decimal('peso', 10, 2)->nullable();
            $table->decimal('temperatura', 10, 2)->nullable();
            $table->integer('frequencia_cardiaca')->nullable();
            $table->integer('frequencia_respiratoria')->nullable();

            $table->text('observacoes_triagem')->nullable();
            $table->json('checklists')->nullable();

            $table->string('codigo', 50)->nullable();

            $table->timestamps();
        });

        Schema::create('petshop_vet_atendimento_anexos', function (Blueprint $table) {
            $table->increments('id');

            $table->unsignedInteger('atendimento_id');
            $table->foreign('atendimento_id')->references('id')->on('petshop_vet_atendimentos')->onDelete('cascade');

            $table->string('name', 191);
            $table->string('path', 191);
            $table->string('url', 191)->nullable();
            $table->string('extension', 20)->nullable();
            $table->string('mime_type', 191)->nullable();
            $table->unsignedBigInteger('size_in_bytes')->nullable();
            $table->dateTime('uploaded_at')->nullable();
            $table->unsignedBigInteger('uploaded_by')->nullable();
            $table->foreign('uploaded_by')->references('id')->on('users')->onDelete('set null');

            $table->timestamps();
        });

        Schema::create('petshop_vet_atendimento_faturamentos', function (Blueprint $table) {
            $table->increments('id');

            $table->unsignedInteger('empresa_id');
            $table->foreign('empresa_id')->references('id')->on('empresas')->onDelete('cascade');

            $table->unsignedInteger('atendimento_id');
            $table->foreign('atendimento_id')->references('id')->on('petshop_vet_atendimentos')->onDelete('cascade');

            $table->decimal('total_servicos', 12, 2)->default(0);
            $table->decimal('total_produtos', 12, 2)->default(0);
            $table->decimal('total_geral', 12, 2)->default(0);
            $table->text('observacoes')->nullable();

            $table->timestamps();
        });

        Schema::create('petshop_vet_atendimento_faturamento_produtos', function (Blueprint $table) {
            $table->increments('id');

            $table->unsignedInteger('faturamento_id');
            $table->foreign('faturamento_id')->references('id')->on('petshop_vet_atendimento_faturamentos')->onDelete('cascade');

            $table->unsignedInteger('empresa_id');
            $table->foreign('empresa_id')->references('id')->on('empresas')->onDelete('cascade');

            $table->unsignedInteger('produto_id')->nullable();
            $table->foreign('produto_id')->references('id')->on('produtos')->onDelete('set null');

            $table->string('nome_produto', 191)->nullable();
            $table->decimal('quantidade', 12, 3)->default(0);
            $table->decimal('valor_unitario', 12, 2)->default(0);
            $table->decimal('subtotal', 12, 2)->default(0);

            $table->timestamps();
        });

        Schema::create('petshop_vet_atendimento_faturamento_servicos', function (Blueprint $table) {
            $table->increments('id');

            $table->unsignedInteger('faturamento_id');
            $table->foreign('faturamento_id')->references('id')->on('petshop_vet_atendimento_faturamentos')->onDelete('cascade');

            $table->unsignedInteger('empresa_id');
            $table->foreign('empresa_id')->references('id')->on('empresas')->onDelete('cascade');

            $table->unsignedInteger('servico_id')->nullable();
            $table->foreign('servico_id')->references('id')->on('servicos')->onDelete('set null');

            $table->string('nome_servico', 191)->nullable();
            $table->string('categoria_servico', 191)->nullable();
            $table->date('data_servico')->nullable();
            $table->time('hora_servico')->nullable();
            $table->decimal('valor', 12, 2)->default(0);

            $table->timestamps();
        });
    }

    private function createProntuarios(): void
    {
        if (Schema::hasTable('petshop_vet_prontuarios')) {
            return;
        }

        Schema::create('petshop_vet_prontuarios', function (Blueprint $table) {
            $table->increments('id');

            $table->unsignedInteger('empresa_id')->nullable();
            $table->foreign('empresa_id')->references('id')->on('empresas')->onDelete('cascade');

            $table->unsignedInteger('atendimento_id')->nullable();
            $table->foreign('atendimento_id')->references('id')->on('petshop_vet_atendimentos')->onDelete('set null');

            $table->unsignedInteger('animal_id')->nullable();
            $table->foreign('animal_id')->references('id')->on('petshop_animais')->onDelete('set null');

            $table->unsignedInteger('tutor_id')->nullable();
            $table->foreign('tutor_id')->references('id')->on('clientes')->onDelete('set null');

            $table->unsignedInteger('veterinario_id')->nullable();
            $table->foreign('veterinario_id')->references('id')->on('petshop_medicos')->onDelete('set null');

            $table->unsignedInteger('modelo_avaliacao_id')->nullable();
            $table->foreign('modelo_avaliacao_id')->references('id')->on('petshop_vet_modelos_avaliacao')->onDelete('set null');

            $table->string('codigo', 50)->nullable();
            $table->string('status', 50)->default('draft');
            $table->string('tipo', 50)->nullable();
            $table->dateTime('data_registro')->nullable();

            $table->text('resumo_rapido')->nullable();
            $table->longText('resumo')->nullable();
            $table->longText('queixa_principal')->nullable();
            $table->longText('historico_clinico')->nullable();
            $table->longText('avaliacao_fisica')->nullable();
            $table->longText('diagnostico_presuntivo')->nullable();
            $table->longText('diagnostico_definitivo')->nullable();
            $table->longText('plano_terapeutico')->nullable();
            $table->longText('orientacoes_tutor')->nullable();
            $table->longText('observacoes_adicionais')->nullable();

            $table->json('sinais_vitais')->nullable();
            $table->json('avaliacao_personalizada')->nullable();
            $table->json('campos_avaliacao')->nullable();
            $table->json('snapshot_paciente')->nullable();
            $table->json('snapshot_tutor')->nullable();
            $table->json('dados_triagem')->nullable();
            $table->json('lembretes')->nullable();
            $table->json('checklists')->nullable();
            $table->json('comunicacoes')->nullable();
            $table->json('anexos')->nullable();
            $table->json('metadata')->nullable();

            $table->unsignedBigInteger('created_by')->nullable();
            $table->foreign('created_by')->references('id')->on('users')->onDelete('set null');

            $table->unsignedBigInteger('updated_by')->nullable();
            $table->foreign('updated_by')->references('id')->on('users')->onDelete('set null');

            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('petshop_vet_prontuario_evolucoes', function (Blueprint $table) {
            $table->increments('id');

            $table->unsignedInteger('prontuario_id');
            $table->foreign('prontuario_id')->references('id')->on('petshop_vet_prontuarios')->onDelete('cascade');

            $table->string('categoria', 50)->nullable();
            $table->string('titulo', 191)->nullable();
            $table->longText('descricao')->nullable();
            $table->dateTime('registrado_em')->nullable();

            $table->unsignedBigInteger('registrado_por')->nullable();
            $table->foreign('registrado_por')->references('id')->on('users')->onDelete('set null');

            $table->json('dados')->nullable();

            $table->timestamps();
        });
    }

    private function createPrescricoes(): void
    {
        if (!Schema::hasTable('petshop_vet_prescricoes')) {
            Schema::create('petshop_vet_prescricoes', function (Blueprint $table) {
                $table->increments('id');

                $table->unsignedInteger('empresa_id')->nullable();
                $table->foreign('empresa_id')->references('id')->on('empresas')->onDelete('cascade');

                $table->unsignedInteger('animal_id')->nullable();
                $table->foreign('animal_id')->references('id')->on('petshop_animais')->onDelete('cascade');

                $table->unsignedInteger('veterinario_id')->nullable();
                $table->foreign('veterinario_id')->references('id')->on('petshop_medicos')->onDelete('set null');

                $table->unsignedInteger('atendimento_id')->nullable();
                $table->foreign('atendimento_id')->references('id')->on('petshop_vet_atendimentos')->onDelete('set null');

                $table->unsignedInteger('prontuario_id')->nullable();
                $table->foreign('prontuario_id')->references('id')->on('petshop_vet_prontuarios')->onDelete('set null');

                $table->unsignedInteger('modelo_prescricao_id')->nullable();
                $table->foreign('modelo_prescricao_id')->references('id')->on('petshop_vet_modelos_prescricao')->onDelete('set null');

                $table->string('diagnostico', 255)->nullable();
                $table->text('resumo')->nullable();
                $table->text('observacoes')->nullable();
                $table->text('orientacoes')->nullable();
                $table->string('dispensacao_id', 255)->nullable();
                $table->text('dispensacao_observacoes')->nullable();
                $table->json('campos_personalizados')->nullable();
                $table->dateTime('emitida_em')->nullable();
                $table->string('status', 50)->default('emitida');

                $table->unsignedBigInteger('created_by')->nullable();
                $table->foreign('created_by')->references('id')->on('users')->onDelete('set null');

                $table->unsignedBigInteger('updated_by')->nullable();
                $table->foreign('updated_by')->references('id')->on('users')->onDelete('set null');

                $table->timestamps();
            });
        }

        if (!Schema::hasTable('petshop_vet_prescricao_medicamentos')) {
            Schema::create('petshop_vet_prescricao_medicamentos', function (Blueprint $table) {
                $table->increments('id');

                $table->unsignedInteger('prescricao_id');
                $table->foreign('prescricao_id')->references('id')->on('petshop_vet_prescricoes')->onDelete('cascade');

                $table->unsignedInteger('medicamento_id')->nullable();
                $table->foreign('medicamento_id')->references('id')->on('petshop_vet_medicamentos')->onDelete('set null');

                $table->string('nome', 191)->nullable();
                $table->string('dosagem', 191)->nullable();
                $table->string('frequencia', 191)->nullable();
                $table->string('duracao', 191)->nullable();
                $table->string('via', 191)->nullable();
                $table->text('observacoes')->nullable();

                $table->timestamps();
            });
        }

        if (!Schema::hasTable('petshop_vet_prescricao_canais')) {
            Schema::create('petshop_vet_prescricao_canais', function (Blueprint $table) {
                $table->increments('id');

                $table->unsignedInteger('prescricao_id');
                $table->foreign('prescricao_id')->references('id')->on('petshop_vet_prescricoes')->onDelete('cascade');

                $table->string('canal', 50)->nullable();

                $table->timestamps();
            });
        }

        if (!Schema::hasTable('petshop_vet_prescricao_alergia')) {
            Schema::create('petshop_vet_prescricao_alergia', function (Blueprint $table) {
                $table->increments('id');

                $table->unsignedInteger('prescricao_id');
                $table->foreign('prescricao_id')->references('id')->on('petshop_vet_prescricoes')->onDelete('cascade');

                $table->unsignedInteger('alergia_id');
                $table->foreign('alergia_id')->references('id')->on('petshop_vet_alergias')->onDelete('cascade');

                $table->timestamps();
            });
        }

        if (!Schema::hasTable('petshop_vet_prescricao_condicao_cronica')) {
            Schema::create('petshop_vet_prescricao_condicao_cronica', function (Blueprint $table) {
                $table->increments('id');

                $table->unsignedInteger('prescricao_id');
                // Nome curto para evitar erro de "identifier too long" no MySQL.
                $table->foreign('prescricao_id', 'ps_vet_pcc_presc_fk')
                    ->references('id')
                    ->on('petshop_vet_prescricoes')
                    ->onDelete('cascade');

                $table->unsignedInteger('condicao_cronica_id');
                // Nome curto para evitar erro de "identifier too long" no MySQL.
                $table->foreign('condicao_cronica_id', 'ps_vet_pcc_cond_fk')
                    ->references('id')
                    ->on('petshop_vet_condicoes_cronicas')
                    ->onDelete('cascade');

                $table->timestamps();
            });
        } else {
            // Caso a tabela tenha sido criada numa tentativa anterior sem as FKs (migration falhou no meio).
            $this->ensureForeignKey('petshop_vet_prescricao_condicao_cronica', 'prescricao_id', 'petshop_vet_prescricoes', 'id', 'cascade', 'ps_vet_pcc_presc_fk');
            $this->ensureForeignKey('petshop_vet_prescricao_condicao_cronica', 'condicao_cronica_id', 'petshop_vet_condicoes_cronicas', 'id', 'cascade', 'ps_vet_pcc_cond_fk');
        }
    }

    private function ensureForeignKey(
        string $table,
        string $column,
        string $referencedTable,
        string $referencedColumn,
        string $onDelete,
        string $constraintName
    ): void {
        if (!Schema::hasTable($table) || !Schema::hasTable($referencedTable) || !Schema::hasColumn($table, $column)) {
            return;
        }

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

        if (!empty($constraints)) {
            return;
        }

        Schema::table($table, function (Blueprint $blueprint) use (
            $column,
            $referencedTable,
            $referencedColumn,
            $onDelete,
            $constraintName
        ) {
            $blueprint->foreign($column, $constraintName)
                ->references($referencedColumn)
                ->on($referencedTable)
                ->onDelete($onDelete);
        });
    }

    private function createVetExames(): void
    {
        if (Schema::hasTable('petshop_vet_exames')) {
            return;
        }

        Schema::create('petshop_vet_exames', function (Blueprint $table) {
            $table->increments('id');

            $table->unsignedInteger('empresa_id')->nullable();
            $table->foreign('empresa_id')->references('id')->on('empresas')->onDelete('cascade');

            $table->unsignedInteger('atendimento_id')->nullable();
            $table->foreign('atendimento_id')->references('id')->on('petshop_vet_atendimentos')->onDelete('set null');

            $table->unsignedInteger('animal_id')->nullable();
            $table->foreign('animal_id')->references('id')->on('petshop_animais')->onDelete('cascade');

            $table->unsignedInteger('medico_id')->nullable();
            $table->foreign('medico_id')->references('id')->on('petshop_medicos')->onDelete('set null');

            $table->unsignedInteger('exame_id')->nullable();
            $table->foreign('exame_id')->references('id')->on('petshop_animais_exames')->onDelete('set null');

            $table->date('data_prevista_coleta')->nullable();
            $table->string('laboratorio_parceiro', 191)->nullable();
            $table->string('prioridade', 20)->default('normal');
            $table->text('observacoes_clinicas')->nullable();
            $table->longText('laudo')->nullable();
            $table->dateTime('data_conclusao')->nullable();
            $table->string('status', 40)->default('solicitado');

            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('petshop_vet_exame_anexos', function (Blueprint $table) {
            $table->increments('id');

            $table->unsignedInteger('exame_id');
            $table->foreign('exame_id')->references('id')->on('petshop_vet_exames')->onDelete('cascade');

            $table->string('context', 20)->default('request');
            $table->string('name', 191);
            $table->string('path', 191);
            $table->string('url', 191)->nullable();
            $table->string('extension', 20)->nullable();
            $table->string('mime_type', 100)->nullable();
            $table->unsignedBigInteger('size_in_bytes')->nullable();
            $table->dateTime('uploaded_at')->nullable();

            $table->unsignedBigInteger('uploaded_by')->nullable();
            $table->foreign('uploaded_by')->references('id')->on('users')->onDelete('set null');

            $table->timestamps();
        });

        Schema::create('petshop_vet_exame_analises', function (Blueprint $table) {
            $table->increments('id');

            $table->unsignedInteger('exame_id');
            $table->foreign('exame_id')->references('id')->on('petshop_vet_exames')->onDelete('cascade');

            $table->unsignedInteger('attachment_id')->nullable();
            $table->foreign('attachment_id')->references('id')->on('petshop_vet_exame_anexos')->onDelete('set null');

            $table->json('tool_state')->nullable();
            $table->json('viewport_state')->nullable();

            $table->timestamps();
        });
    }

    private function createInternacoes(): void
    {
        if (!Schema::hasTable('petshop_vet_internacoes')) {
            Schema::create('petshop_vet_internacoes', function (Blueprint $table) {
                $table->increments('id');

                $table->unsignedInteger('empresa_id');
                $table->foreign('empresa_id')->references('id')->on('empresas')->onDelete('cascade');

                $table->unsignedInteger('animal_id');
                $table->foreign('animal_id')->references('id')->on('petshop_animais')->onDelete('cascade');

                $table->unsignedInteger('tutor_id')->nullable();
                $table->foreign('tutor_id')->references('id')->on('clientes')->onDelete('set null');

                $table->unsignedInteger('atendimento_id')->nullable();
                $table->foreign('atendimento_id')->references('id')->on('petshop_vet_atendimentos')->onDelete('set null');

                $table->unsignedInteger('veterinario_id')->nullable();
                $table->foreign('veterinario_id')->references('id')->on('petshop_medicos')->onDelete('set null');

                $table->unsignedInteger('sala_internacao_id')->nullable();
                $table->foreign('sala_internacao_id')->references('id')->on('petshop_salas_internacao')->onDelete('set null');

                $table->enum('status', ['rascunho', 'ativo', 'alta', 'cancelado'])->default('ativo');
                $table->enum('nivel_risco', ['baixo', 'moderado', 'alto'])->nullable();

                $table->dateTime('internado_em')->nullable();
                $table->dateTime('previsao_alta_em')->nullable();
                $table->dateTime('alta_em')->nullable();

                $table->string('motivo', 500)->nullable();
                $table->text('observacoes')->nullable();

                $table->timestamps();
                $table->softDeletes();
            });
        } else {
            // Caso a tabela tenha sido criada numa tentativa anterior (migration falhou no meio).
            $this->ensureForeignKey('petshop_vet_internacoes', 'sala_internacao_id', 'petshop_salas_internacao', 'id', 'set null', 'ps_vet_int_sala_fk');
        }

        if (!Schema::hasTable('petshop_vet_internacao_status')) {
            Schema::create('petshop_vet_internacao_status', function (Blueprint $table) {
                $table->increments('id');

                $table->unsignedInteger('empresa_id');
                $table->foreign('empresa_id')->references('id')->on('empresas')->onDelete('cascade');

                $table->unsignedInteger('internacao_id');
                $table->foreign('internacao_id')->references('id')->on('petshop_vet_internacoes')->onDelete('cascade');

                $table->string('status', 191);
                $table->text('anotacao')->nullable();
                $table->enum('evolucao', ['sim', 'nao', 'normal'])->default('normal');

                $table->timestamps();
            });
        }
    }
};

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $this->createVacinacoes();
        $this->createVacinacaoDoses();
        $this->createVacinacaoSessoes();
        $this->createVacinacaoSessaoDoses();
        $this->createVacinacaoEventos();
    }

    public function down(): void
    {
        Schema::dropIfExists('petshop_vacinacao_eventos');
        Schema::dropIfExists('petshop_vacinacao_sessao_doses');
        Schema::dropIfExists('petshop_vacinacao_sessoes');
        Schema::dropIfExists('petshop_vacinacao_doses');
        Schema::dropIfExists('petshop_vacinacoes');
    }

    private function createVacinacoes(): void
    {
        if (Schema::hasTable('petshop_vacinacoes')) {
            return;
        }

        Schema::create('petshop_vacinacoes', function (Blueprint $table) {
            $table->increments('id');

            $table->unsignedInteger('empresa_id');
            $table->foreign('empresa_id')->references('id')->on('empresas')->onDelete('cascade');

            $table->unsignedInteger('animal_id');
            $table->foreign('animal_id')->references('id')->on('petshop_animais')->onDelete('cascade');

            $table->unsignedInteger('cliente_id')->nullable();
            $table->foreign('cliente_id')->references('id')->on('clientes')->onDelete('set null');

            $table->unsignedInteger('protocolo_id')->nullable();
            $table->foreign('protocolo_id')->references('id')->on('petshop_vet_vacinas')->onDelete('set null');

            $table->unsignedInteger('medico_id')->nullable();
            $table->foreign('medico_id')->references('id')->on('petshop_medicos')->onDelete('set null');

            $table->unsignedInteger('sala_atendimento_id')->nullable();
            $table->foreign('sala_atendimento_id')->references('id')->on('petshop_salas_atendimento')->onDelete('set null');

            $table->unsignedInteger('attendance_id')->nullable();
            $table->foreign('attendance_id')->references('id')->on('petshop_vet_atendimentos')->onDelete('set null');

            $table->string('codigo', 191)->nullable();
            $table->enum('status', [
                'agendado',
                'em_execucao',
                'concluido',
                'cancelado',
                'pendente_validacao',
                'pendente',
                'atrasado',
            ])->default('agendado');

            $table->dateTime('scheduled_at')->nullable();

            $table->unsignedBigInteger('scheduled_by')->nullable();
            $table->foreign('scheduled_by')->references('id')->on('users')->onDelete('set null');

            $table->unsignedSmallInteger('duration_minutes')->nullable();
            $table->json('reminders')->nullable();
            $table->json('checklist')->nullable();

            $table->text('observacoes_planejamento')->nullable();
            $table->text('observacoes_clinicas')->nullable();
            $table->text('observacoes_logistica')->nullable();
            $table->text('instrucoes_tutor')->nullable();

            $table->timestamps();
            $table->softDeletes();
        });
    }

    private function createVacinacaoDoses(): void
    {
        if (Schema::hasTable('petshop_vacinacao_doses')) {
            return;
        }

        Schema::create('petshop_vacinacao_doses', function (Blueprint $table) {
            $table->increments('id');

            $table->unsignedInteger('vacinacao_id');
            $table->foreign('vacinacao_id')->references('id')->on('petshop_vacinacoes')->onDelete('cascade');

            $table->unsignedInteger('vacina_id')->nullable();
            $table->foreign('vacina_id')->references('id')->on('petshop_vet_vacinas')->onDelete('set null');

            $table->unsignedTinyInteger('dose_ordem')->default(1);
            $table->decimal('dose_prevista_ml', 8, 2)->nullable();
            $table->enum('via_aplicacao_prevista', ['subcutanea', 'intramuscular', 'intranasal', 'oral', 'outros'])->nullable();
            $table->smallInteger('reforco_intervalo_dias')->nullable();
            $table->json('alertas')->nullable();

            $table->string('fabricante', 191)->nullable();
            $table->string('lote', 191)->nullable();
            $table->date('validade')->nullable();
            $table->string('dose', 191)->nullable();
            $table->string('via_administracao', 191)->nullable();
            $table->string('local_anatomico', 191)->nullable();
            $table->string('volume', 191)->nullable();
            $table->text('observacoes')->nullable();

            $table->timestamps();
        });
    }

    private function createVacinacaoSessoes(): void
    {
        if (Schema::hasTable('petshop_vacinacao_sessoes')) {
            return;
        }

        Schema::create('petshop_vacinacao_sessoes', function (Blueprint $table) {
            $table->increments('id');

            $table->unsignedInteger('vacinacao_id');
            $table->foreign('vacinacao_id')->references('id')->on('petshop_vacinacoes')->onDelete('cascade');

            $table->string('session_code', 50)->nullable();
            $table->dateTime('inicio_execucao_at')->nullable();
            $table->dateTime('termino_execucao_at')->nullable();

            $table->unsignedBigInteger('responsavel_id')->nullable();
            $table->foreign('responsavel_id')->references('id')->on('users')->onDelete('set null');

            $table->json('assistentes_ids')->nullable();
            $table->string('status', 30)->nullable();

            $table->text('observacoes_execucao')->nullable();
            $table->string('assinatura_tutor_path', 191)->nullable();

            $table->timestamps();
        });
    }

    private function createVacinacaoSessaoDoses(): void
    {
        if (Schema::hasTable('petshop_vacinacao_sessao_doses')) {
            return;
        }

        Schema::create('petshop_vacinacao_sessao_doses', function (Blueprint $table) {
            $table->increments('id');

            $table->unsignedInteger('sessao_id');
            $table->foreign('sessao_id')->references('id')->on('petshop_vacinacao_sessoes')->onDelete('cascade');

            $table->unsignedInteger('dose_planejada_id')->nullable();
            $table->foreign('dose_planejada_id')->references('id')->on('petshop_vacinacao_doses')->onDelete('set null');

            $table->dateTime('aplicada_em')->nullable();

            $table->unsignedBigInteger('responsavel_id')->nullable();
            $table->foreign('responsavel_id')->references('id')->on('users')->onDelete('set null');

            $table->unsignedInteger('lote_id')->nullable();
            $table->decimal('quantidade_ml', 10, 2)->nullable();
            $table->string('via_aplicacao', 50)->nullable();
            $table->string('local_anatomico', 191)->nullable();
            $table->decimal('temperatura_pet', 10, 1)->nullable();
            $table->text('observacoes')->nullable();
            $table->string('resultado', 30)->nullable();
            $table->string('motivo_nao_aplicacao', 191)->nullable();

            $table->timestamps();
        });
    }

    private function createVacinacaoEventos(): void
    {
        if (Schema::hasTable('petshop_vacinacao_eventos')) {
            return;
        }

        Schema::create('petshop_vacinacao_eventos', function (Blueprint $table) {
            $table->increments('id');

            $table->unsignedInteger('vacinacao_id');
            $table->foreign('vacinacao_id')->references('id')->on('petshop_vacinacoes')->onDelete('cascade');

            $table->string('tipo', 50)->nullable();
            $table->json('payload')->nullable();

            $table->unsignedBigInteger('registrado_por')->nullable();
            $table->foreign('registrado_por')->references('id')->on('users')->onDelete('set null');

            $table->dateTime('registrado_em')->nullable();

            $table->timestamps();
        });
    }
};

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
        if (Schema::hasTable('petshop_vet_atendimento_anexos')) {
            return;
        }

        Schema::create('petshop_vet_atendimento_anexos', function (Blueprint $table) {
            $table->increments('id');

            $table->unsignedInteger('atendimento_id');

            $table->string('name', 191);
            $table->string('path', 191);
            $table->string('url', 191)->nullable();
            $table->string('extension', 20)->nullable();
            $table->string('mime_type', 191)->nullable();
            $table->unsignedBigInteger('size_in_bytes')->nullable();
            $table->dateTime('uploaded_at')->nullable();
            $table->unsignedBigInteger('uploaded_by')->nullable();

            $table->timestamps();
        });

        $this->addForeignKeys();
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('petshop_vet_atendimento_anexos');
    }

    private function addForeignKeys(): void
    {
        $supportsForeignKeys = null;

        try {
            $supportsForeignKeys = DB::selectOne("SELECT @@foreign_key_checks as value")->value ?? null;
        } catch (\Throwable $exception) {
            $supportsForeignKeys = null;
        }

        if ($supportsForeignKeys === null) {
            return;
        }

        Schema::table('petshop_vet_atendimento_anexos', function (Blueprint $table) {
            $table->foreign('atendimento_id')
                ->references('id')
                ->on('petshop_vet_atendimentos')
                ->onDelete('cascade');

            $table->foreign('uploaded_by')
                ->references('id')
                ->on('users')
                ->onDelete('set null');
        });
    }
};

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('conta_recebers', function (Blueprint $table) {
            if (!Schema::hasColumn('conta_recebers', 'hotel_id')) {
                $table->unsignedInteger('hotel_id')->nullable()->after('venda_caixa_id');
                $table->index(['hotel_id']);
            }

            if (!Schema::hasColumn('conta_recebers', 'creche_id')) {
                $table->unsignedInteger('creche_id')->nullable()->after('hotel_id');
                $table->index(['creche_id']);
            }

            if (!Schema::hasColumn('conta_recebers', 'estetica_id')) {
                $table->unsignedInteger('estetica_id')->nullable()->after('creche_id');
                $table->index(['estetica_id']);
            }
        });
    }

    public function down()
    {
        Schema::table('conta_recebers', function (Blueprint $table) {
            if (Schema::hasColumn('conta_recebers', 'estetica_id')) {
                $table->dropIndex(['estetica_id']);
                $table->dropColumn('estetica_id');
            }

            if (Schema::hasColumn('conta_recebers', 'creche_id')) {
                $table->dropIndex(['creche_id']);
                $table->dropColumn('creche_id');
            }

            if (Schema::hasColumn('conta_recebers', 'hotel_id')) {
                $table->dropIndex(['hotel_id']);
                $table->dropColumn('hotel_id');
            }
        });
    }
};


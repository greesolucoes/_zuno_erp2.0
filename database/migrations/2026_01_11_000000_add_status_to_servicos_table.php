<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddStatusToServicosTable extends Migration
{
    public function up()
    {
        Schema::table('servicos', function (Blueprint $table) {
            if (!Schema::hasColumn('servicos', 'status')) {
                $table->tinyInteger('status')->default(1)->after('empresa_id');
            }
        });
    }

    public function down()
    {
        Schema::table('servicos', function (Blueprint $table) {
            if (Schema::hasColumn('servicos', 'status')) {
                $table->dropColumn('status');
            }
        });
    }
}


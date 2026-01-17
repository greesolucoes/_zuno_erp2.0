<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasColumn('empresas', 'termo_garantia_os')) {
            return;
        }

        DB::table('empresas')
            ->whereNull('termo_garantia_os')
            ->update([
                'termo_garantia_os' => '<b>CONDIÇÕES GERAIS DE FORNECIMENTO E GARANTIA</b><br />
A garantia será somente nos serviços executados descritos acima, no prazo de 90 dias. A garantia será efetuada
somente nesta oficina. Não cobrimos despesas com fretes, danos por transportes e por terceiros ou uso inadequado
do equipamento, peças externas e motor.<br />Favor conferir a mercadoria no ato da entrega.<br />Obrigado pela
preferência!',
            ]);
    }

    public function down(): void
    {
        if (!Schema::hasColumn('empresas', 'termo_garantia_os')) {
            return;
        }

        DB::table('empresas')->update([
            'termo_garantia_os' => null,
        ]);
    }
};


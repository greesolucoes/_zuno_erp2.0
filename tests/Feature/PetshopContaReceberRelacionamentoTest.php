<?php

namespace Tests\Feature;

use App\Models\ContaReceber;
use App\Models\Petshop\Creche;
use App\Models\Petshop\Estetica;
use App\Models\Petshop\Hotel;
use Tests\TestCase;

class PetshopContaReceberRelacionamentoTest extends TestCase
{
    public function test_relacoes_conta_receber_usam_fks_petshop()
    {
        $hotel = new Hotel();
        $this->assertSame('hotel_id', $hotel->contaReceber()->getForeignKeyName());
        $this->assertSame('id', $hotel->contaReceber()->getLocalKeyName());

        $creche = new Creche();
        $this->assertSame('creche_id', $creche->contaReceber()->getForeignKeyName());
        $this->assertSame('id', $creche->contaReceber()->getLocalKeyName());

        $estetica = new Estetica();
        $this->assertSame('estetica_id', $estetica->contaReceber()->getForeignKeyName());
        $this->assertSame('id', $estetica->contaReceber()->getLocalKeyName());
    }

    public function test_conta_receber_aceita_vinculos_petshop()
    {
        $contaReceber = new ContaReceber();

        $this->assertContains('hotel_id', $contaReceber->getFillable());
        $this->assertContains('creche_id', $contaReceber->getFillable());
        $this->assertContains('estetica_id', $contaReceber->getFillable());

        $casts = $contaReceber->getCasts();
        $this->assertSame('integer', $casts['hotel_id']);
        $this->assertSame('integer', $casts['creche_id']);
        $this->assertSame('integer', $casts['estetica_id']);
    }
}


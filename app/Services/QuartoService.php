<?php

namespace App\Services;

use App\Models\Petshop\Hotel;
use App\Models\Petshop\Quarto;

class QuartoService
{
    /**
     * Varefica se o quarto está lotado no horário da reserva passado 
     * 
     * @param object $data Informações do quarto e do periódo de reserva a ser validado
    */
    public function checkIfQuartoIsBusy($data)
    {
        $reservas_ativas = Hotel::where('quarto_id', $data->quarto_id)
        ->where('empresa_id', $data->empresa_id)
        ->when(!empty($data->reserva_id), fn($q) => $q->where('id', '!=', $data->reserva_id))
        ->where('checkin', '<', $data->checkout)
        ->where('checkout', '>', $data->checkin)
        ->count();

        $quarto = Quarto::find($data->quarto_id);

        return $reservas_ativas >= $quarto->capacidade;
    }
}
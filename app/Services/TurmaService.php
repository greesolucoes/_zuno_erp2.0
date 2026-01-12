<?php

namespace App\Services;

use App\Models\Petshop\Creche;
use App\Models\Petshop\Turma;

class TurmaService
{
    /**
     * Varefica se a turma está lotado no horário da reserva passado 
     * 
     * @param object $data Informações da turma e do periódo de reserva a ser validado
    */
    public function checkIfTurmaIsBusy($data)
    {
        $reservas_ativas = Creche::where('turma_id', $data->turma_id)
        ->where('empresa_id', $data->empresa_id)
        ->when(!empty($data->reserva_id), fn($q) => $q->where('id', '!=', $data->reserva_id))
        ->where('data_entrada', '<', $data->data_saida)
        ->where('data_saida', '>', $data->data_entrada)
        ->count();

        $turma = Turma::find($data->turma_id);

        return $reservas_ativas >= $turma->capacidade;
    }
}
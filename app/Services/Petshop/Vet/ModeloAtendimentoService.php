<?php

namespace App\Services\Petshop\Vet;

class ModeloAtendimentoService
{
    public function getDefaultTemplates(): object
    {
        $templates = config('vet_modelos_atendimento_padrao');

        return collect($templates)->map(function ($template)  {
            return [
                'title'        => $template['title'],
                'notes'        => $template['notes'],
                'category'     => $template['category'],
                'status'       => $template['status'],
                'content'      => $template['content'],
                'icon'         => $template['icon']
            ];
        });
    }
}
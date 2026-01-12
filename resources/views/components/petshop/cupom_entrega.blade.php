<!DOCTYPE html>
<html>
    <head>
        <style type="text/css">
            * {
                font-family: "Montserrat", sans-serif;
            }

            body {
                margin: -20px;
                margin-top: -30px;
            }
        </style>

        <link rel="preconnect" href="https://fonts.googleapis.com">
        <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
        <link href="https://fonts.googleapis.com/css2?family=Montserrat:ital,wght@0,100..900;1,100..900&display=swap" rel="stylesheet">
    </head>
    <body>
        <h5 style="text-align: center;">
            Endereço do Cliente
        </h5>
        
        <div style="font-size: 11px; font-weight: 600">
            <b>Endereço:</b>
            {{ $item->bairro }}, {{ $item->rua }} {{ $item->numero }}<br>
            {{ $item->cidade->nome }}{{ isset($item->cep) ? ', ' . $item->cep : '' }}
        </div>

        <div style="font-size: 11px; font-weight: 500; margin-top: 10px">
            <b>Cliente:</b>
            {{ $item->cliente->razao_social }}
        </div>

        <div style="font-size: 11px; font-weight: 500; margin-top: 10px">
            <b>Contato:</b>
            @if (isset($item->cliente->telefone))
                {{ $item->cliente->telefone ?? '--'}}
            @elseif (isset($item->cliente->telefone_secundario))
                {{ $item->cliente->telefone_secundario ?? '--'}}
            @elseif (isset($item->cliente->telefone_terciario))
                {{ $item->cliente->telefone_terciario ?? '--'}}
            @else
                --
            @endif
        </div>

        <h5 style="text-align: center">
            Informações do Pet
        </h5>

        <div style="font-size: 11px; font-weight: 500; margin-top: 3px">
            <b>Nome do pet:</b>
            {{ $agendamento->animal->nome ?? '--' }}
        </div>

        <div style="font-size: 11px; font-weight: 500; margin-top: 3px">
            <b>Espécie:</b>
            {{ $agendamento->animal->especie->nome ?? '--'}}
        </div>

        <div style="font-size: 11px; font-weight: 500; margin-top: 3px">
            <b>Raça:</b>
            {{ $agendamento->animal->raca->nome ?? '--' }}
        </div>

        <div style="font-size: 11px; font-weight: 500; margin-top: 3px">
            <b>Porte:</b>
            {{ $agendamento->animal->porte ?? '--' }}
        </div>

        <div style="font-size: 11px; font-weight: 500; margin-top: 3px">
            <b>Peso:</b>
            {{ $agendamento->animal->peso ?? '--' }}
        </div>

        <div style="font-size: 11px; font-weight: 500; margin-top: 3px">
            <b>Observações:</b>
            {{ $agendamento->animal->observacoes ?? '--' }}
        </div>
    </body>

</html>
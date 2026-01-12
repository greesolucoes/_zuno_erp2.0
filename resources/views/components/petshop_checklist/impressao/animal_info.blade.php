<table class="section-title" style="margin-top: -15px">
    <tr>
        <td>
            <strong>TUTOR</strong>
        </td>
    </tr>
</table>

<div class="impression-data">
    <div class="data-left-column">
        <div>
            <strong>Nome:</strong> {{isset( $animal->cliente->razao_social) ? $animal->cliente->razao_social : '--' }}
        </div>
        <div>
            <strong>CPF/CNPJ:</strong> {{ isset($animal->cliente->cpf_cnpj) ? $animal->cliente->cpf_cnpj : '--' }}
        </div>
        <div>
            <strong>Telefone:</strong> {{ isset($animal->cliente->telefone) ? $animal->cliente->telefone : '--' }}
        </div>
    </div>

    <div class="data-right-column">
        <div >
            <strong> Endereço:</strong>
                @if (isset($animal->cliente))
                    {{ $animal->cliente->rua }},
                    {{ $animal->cliente->numero }}
                    - {{ $animal->cliente->bairro }}
                    - {{ $animal->cliente->cidade->nome ?? '--' }}
                    ({{ $animal->cliente->cidade->uf ?? '--' }})
                @else
                    --
                @endif
        </div>

        @if (isset($animal->cliente->complemento) && $animal->cliente->complemento != '')
            <div>
                <strong>Complemento:</strong> {{ $animal->cliente->complemento }}
            </div>
        @endif

        <div style="line-height: 1.5">
            <strong>CEP:</strong> {{ isset($animal->cliente->cep) ? $animal->cliente->cep : '--' }}
        </div>
    </div>
</div>

<table class="section-title">
    <tr>
        <td>
            <strong>PET</strong>
        </td>
    </tr>
</table>

<div class="impression-data">
    <div class="data-left-column">
        <div>
            <strong>Nome:</strong> {{isset( $animal->nome) ? $animal->nome : '--' }}
        </div>
        <div>
            <strong>Sexo:</strong>
            @isset($sexo)
                @if ($sexo == 'M')
                    Macho
                @else
                    Fêmea
                @endif
            @endisset
        </div>
        <div>
            <strong>Espécie:</strong> {{ isset($animal->especie->nome) ? $animal->especie->nome : '--' }}
        </div>
    </div>

    <div class="data-right-column">
        <div >
            <strong> Raça:</strong> {{ isset($animal->raca->nome) ? $animal->raca->nome : '--' }}
        </div>
        <div >
            <strong> Peso:</strong> {{ isset($animal->peso) ? $animal->peso : '--' }}
        </div>

        @if (isset($animal->observacao) && $animal->observacao != '')
            <div>
                <strong>Observações:</strong> {{ $animal->observacao }}
            </div>
        @endif
    </div>
</div>
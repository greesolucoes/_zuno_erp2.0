@php
    use Carbon\Carbon;
@endphp


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
            <strong>Nome:</strong> {{isset( $ordem->animal->nome) ? $ordem->animal->nome : '--' }}
        </div>
        <div>
            <strong>Espécie:</strong> {{ isset($ordem->animal->especie->nome) ? $ordem->animal->especie->nome : '--' }}
        </div>
        <div>
            <strong> Raça:</strong> {{ isset($ordem->animal->raca->nome) ? $ordem->animal->raca->nome : '--' }}
        </div>
        <div>
            <strong> Pelagem:</strong> {{ isset($ordem->animal->pelagem->nome) ? $ordem->animal->pelagem->nome : '--' }}
        </div>
        <div>
            <strong> Cor:</strong> {{ isset($ordem->animal->cor) ? $ordem->animal->cor : '--' }}
        </div>
        <div>
            <strong>Sexo:</strong>
            @isset($ordem->animal->sexo)
                @if ($ordem->animal->sexo == 'M')
                    Macho
                @else
                    Fêmea
                @endif
            @endisset
        </div>
    </div>

    <div class="data-right-column">
        <div>
            <strong> Porte:</strong> {{ isset($ordem->animal->porte) ? $ordem->animal->porte : '--' }}
        </div>
        <div>
            <strong> Peso:</strong> {{ isset($ordem->animal->peso) ? $ordem->animal->peso : '--' }}
        </div>
        <div>
            <strong> Idade:</strong>
            {{ 
                isset($ordem?->animal->data_nascimento) ?
                    (
                        $ordem->animal->data_nascimento ?
                            Carbon::parse($ordem->animal->data_nascimento)->age . 
                            ' ano' . (Carbon::parse($ordem->animal->data_nascimento)->age > 1 ? 's' : '')  
                        : '--'
                    ) 
                :
                    '--'
            }}
        </div> 
        <div>
            <strong>Possui pedigree: </strong>
            {{ isset($ordem->animal->tem_pedigree) && $ordem->animal->tem_pedigree ? 'Sim' : 'Não' }}
        </div>
        @if (isset($ordem?->animal->tem_pedigree) && isset($ordem->animal->pedigree) && $ordem->animal->tem_pedigree)
            <div>
                <strong>Pedigree:</strong> {{ $ordem->animal->pedigree }}
            </div>
        @endif

        @if (isset($ordem->animal->observacao) && $ordem->animal->observacao != '')
            <div>
                <strong>Observações:</strong> {{ $ordem->animal->observacao }}
            </div>
        @endif
    </div>
</div>
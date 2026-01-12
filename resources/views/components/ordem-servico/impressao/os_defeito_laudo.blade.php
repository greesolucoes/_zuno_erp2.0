<table style="margin-bottom: 20px; width:fit-content;">
        @if (isset($ordem->defeito) && $ordem->defeito != '')
        <tr>
            <td class="half-text-block">
                <p>
                    <strong>
                        @if (__isSegmento(Auth::user()->empresa, 'PRO')) 
                            Reclamação
                        @else
                            Defeito
                        @endif
                    </strong>
                </p>
                <span style="font-size: 7px">{!! $ordem->defeito !!}</span>
            </td>
        </tr>
        @endif
        @if (isset($ordem->laudo) && $ordem->laudo != '')
            <tr>
                <td class="half-text-block">
                <p>
                    <strong>
                        @if (__isSegmento(Auth::user()->empresa, 'PRO')) 
                            Causa
                        @else
                            Laudo
                        @endif
                    </strong>
                </p>
                <span style="font-size: 7px">{!! $ordem->laudo !!}</span>
            </td>
            </tr>
        @endif
        @if (isset($ordem->servico_realizado) && $ordem->servico_realizado != '')
            <tr>
                <td class="half-text-block">
                <p><strong>Serviço Realizado</strong></p>
                <span style="font-size: 7px">{!! $ordem->servico_realizado !!}</span>
            </td>
            </tr>
        @endif
</table>

<header>
    <table class="header-title-container">
        <tr>
            <td width="33%">&nbsp;</td>

            <td width="35%" class="header-title">
                <h2>{{$title}}</h2>
            </td>

            <td width="33%" class="header-emition">
                <small>
                    <div>
                        Emitido em {{ date('d/m/Y - H:i') }}
                    </div>
                    <div>
                        Página: <span class="page-number">
                    </div>
                </small>
            </td>
        </tr>
    </table>
    <table>
        <tr>
            <td width="25%">
                @if ($config->logo != null)
                    <img
                        src="{{ 'data:image/png;base64,' . base64_encode(file_get_contents(@__customPath('/uploads/logos/' . $config->logo))) }}"
                        alt="Logo"
                        class="logo">
                @else
                    <img
                        src="{{ 'data:image/png;base64,' . base64_encode(file_get_contents(@public_path('logo.png'))) }}"
                        alt="Logo"
                        class="logo"
                    >
                @endif
            </td>
            <td class="header-info">
                <div class="header-store-name"><strong>{{ $config->nome }}</strong></div>
                <div>
                    <strong>CNPJ:</strong> {{ $config->cpf_cnpj }}
                </div>
                <div>
                    <strong>E-mail:</strong> {{ $config->email ?? '---' }} <strong>| Fone:</strong> {{ $config->celular }}
                </div>
                <div>
                    <strong>Endereço:</strong> {{ $config->rua }} - {{ $config->numero }}, {{ $config->bairro }}
                </div>
                <div>
                    <strong>Cidade/UF:</strong> {{ $config->cidade->nome }} - ({{ $config->cidade->uf }})
                </div>
            </td>
        </tr>
    </table>
</header>

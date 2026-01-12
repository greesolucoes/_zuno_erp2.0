<tr>
    {{-- Ações -----------------------------------------------------------}}
    <td class="text-center">
        <form id="form-{{ $cx->id }}"
              class="d-flex align-items-center gap-1"
              action="{{ route('caixa-fisico.destroy', $cx->id) }}"
              method="POST">
            @csrf @method('DELETE')

            {{-- Excluir --}}
            <button type="button"
                    class="border-0 bg-transparent p-0 btn-delete"
                    title="Excluir Caixa">
                <img src="/assets/images/svg/icone excluir.svg" width="26" height="26" alt="Excluir">
            </button>

            {{-- Editar --}}
            <a  href="{{ route('caixa-fisico.edit', $cx->id) }}"
                class="border-0 bg-transparent p-0"
                title="Editar Caixa">
                <img src="/assets/images/svg/icone editar nfe.svg" width="26" height="26" alt="Editar">
            </a>

            {{-- Visualizar --}}
            <button
                type="button"
                title="Visualilizar Dados do Caixa"
                class="border-0 mx-1 p-0 bg-transparent text-color-back"
                data-bs-toggle="modal"
                data-bs-target="#modal_view_caixa_fisico-{{ $cx->id }}"
                data-id="{{$cx->id}}"
            >
                <img
                    height="26"
                    width="26"
                    src="/assets/images/svg/icone visualizacao.svg"
                    alt="Visualilizar Caixa"
                >
            </button>
        </form>
    </td>

    {{-- # ----------------------------------------------------------------}}
    <td class="text-center align-middle">{{ $cx->id }}</td>

    {{-- Descrição --------------------------------------------------------}}
    <td class="text-center">{{ $cx->descricao }}</td>

    {{-- Filial -----------------------------------------------------------}}


    {{-- Status -----------------------------------------------------------}}
    <td class="text-center w-max-min-content">
        <span
            title="Clique para alterar para permitir/desabilitar o acesso"
            class="status-dot {{ $cx->ativo == '1' ? 'on' : 'off' }} pointer dot-{{ $cx->id }}"
            data-id="{{ $cx->id }}"
            data-status={{$cx->ativo}}
        >
        </span>
    </td>

    {{-- Cadastro ---------------------------------------------------------}}
    <td class="">
        <b class="m-0 p-0">Cadastro</b><br>
        {{ __data_pt($cx->created_at) }}
    </td>
</tr>

@include('modals._view_caixa_fisico')

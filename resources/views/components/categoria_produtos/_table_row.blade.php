<tr>
    <td class="text-center">
        @if ($item->status_global == 0)
            <form class="d-flex align-items-center gap-1"
                  action="{{ route('categoria-produtos.destroy', $item->id) }}"
                  method="post" id="form-{{ $item->id }}">
                @method('delete')
                @csrf
                @can('categoria_produtos_delete')
                    <button
                        type="button"
                        class="border-0 m-0 p-0 bg-transparent text-color-back btn-delete"
                        title="Excluir Cliente"
                    >
                        <img
                            height="26"
                            width="26"
                            src="/assets/images/svg/icone excluir.svg"
                            alt="Excluir Cliente"
                        >
                    </button>
                @endcan
                @can('categoria_produtos_edit')
                    <a class="border-0 m-0 p-0 bg-transparent text-color-back"
                       href="{{ route('categoria-produtos.edit', [$item->id, 'page' => request()->query('page', 1)]) }}">
                        <img
                                height="26"
                                width="26"
                                src="/assets/images/svg/icone editar nfe.svg"
                                alt="Editar Categoria"
                        >
                    </a>
                @endcan
            </form>
        @endif
    </td>
    <td class="text-center"></td>
    <td class="text-center">{{ $item->nome }}</td>

    @if(__isActivePlan(Auth::user()->empresa, 'Cardapio'))
        <td class="text-center">
            <i class="{{ $item->cardapio ? 'ri-checkbox-circle-fill text-success' : 'ri-close-circle-fill text-danger' }}"></i>
        </td>
    @endif

    @if(__isActivePlan(Auth::user()->empresa, 'Delivery'))
        <td class="text-center">
            <i class="{{ $item->delivery ? 'ri-checkbox-circle-fill text-success' : 'ri-close-circle-fill text-danger' }}"></i>
        </td>
        <td class="text-center">
            <i class="{{ $item->tipo_pizza ? 'ri-checkbox-circle-fill text-success' : 'ri-close-circle-fill text-danger' }}"></i>
        </td>
    @endif

    @if(__isActivePlan(Auth::user()->empresa, 'Ecommerce'))
        <td class="text-center">
            <i class="{{ $item->ecommerce ? 'ri-checkbox-circle-fill text-success' : 'ri-close-circle-fill text-danger' }}"></i>
        </td>
    @endif

    @if(__isActivePlan(Auth::user()->empresa, 'Reservas'))
        <td class="text-center">
            <i class="{{ $item->reserva ? 'ri-checkbox-circle-fill text-success' : 'ri-close-circle-fill text-danger' }}"></i>
        </td>
    @endif
</tr>

@props([
    'table_headers' => [],
    'data' => [],
    'has_actions' => true,
    'modal_actions' => true,
    'sum' => null,
    'sum_label' => 'Soma:',
    'pagination' => true,
    'back_action' => false
])

<div class="page-content">
    <div class="card">
        <div class="card-body p-4">
            <div class="page-breadcrumb d-sm-flex align-items-center mb-3">
                @if ($back_action)
                    <a href="{{ $back_action }}" class="btn btn-danger btn-sm">
                        <i class="bx bx-arrow-back"></i> Voltar
                    </a>
                @endif

                <div class="ms-auto">
                    {{ $buttons ?? '' }}
                </div>
            </div>

            <div class="col">
                <h6 class="mb-0 text-uppercase">{{ $title ?? '' }}</h6>

                @if (isset($search_form))
                    {{ $search_form }}
                @endif

                <hr/>

                <div class="card">
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table mb-0 table-striped">
                                <thead>
                                    <tr>
                                        @foreach ($table_headers as $header)
                                            <th
                                                @if (!empty($header['width'])) style="width: {{ $header['width'] }}" @endif
                                                class="text-{{ $header['align'] ?? 'center' }}"
                                            >
                                                {{ $header['label'] }}
                                            </th>
                                        @endforeach
                                        @if ($has_actions)
                                            <th>Ações</th>
                                        @endif
                                    </tr>
                                </thead>
                                <tbody>
                                    @if (isset($data) && sizeof($data) > 0)
                                        {{ $slot }}
                                    @else
                                        <tr>
                                            <td colspan="{{ count($table_headers) + ($has_actions ? 1 : 0) }}" class="text-center">
                                                Nada encontrado
                                            </td>
                                        </tr>
                                    @endif
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            @if ($pagination == true)
                {!! $data->appends(request()->all())->links() !!}
            @endif

            @if ($sum)
                <h5 class="mt-3 text-right">{{ $sum_label }} <strong class="text-green">R$ {{ $sum }}</strong></h5>
            @endif
        </div>
    </div>
</div>

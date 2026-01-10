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

<div class="new-colors row">
    <div class="card">
        <div class="card-body">
            @if (isset($title) || isset($buttons))
                @if ($back_action)
                    <div class="d-flex align-items-center justify-content-between mb-5">
                        <h3 class="text-gold bold">{{$title}}</h3>
                        <a 
                            href="{{ $back_action }}" 
                            class="btn btn-danger btn-sm d-flex align-items-center gap-1 px-2"
                            style="background: #7b0a42 !important"
                        >
                            <i class="ri-arrow-left-double-fill"></i>Voltar
                        </a>
                    </div>
                @else
                    <div class="mb-5">
                        <h3 class="text-gold bold">{{$title}}</h3>
                        <p class="mb-0 text-muted">{{ $description ?? '' }}</p>
                    </div>
                @endif
                <div class="col-auto ms-auto gap-3 text-right mb-2">
                    {{ $buttons ?? '' }}
                </div>
            @endif
            <div class="mt-3 mb-5 col-lg-12">
                @if(isset($search_form))
                    <div class="mt-3 col-lg-12">
                        {{ $search_form }}
                    </div>
                @endif
            </div>

            <div class="col-md-12 mt-3">
                <div class="table-responsive">
                    <table class="table table-striped table-centered mb-0">
                        <thead class="table-dark" style="font-size: 14px !important">
                            <tr>
                                @if ($has_actions == true)
                                    <th 
                                        style="padding-left: 36px"
                                        width="10%" 
                                        scope="col"
                                    >
                                        Ações
                                    </th>
                                    @if($modal_actions)
                                        <th width="0.1%"></th>
                                    @endif
                                @endif
                                @foreach ($table_headers as $header)
                                    <th scope="col"  class="text-{{ $header['align'] ?? 'center' }}" width="{{ $header['width'] }}">
                                        {{ $header['label'] }}
                                    </th>
                                @endforeach
                            </tr>
                        </thead>
                        <tbody style="font-size: 14px !important">
                            @if (isset($data) && sizeof($data) > 0)
                                {{ $slot }}
                            @else
                                <tr>
                                    <td colspan="{{ count($table_headers) + 2 }}"
                                        class="text-center">Nenhum registro encontrado...
                                    </td>
                                </tr>
                            @endif
                        </tbody>
                    </table>
                </div>
                @if ($pagination == true)
                    <div class="my-3">
                    {!! $data->appends(request()->all())->links() !!}
                    </div>
                @endif
            </div>
            @if($sum)
                <h5 class="mt-3 text-right">{{ $sum_label }} <strong class="text-green">R$ {{ $sum }}</strong></h5>
            @endif
        </div>
    </div>
</div>

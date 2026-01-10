@extends('layouts.app', ['title' => 'Modelo de Prescrição'])

@section('content')
    <div class="card">
        <div class="card-header d-flex flex-wrap align-items-center justify-content-between gap-3">
            <div>
                <h3 class="text-color mb-1">{{ $detalhesModelo['title'] ?? 'Modelo sem título' }}</h3>
                <div class="d-flex flex-wrap align-items-center gap-2 small">
                    <span class="badge bg-primary">{{ $detalhesModelo['category_label'] ?? 'Categoria não informada' }}</span>
                    <span class="{{ $detalhesModelo['status_class'] ?? 'badge bg-light text-dark' }}">{{ $detalhesModelo['status_label'] ?? '—' }}</span>
                    @if (! empty($detalhesModelo['updated_at']))
                        <span class="text-muted">Atualizado em {{ $detalhesModelo['updated_at'] }}</span>
                    @endif
                </div>
            </div>
            <div class="d-flex flex-wrap gap-2 new-colors">
                <a
                    href="{{ route('vet.prescription-models.index', ['page' => request()->query('page', 1)]) }}"
                    class="btn btn-secondary btn-sm d-flex align-items-center gap-1 px-3"
                >
                    <i class="ri-arrow-left-double-fill"></i>
                    Voltar
                </a>
                <a
                    href="{{ route('vet.prescription-models.edit', ['modeloPrescricao' => $modelo->id, 'page' => request()->query('page', 1)]) }}"
                    class="btn btn-success btn-sm d-flex align-items-center gap-1 px-3"
                >
                    <i class="ri-edit-line"></i>
                    Editar
                </a>
            </div>
        </div>
        <div class="card-body">
            <div class="mb-4">
                <h5 class="text-color">Informações gerais</h5>
                <dl class="row mb-0">
                    <dt class="col-md-3 text-muted">Título do modelo</dt>
                    <dd class="col-md-9">{{ $detalhesModelo['title'] ?? 'Modelo sem título' }}</dd>

                    <dt class="col-md-3 text-muted">Categoria</dt>
                    <dd class="col-md-9">{{ $detalhesModelo['category_label'] ?? 'Categoria não informada' }}</dd>

                    <dt class="col-md-3 text-muted">Status</dt>
                    <dd class="col-md-9">{{ $detalhesModelo['status_label'] ?? '—' }}</dd>

                    <dt class="col-md-3 text-muted">Criado em</dt>
                    <dd class="col-md-9">{{ $detalhesModelo['created_at'] ?? '—' }}</dd>

                    <dt class="col-md-3 text-muted">Última atualização</dt>
                    <dd class="col-md-9">{{ $detalhesModelo['updated_at'] ?? '—' }}</dd>

                    @if (! empty($detalhesModelo['notes']))
                        <dt class="col-md-3 text-muted">Observações</dt>
                        <dd class="col-md-9">{!! nl2br(e($detalhesModelo['notes'])) !!}</dd>
                    @endif
                </dl>
            </div>

            <div>
                <div class="d-flex align-items-center justify-content-between flex-wrap gap-2 mb-3">
                    <h5 class="text-color mb-0">Campos configurados</h5>
                    <span class="badge bg-info p-1 text-white fw-semibold">{{ count($detalhesModelo['fields'] ?? []) }} campo(s)</span>
                </div>

                @forelse ($detalhesModelo['fields'] ?? [] as $campo)
                    <div class="border rounded-3 p-4 mb-3 shadow-sm">
                        <div class="d-flex align-items-start justify-content-between gap-3 flex-wrap">
                            <div>
                                <h6 class="mb-1 text-color">{{ $campo['label'] ?? 'Campo sem título' }}</h6>
                                <span class="badge bg-light text-dark">{{ $campo['type_label'] ?? ($campo['type'] ?? 'Tipo não informado') }}</span>
                            </div>
                        </div>

                        @if (! empty($campo['configuracoes']))
                            <div class="mt-3">
                                <h6 class="text-muted text-uppercase small mb-2">Configurações</h6>
                                <ul class="list-unstyled mb-0">
                                    @foreach ($campo['configuracoes'] as $config)
                                        <li class="mb-2">
                                            <span class="fw-semibold">{{ $config['label'] }}:</span>
                                            @if (! empty($config['is_html']))
                                                <div class="mt-1">{!! $config['value'] !!}</div>
                                            @else
                                                <span>{{ $config['value'] }}</span>
                                            @endif
                                        </li>
                                    @endforeach
                                </ul>
                            </div>
                        @else
                            <p class="text-muted mt-3 mb-0">Nenhuma configuração adicional para este campo.</p>
                        @endif
                    </div>
                @empty
                    <div class="border rounded-3 p-4 text-center text-muted">
                        Nenhum campo configurado para este modelo.
                    </div>
                @endforelse
            </div>
        </div>
    </div>
@endsection
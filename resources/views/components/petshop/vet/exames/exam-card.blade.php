@props([
    'exam',
])

@php
    $badgeMap = [
        'success' => 'badge-soft-success',
        'warning' => 'badge-soft-warning',
        'info' => 'badge-soft-info',
        'danger' => 'badge-soft-danger',
    ];

    $badgeClass = $badgeMap[$exam['status_badge'] ?? 'info'] ?? 'badge-soft-info';
@endphp

<div class="vet-exams__card shadow-sm">
    <div class="d-flex justify-content-between align-items-start mb-3">
        <div>
            <h5 class="mb-1 text-dark">{{ $exam['type'] }}</h5>
            <p class="mb-0 text-muted small">Solicitado por {{ $exam['veterinarian'] }} · {{ $exam['requested_at'] }}</p>
        </div>
        <span class="badge {{ $badgeClass }}">{{ $exam['status'] }}</span>
    </div>

    <div class="row gy-3">
        <div class="col-md-4">
            <div class="vet-exams__label text-muted">Paciente</div>
            <div class="fw-semibold">{{ $exam['animal'] }}</div>
            <small class="text-muted">Tutor: {{ $exam['guardian'] }}</small>
        </div>
        <div class="col-md-4">
            <div class="vet-exams__label text-muted">Laboratório</div>
            <div class="fw-semibold">{{ $exam['laboratory'] }}</div>
            <small class="text-muted">Conclusão: {{ $exam['completed_at'] ? $exam['completed_at'] : 'Em andamento' }}</small>
        </div>
        <div class="col-md-4">
            <div class="vet-exams__label text-muted">Observações</div>
            <p class="mb-0 small">{{ $exam['findings'] }}</p>
        </div>
    </div>

    <div class="d-flex flex-wrap align-items-center gap-2 mt-4">
        <a href="#" class="btn btn-outline-primary btn-sm">
            <i class="mdi mdi-share-variant me-1"></i> Compartilhar com tutor
        </a>
        <button
            type="button"
            class="btn btn-primary btn-sm"
            data-bs-toggle="modal"
            data-bs-target="#{{ $exam['modal_id'] }}"
        >
            <i class="mdi mdi-file-eye-outline me-1"></i> Ver documentos
        </button>
        <button type="button" class="btn btn-soft-secondary btn-sm">
            <i class="mdi mdi-download-outline me-1"></i> Baixar laudo
        </button>
    </div>
</div>
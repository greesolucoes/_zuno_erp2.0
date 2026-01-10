@props([
    'type',
])

<div class="vet-exams__type-card h-100">
    <div class="vet-exams__type-badge mb-3">
        <span class="badge bg-primary-subtle text-primary text-uppercase">{{ $type['segment'] }}</span>
    </div>
    <h5 class="mb-1 text-dark">{{ $type['name'] }}</h5>
    <p class="text-muted small mb-2">Código: {{ $type['code'] }}</p>
    <p class="text-muted small">{{ $type['description'] }}</p>
    <div class="mt-4">
        <h6 class="text-muted text-uppercase small fw-bold mb-2">Preparação</h6>
        <p class="small mb-0">{{ $type['preparation'] }}</p>
    </div>
</div>
@extends('default.layout', ['title' => 'Status da internação'])

@php($patient = $internacao->animal)
@php($tutor = $patient?->cliente)
@php($primaryContact = collect([
    $tutor?->telefone,
    $tutor?->telefone_secundario,
    $tutor?->telefone_terciario,
    $tutor?->contato,
    $tutor?->email,
])->first(fn ($value) => filled($value)))

@section('content')
    <div class="card mb-4">
        <div class="card-body">
            <div class="d-flex flex-column flex-lg-row justify-content-between align-items-start gap-4">
                <div>
                    <h4 class="text-color mb-1">{{ $patient?->nome ?? 'Paciente não informado' }}</h4>
                    <div class="text-muted small">
                        {{ $patient?->especie?->nome ?? 'Espécie não informada' }}
                        @if ($patient?->raca?->nome)
                            • {{ $patient->raca->nome }}
                        @endif
                        @if ($patient?->idade)
                            • {{ $patient->idade }} {{ $patient->idade == 1 ? 'ano' : 'anos' }}
                        @endif
                    </div>
                </div>
                <div class="text-lg-end">
                    <div class="fw-semibold text-color">Tutor</div>
                    <div class="text-muted small">
                        {{ $tutor->razao_social ?? $tutor->nome_fantasia ?? $tutor->contato ?? '—' }}
                    </div>
                    <div class="text-muted small">{{ $primaryContact ?? '—' }}</div>
                </div>
            </div>

            <hr class="my-4">

            <div class="row g-3">
                <div class="col-md-3 col-6">
                    <div class="fw-semibold text-color">Status clínico</div>
                    <span class="badge text-bg-{{ $internacao->status_color }}">{{ $internacao->status_label }}</span>
                </div>
                <div class="col-md-3 col-6">
                    <div class="fw-semibold text-color">Risco assistencial</div>
                    <span class="badge text-bg-{{ $internacao->risk_color }}">{{ $internacao->risk_label }}</span>
                </div>
                <div class="col-md-3 col-6">
                    <div class="fw-semibold text-color">Admissão</div>
                    <div class="text-muted small">
                        {{ optional($internacao->internado_em)->format('d/m/Y H:i') ?? '—' }}
                    </div>
                </div>
                <div class="col-md-3 col-6">
                    <div class="fw-semibold text-color">Profissional responsável</div>
                    <div class="text-muted small">
                        {{ optional($internacao->veterinarian?->funcionario)->nome ?? '—' }}
                    </div>
                </div>
            </div>
        </div>
    </div>

    <x-table
        :data="$statuses"
        :table_headers="[
            ['label' => 'Status', 'width' => '10%', 'align' => 'left'],
            ['label' => 'Anotação', 'width' => '15%', 'align' => 'left'],
            ['label' => 'Evolução', 'width' => '10%'],
            ['label' => 'Registrado em', 'width' => '10%', 'align' => 'left'],
            ['label' => 'Atualizado em', 'width' => '10%', 'align' => 'left'],
        ]"
        :modal_actions="false"
    >
        <x-slot name="title" class="text-color">Status da internação</x-slot>

        <x-slot name="buttons">
            <div class="d-flex flex-wrap align-items-center justify-content-end gap-2">
                <a
                    href="{{ route('vet.hospitalizations.status.create', $internacao) }}"
                    class="btn btn-success d-flex align-items-center gap-1"
                >
                    <i class="ri-add-circle-fill"></i>
                    Novo status
                </a>
                <a
                    href="{{ route('vet.hospitalizations.index') }}"
                    class="btn btn-light d-flex align-items-center gap-1"
                >
                    <i class="ri-arrow-left-line"></i>
                    Voltar para internações
                </a>
            </div>
        </x-slot>

        @foreach ($statuses as $statusRecord)
            @include('components.petshop.vet.internacoes.status._table_row', [
                'statusRecord' => $statusRecord,
                'internacao' => $internacao,
            ])
        @endforeach
    </x-table>
@endsection
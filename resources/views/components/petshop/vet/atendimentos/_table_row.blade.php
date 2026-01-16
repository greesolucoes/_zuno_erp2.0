@php
    $page = request()->query('page', 1);
@endphp

<tr>
    <td class="text-start">
        <div class="fw-semibold text-color">{{ $encounter['patient'] ?? '—' }}</div>
        <div class="text-muted small">
            {{ $encounter['species'] ?? '—' }}
            @if(!empty($encounter['tutor']))
                • Tutor: {{ $encounter['tutor'] }}
            @endif
        </div>
        <div class="text-muted small">{{ $encounter['code'] ?? '—' }}</div>
    </td>
    <td class="text-start">
        <div class="fw-semibold text-color">{{ $encounter['veterinarian'] ?? '—' }}</div>
    </td>
    <td class="text-center">
        <div class="fw-semibold text-color">{{ $encounter['service'] ?? '—' }}</div>
        @if(!empty($encounter['room']))
            <div class="text-muted small">{{ $encounter['room'] }}</div>
        @endif
    </td>
    <td>
        <div class="fw-semibold text-color">
            @if(!empty($encounter['start']))
                {{ \Illuminate\Support\Carbon::parse($encounter['start'])->format('d/m/Y') }} <br>
                <small>{{ \Illuminate\Support\Carbon::parse($encounter['start'])->format('H:i') }}</small>
            @else
                —-
            @endif
        </div>
    </td>
    <td class="text-center">
        <span class="badge p-2 fw-semibold bg-{{ $encounter['status_color'] ?? 'primary' }} text-uppercase">
            {{ $encounter['status'] ?? '—' }}
        </span>
    </td>
    <td style="min-width: max-content; width: auto;">
        <div class="d-flex align-items-center gap-2">
            <a
                href="{{ route('vet.atendimentos.history', [$encounter['id'], 'page' => $page]) }}"
                class="btn btn-sm btn-primary d-inline-flex align-items-center gap-2"
                title="Ações do atendimento"
            >
                <i class="bx bx-menu"></i>
                Ações
            </a>

            <form
                action="{{ route('vet.atendimentos.destroy', $encounter['id']) }}"
                method="POST"
                id="form-delete-{{ $encounter['id'] }}"
                class="d-inline"
            >
                @csrf
                @method('delete')
                <button type="button" class="btn btn-sm btn-danger btn-delete" title="Excluir atendimento">
                    <i class="ri-delete-bin-6-line"></i>
                </button>
            </form>
        </div>
    </td>
</tr>

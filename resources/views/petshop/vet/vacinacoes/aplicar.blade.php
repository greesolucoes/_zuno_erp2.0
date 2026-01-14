@extends('default.layout', ['title' => 'Aplicação de Vacinas'])

@section('css')
    <style>
        .vet-vaccination-apply__summary {
            border-radius: 16px;
            border: 1px solid rgba(22, 22, 107, 0.08);
            background: #fff;
            box-shadow: 0 8px 24px rgba(22, 22, 107, 0.08);
        }

        .vet-vaccination-apply__dose-card {
            border-radius: 14px;
            border: 1px solid rgba(22, 22, 107, 0.08);
            background: #fff;
            box-shadow: 0 6px 18px rgba(22, 22, 107, 0.06);
        }

        .vet-vaccination-apply__section-title {
            font-size: 1rem;
            font-weight: 600;
            color: #16166b;
        }

        .vet-vaccination-apply__meta {
            display: flex;
            flex-wrap: wrap;
            gap: .5rem 1rem;
            font-size: .875rem;
            color: #6c757d;
        }

        .vet-vaccination-apply__meta span {
            display: inline-flex;
            align-items: center;
            gap: .25rem;
        }
    </style>
@endsection

@section('content')
<x-form-page
    title="Aplicação de Vacinas"
    :back-url="route('vet.vaccinations.index', ['page' => request()->query('page', 1)])"
>
    <div class="row g-4">
        <div class="col-12">
            <div class="vet-vaccination-apply__summary p-4">
                <div class="d-flex flex-column flex-md-row justify-content-between align-items-start gap-3">
                    <div>
                        <p class="text-muted mb-0">Registre as doses aplicadas para manter o histórico clínico completo do pet.</p>
                    </div>
                    <div class="text-end">
                        <span class="badge bg-{{ $vaccination['status_color'] ?? 'secondary' }} text-uppercase">{{ $vaccination['status'] ?? '—' }}</span>
                        <div class="text-muted small">Código: {{ $vaccination['code'] ?? '—' }}</div>
                    </div>
                </div>

                <hr class="my-4">

                <div class="row g-4">
                    <div class="col-md-4">
                        <h2 class="vet-vaccination-apply__section-title mb-2">Paciente</h2>
                        <p class="mb-1 fw-semibold text-primary">{{ $vaccination['patient'] ?? '—' }}</p>
                        <p class="text-muted mb-0">
                            {{ $vaccination['species'] ?? '—' }}
                            @if(!empty($vaccination['breed']))
                                • {{ $vaccination['breed'] }}
                            @endif
                        </p>
                        @if(!empty($vaccination['tutor']))
                            <p class="text-muted small mb-0">Tutor: {{ $vaccination['tutor'] }}</p>
                        @endif
                    </div>
                    <div class="col-md-4">
                        <h2 class="vet-vaccination-apply__section-title mb-2">Agendamento</h2>
                        <div class="vet-vaccination-apply__meta">
                            <span><i class="ri-calendar-line"></i> {{ $vaccination['scheduled_at'] ?? '—' }}</span>
                            <span><i class="ri-shield-cross-line"></i> {{ data_get($vaccination, 'vaccine.name', '—') }}</span>
                            <span><i class="ri-user-heart-line"></i> {{ $vaccination['veterinarian'] ?? '—' }}</span>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <h2 class="vet-vaccination-apply__section-title mb-2">Última atualização</h2>
                        <p class="mb-1">Última aplicação registrada:</p>
                        <p class="fw-semibold text-primary mb-1">{{ $vaccination['last_application'] ?? '—' }}</p>
                        @if(!empty($vaccination['planning_notes']))
                            <p class="text-muted small mb-0">Notas do planejamento: {{ $vaccination['planning_notes'] }}</p>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        <div class="col-12">
            <form method="POST" action="{{ route('vet.vaccinations.apply.store', $vaccinationModel) }}">
                @csrf

                <div class="card mb-4">
                    <div class="card-body">
                        <h2 class="vet-vaccination-apply__section-title mb-3">Informações da sessão</h2>

                        @if($errors->any())
                            <div class="alert alert-danger">
                                <p class="mb-1 fw-semibold">Ocorreram alguns problemas ao salvar:</p>
                                <ul class="mb-0 ps-3">
                                    @foreach ($errors->all() as $error)
                                        <li>{{ $error }}</li>
                                    @endforeach
                                </ul>
                            </div>
                        @endif

                        <div class="row g-3">
                            <div class="col-md-4">
                                <label class="form-label" for="inicio_execucao_at">Início</label>
                                <input
                                    type="datetime-local"
                                    class="form-control @error('inicio_execucao_at') is-invalid @enderror"
                                    id="inicio_execucao_at"
                                    name="inicio_execucao_at"
                                    value="{{ old('inicio_execucao_at', $sessionDefaults['inicio_execucao_at']) }}"
                                    required
                                >
                                @error('inicio_execucao_at')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-4">
                                <label class="form-label" for="termino_execucao_at">Término</label>
                                <input
                                    type="datetime-local"
                                    class="form-control @error('termino_execucao_at') is-invalid @enderror"
                                    id="termino_execucao_at"
                                    name="termino_execucao_at"
                                    value="{{ old('termino_execucao_at', $sessionDefaults['termino_execucao_at']) }}"
                                >
                                @error('termino_execucao_at')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-4">
                                <label class="form-label" for="status">Status da sessão</label>
                                <select
                                    class="form-select select2 @error('status') is-invalid @enderror"
                                    id="status"
                                    name="status"
                                    data-toggle="select2"
                                    data-placeholder="Selecione o status da sessão"
                                    required
                                >
                                    @foreach($sessionStatusOptions as $value => $label)
                                        <option value="{{ $value }}" @selected(old('status', $sessionDefaults['status']) === $value)>{{ $label }}</option>
                                    @endforeach
                                </select>
                                @error('status')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-4">
                                <label class="form-label" for="responsavel_id">Responsável pela sessão</label>
                                <select class="form-select @error('responsavel_id') is-invalid @enderror" id="responsavel_id" name="responsavel_id">
                                    <option value="">Selecionar</option>
                                    @foreach($teamMembers as $member)
                                        <option value="{{ $member['id'] }}" @selected(old('responsavel_id', $sessionDefaults['responsavel_id']) === $member['id'])>
                                            {{ $member['name'] }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('responsavel_id')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-8">
                                <label class="form-label" for="assistentes_ids">Assistentes</label>
                                <select
                                    class="form-select select2 @error('assistentes_ids') is-invalid @enderror"
                                    id="assistentes_ids"
                                    name="assistentes_ids[]"
                                    data-toggle="select2"
                                    multiple
                                >
                                    @foreach($teamMembers as $member)
                                        <option value="{{ $member['id'] }}" @selected(collect(old('assistentes_ids', $sessionDefaults['assistentes_ids'] ?? []))->contains($member['id']))>
                                            {{ $member['name'] }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('assistentes_ids')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                                @error('assistentes_ids.*')
                                    <div class="invalid-feedback d-block">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-12">
                                <label class="form-label" for="observacoes_execucao">Observações da sessão</label>
                                <textarea
                                    class="form-control @error('observacoes_execucao') is-invalid @enderror"
                                    id="observacoes_execucao"
                                    name="observacoes_execucao"
                                    rows="3"
                                >{{ old('observacoes_execucao') }}</textarea>
                                @error('observacoes_execucao')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>
                </div>

                @foreach($vaccination['vaccines'] as $index => $dose)
                    @php
                        $selectedResult = old('doses.' . $index . '.resultado', 'aplicada');
                        $shouldShowReason = in_array($selectedResult, ['nao_aplicada', 'reagendada'], true);
                    @endphp
                    <div class="vet-vaccination-apply__dose-card mb-4">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-start flex-wrap gap-2 mb-3">
                                <div>
                                    <h3 class="vet-vaccination-apply__section-title mb-1">Dose {{ $index + 1 }}</h3>
                                    <p class="text-muted mb-0">{{ $dose['name'] ?? '—' }} • {{ $dose['dose'] ?? 'Dosagem não informada' }}</p>
                                    <p class="text-muted small mb-0">
                                        Fabricante: {{ $dose['manufacturer'] ?? '—' }} • Lote: {{ $dose['lot'] ?? '—' }}
                                    </p>
                                </div>
                                <div class="text-end text-muted small">
                                    Volume previsto: {{ $dose['volume'] ?? '—' }}<br>
                                    Via prevista: {{ $dose['route'] ?? '—' }}
                                </div>
                            </div>

                            <input type="hidden" name="doses[{{ $index }}][dose_planejada_id]" value="{{ $dose['planned_id'] ?? '' }}">

                            <div class="row g-3">
                                <div class="col-md-4">
                                    <label class="form-label" for="dose-{{ $index }}-aplicada_em">Data de aplicação</label>
                                    <input
                                        type="datetime-local"
                                        class="form-control @error('doses.' . $index . '.aplicada_em') is-invalid @enderror"
                                        id="dose-{{ $index }}-aplicada_em"
                                        name="doses[{{ $index }}][aplicada_em]"
                                        value="{{ old('doses.' . $index . '.aplicada_em', $sessionDefaults['inicio_execucao_at']) }}"
                                        required
                                    >
                                    @error('doses.' . $index . '.aplicada_em')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label" for="dose-{{ $index }}-resultado">Resultado</label>
                                    <select
                                        class="form-select @error('doses.' . $index . '.resultado') is-invalid @enderror"
                                        id="dose-{{ $index }}-resultado"
                                        name="doses[{{ $index }}][resultado]"
                                        data-dose-result="{{ $index }}"
                                        required
                                    >
                                        @foreach($doseResultOptions as $value => $label)
                                            <option value="{{ $value }}" @selected($selectedResult === $value)>{{ $label }}</option>
                                        @endforeach
                                    </select>
                                    @error('doses.' . $index . '.resultado')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label" for="dose-{{ $index }}-responsavel_id">Responsável pela dose</label>
                                    <select class="form-select @error('doses.' . $index . '.responsavel_id') is-invalid @enderror" id="dose-{{ $index }}-responsavel_id" name="doses[{{ $index }}][responsavel_id]">
                                        <option value="">Mesmo responsável da sessão</option>
                                        @foreach($teamMembers as $member)
                                            <option value="{{ $member['id'] }}" @selected(old('doses.' . $index . '.responsavel_id') === $member['id'])>{{ $member['name'] }}</option>
                                        @endforeach
                                    </select>
                                    @error('doses.' . $index . '.responsavel_id')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label" for="dose-{{ $index }}-quantidade_ml">Quantidade aplicada (ml)</label>
                                    <input
                                        type="number"
                                        step="0.01"
                                        class="form-control @error('doses.' . $index . '.quantidade_ml') is-invalid @enderror"
                                        id="dose-{{ $index }}-quantidade_ml"
                                        name="doses[{{ $index }}][quantidade_ml]"
                                        value="{{ old('doses.' . $index . '.quantidade_ml', $dose['predicted_volume']) }}"
                                        min="0"
                                    >
                                    @error('doses.' . $index . '.quantidade_ml')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label" for="dose-{{ $index }}-via_aplicacao">Via de aplicação</label>
                                    <select class="form-select @error('doses.' . $index . '.via_aplicacao') is-invalid @enderror" id="dose-{{ $index }}-via_aplicacao" name="doses[{{ $index }}][via_aplicacao]">
                                        <option value="">Selecionar</option>
                                        @foreach($viaOptions as $value => $label)
                                            <option value="{{ $value }}" @selected(old('doses.' . $index . '.via_aplicacao', $dose['route_value'] ?? null) === $value)>{{ $label }}</option>
                                        @endforeach
                                    </select>
                                    @error('doses.' . $index . '.via_aplicacao')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label" for="dose-{{ $index }}-local_anatomico">Local anatômico</label>
                                    <input
                                        type="text"
                                        class="form-control @error('doses.' . $index . '.local_anatomico') is-invalid @enderror"
                                        id="dose-{{ $index }}-local_anatomico"
                                        name="doses[{{ $index }}][local_anatomico]"
                                        value="{{ old('doses.' . $index . '.local_anatomico', $dose['site'] ?? '') }}"
                                    >
                                    @error('doses.' . $index . '.local_anatomico')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label" for="dose-{{ $index }}-lote_id">Lote (ID do estoque)</label>
                                    <input
                                        type="number"
                                        class="form-control @error('doses.' . $index . '.lote_id') is-invalid @enderror"
                                        id="dose-{{ $index }}-lote_id"
                                        name="doses[{{ $index }}][lote_id]"
                                        value="{{ old('doses.' . $index . '.lote_id') }}"
                                        min="0"
                                        step="1"
                                    >
                                    @error('doses.' . $index . '.lote_id')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label" for="dose-{{ $index }}-temperatura_pet">Temperatura do pet (°C)</label>
                                    <input
                                        type="number"
                                        step="0.1"
                                        class="form-control @error('doses.' . $index . '.temperatura_pet') is-invalid @enderror"
                                        id="dose-{{ $index }}-temperatura_pet"
                                        name="doses[{{ $index }}][temperatura_pet]"
                                        value="{{ old('doses.' . $index . '.temperatura_pet') }}"
                                        min="30"
                                        max="45"
                                    >
                                    @error('doses.' . $index . '.temperatura_pet')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label" for="dose-{{ $index }}-observacoes">Observações clínicas</label>
                                    <textarea
                                        class="form-control @error('doses.' . $index . '.observacoes') is-invalid @enderror"
                                        id="dose-{{ $index }}-observacoes"
                                        name="doses[{{ $index }}][observacoes]"
                                        rows="2"
                                    >{{ old('doses.' . $index . '.observacoes') }}</textarea>
                                    @error('doses.' . $index . '.observacoes')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                                <div
                                    class="col-md-6 vet-vaccination-apply__dose-reason {{ $shouldShowReason ? '' : 'd-none' }}"
                                    data-dose-reason="{{ $index }}"
                                >
                                    <label class="form-label" for="dose-{{ $index }}-motivo_nao_aplicacao">Motivo (quando não aplicada ou reagendada)</label>
                                    <textarea
                                        class="form-control @error('doses.' . $index . '.motivo_nao_aplicacao') is-invalid @enderror"
                                        id="dose-{{ $index }}-motivo_nao_aplicacao"
                                        name="doses[{{ $index }}][motivo_nao_aplicacao]"
                                        @unless($shouldShowReason) disabled @endunless
                                        rows="2"
                                    >{{ old('doses.' . $index . '.motivo_nao_aplicacao') }}</textarea>
                                    @error('doses.' . $index . '.motivo_nao_aplicacao')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>
                    </div>
                @endforeach

                <div class="d-flex justify-content-between align-items-center">
                    <a class="btn btn-outline-secondary" href="{{ route('vet.vaccinations.index') }}">
                        <i class="ri-arrow-go-back-line"></i> Voltar para a listagem
                    </a>
                    <button type="submit" class="btn btn-primary">
                        <i class="ri-check-fill"></i> Registrar aplicação
                    </button>
                </div>
            </form>
        </div>
    </div>
</x-form-page>
@endsection

@section('js')
    <script>
        (function (global) {
            'use strict';

            const $ = global.jQuery || global.$;

            if (!$ || !$.fn || typeof $.fn.select2 !== 'function') {
                return;
            }

            $(function () {
                $('.select2').each(function () {
                    const $select = $(this);
                    const allowClearAttr = $select.data('allow-clear');
                    let allowClear = false;

                    if (typeof allowClearAttr === 'string') {
                        allowClear = allowClearAttr === 'true';
                    } else if (typeof allowClearAttr === 'boolean') {
                        allowClear = allowClearAttr;
                    }

                    $select.select2({
                        width: '100%',
                        placeholder: $select.data('placeholder') || '',
                        allowClear,
                    });
                });
            });
        })(window);
    </script>
    <script>
        (function () {
            const RESULT_FIELDS = ['nao_aplicada', 'reagendada'];

            document.querySelectorAll('[data-dose-result]').forEach((select) => {
                const doseIndex = select.getAttribute('data-dose-result');
                const reasonWrapper = document.querySelector(`[data-dose-reason="${doseIndex}"]`);
                if (!reasonWrapper) {
                    return;
                }

                const textarea = reasonWrapper.querySelector('textarea');

                const toggleReason = () => {
                    const shouldShow = RESULT_FIELDS.includes(select.value);
                    reasonWrapper.classList.toggle('d-none', !shouldShow);

                    if (textarea) {
                        textarea.toggleAttribute('disabled', !shouldShow);
                        if (shouldShow) {
                            textarea.setAttribute('required', 'required');
                        } else {
                            textarea.removeAttribute('required');
                        }
                    }
                };

                select.addEventListener('change', toggleReason);
                toggleReason();
            });
        })();
    </script>
@endsection

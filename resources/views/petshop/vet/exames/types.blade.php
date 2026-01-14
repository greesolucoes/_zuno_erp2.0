@extends('default.layout', ['title' => 'Tipos de Exames'])

@section('css')
    <style>
        .vet-exams__type-wrapper {
            border-radius: 20px;
            border: 1px solid rgba(22, 22, 107, 0.08);
            background: #fff;
            box-shadow: 0 12px 32px rgba(22, 22, 107, 0.1);
        }

        .vet-exams__category-chip {
            display: inline-flex;
            align-items: center;
            gap: .4rem;
            border-radius: 999px;
            background: rgba(85, 110, 230, 0.12);
            color: #556ee6;
            padding: .45rem .85rem;
            font-size: .75rem;
            font-weight: 600;
        }

        .vet-exams__type-grid {
            display: grid;
            gap: 1.5rem;
        }

        @media (min-width: 768px) {
            .vet-exams__type-grid {
                grid-template-columns: repeat(2, minmax(0, 1fr));
            }
        }

        .vet-exams__type-card {
            border-radius: 16px;
            border: 1px solid rgba(22, 22, 107, 0.08);
            background: #fdfdff;
            padding: 1.5rem;
        }
    </style>
@endsection

@section('content')
<x-form-page
    title="Tipos de Exames"
    heading="Tipos de exames cadastrados"
    :back-url="route('vet.exams.index', ['page' => request()->query('page', 1)])"
>
        <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-4">
            <p class="text-muted mb-0">Consulte as descrições e preparos recomendados antes de realizar cada exame.</p>
            <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#createExamTypeModal">
                <i class="mdi mdi-plus me-1"></i> Novo tipo de exame
            </button>
        </div>

        <div class="modal fade" id="createExamTypeModal" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-lg modal-dialog-centered">
                <div class="modal-content">
                    <form action="{{ route('vet.exams.types.store') }}" method="POST" novalidate>
                        @csrf

                        <div class="modal-header">
                            <h5 class="modal-title">Cadastrar tipo de exame</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fechar"></button>
                        </div>

                        <div class="modal-body">
                            <div class="mb-3">
                                <label class="form-label" for="exam-type-name">Nome do exame</label>
                                <input
                                    type="text"
                                    class="form-control @error('nome', 'storeExamType') is-invalid @enderror"
                                    id="exam-type-name"
                                    name="nome"
                                    value="{{ old('nome') }}"
                                    maxlength="200"
                                    required
                                    placeholder="Ex.: Hemograma completo"
                                >
                                @error('nome', 'storeExamType')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div>
                                <label class="form-label" for="exam-type-description">Descrição e orientações</label>
                                <textarea
                                    class="form-control @error('descricao', 'storeExamType') is-invalid @enderror"
                                    id="exam-type-description"
                                    name="descricao"
                                    rows="4"
                                    maxlength="2000"
                                    placeholder="Detalhe quando solicitar, orientações de preparo ou observações internas"
                                >{{ old('descricao') }}</textarea>
                                @error('descricao', 'storeExamType')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="modal-footer">
                            <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancelar</button>
                            <button type="submit" class="btn btn-primary">Salvar tipo de exame</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <div class="vet-exams__type-wrapper p-4 mb-4">
            <h5 class="mb-3">Categorias mais solicitadas</h5>
            <div class="d-flex flex-wrap gap-2">
                @foreach ($categories as $category => $items)
                    <span class="vet-exams__category-chip">
                        <i class="mdi mdi-shield-check-outline"></i>
                        {{ $category }}
                        <span class="badge bg-light text-dark ms-1">{{ count($items) }}</span>
                    </span>
                @endforeach
            </div>
        </div>

        <div class="vet-exams__type-grid">
            @foreach ($examTypes as $type)
                <x-petshop.vet.exames.exam-type-card :type="$type" />
            @endforeach
        </div>
</x-form-page>
@endsection

@section('js')
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            var modalElement = document.getElementById('createExamTypeModal');

            if (!modalElement) {
                return;
            }

            if (typeof bootstrap === 'undefined') {
                return;
            }

            @if ($errors->storeExamType->any())
                var modalInstance = new bootstrap.Modal(modalElement);
                modalInstance.show();
            @endif
        });
    </script>
@endsection

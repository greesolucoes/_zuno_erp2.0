@extends('default.layout', ['title' => 'Emitir laudo'])

@php
    $statusLabel = \App\Models\Petshop\VetExame::statusLabels()[$exam->status] ?? ucwords(str_replace('_', ' ', (string) $exam->status));
@endphp

@php
    $analysisStateInitial = [];
    $analysisStateInputValue = '{}';
    $analysisStateOld = old('analysis_state');

    if (is_string($analysisStateOld) && $analysisStateOld !== '') {
        $decodedAnalysisState = json_decode($analysisStateOld, true);

        if (json_last_error() === JSON_ERROR_NONE && is_array($decodedAnalysisState)) {
            $analysisStateInitial = $decodedAnalysisState;
        }
    } elseif (isset($analysisState) && is_array($analysisState)) {
        $analysisStateInitial = $analysisState;
    }

    if ($analysisStateInitial !== []) {
        $encodedAnalysisState = json_encode($analysisStateInitial, JSON_UNESCAPED_UNICODE);

        if (is_string($encodedAnalysisState) && $encodedAnalysisState !== '') {
            $analysisStateInputValue = $encodedAnalysisState;
        }
    }
@endphp

@section('css')
    <style>
        .vet-exams-report {
            position: relative;
        }

        .vet-exams-report__badge {
            text-transform: uppercase;
            letter-spacing: 0.08em;
            font-size: 0.75rem;
        }

        .vet-exams-report__summary-card {
            border-radius: 18px;
            border: 1px solid rgba(22, 22, 107, 0.08);
            background: #fff;
            box-shadow: 0 14px 32px rgba(22, 22, 107, 0.08);
        }

        .vet-exams-report__summary-label {
            text-transform: uppercase;
            font-size: 0.7rem;
            letter-spacing: 0.06em;
            color: #7a7a7a;
            font-weight: 600;
        }

        .vet-exams-report__summary-value {
            font-size: 1rem;
            font-weight: 600;
            color: #16166b;
        }

        .vet-exams-report__viewer-card {
            border-radius: 20px;
            border: 1px solid rgba(22, 22, 107, 0.08);
            background: #0c0c0f;
            color: #f8f9fa;
        }

        .dicom-toolbar {
            display: flex;
            flex-wrap: wrap;
            gap: 0.5rem;
            margin-bottom: 1rem;
        }

        .dicom-viewer__wrapper {
            display: flex;
            flex-direction: column;
            gap: 1rem;
        }

        .dicom-viewer__wrapper:fullscreen,
        .dicom-viewer__wrapper:-webkit-full-screen {
            width: 100vw;
            height: 100vh;
            padding: 1.5rem;
            box-sizing: border-box;
        }

        .dicom-viewer__wrapper:fullscreen .dicom-viewer,
        .dicom-viewer__wrapper:-webkit-full-screen .dicom-viewer {
            flex: 1;
            min-height: 0;
        }

        .dicom-viewer__wrapper:fullscreen .dicom-viewer__viewport,
        .dicom-viewer__wrapper:-webkit-full-screen .dicom-viewer__viewport {
            min-height: calc(100vh - 200px);
        }

        .dicom-toolbar .btn {
            border-radius: 10px;
        }

        .dicom-toolbar .btn.active {
            background: #556ee6;
            color: #fff;
            border-color: #556ee6;
            box-shadow: 0 0 0 0.2rem rgba(85, 110, 230, 0.25);
        }

        .dicom-toolbar .btn[data-action="fullscreen"].active {
            background: rgba(85, 110, 230, 0.2);
            border-color: rgba(85, 110, 230, 0.4);
            color: #fff;
        }

        .dicom-viewer {
            position: relative;
            background: #000;
            border-radius: 16px;
            overflow: hidden;
            min-height: 480px;
        }

        .dicom-viewer:fullscreen,
        .dicom-viewer:-webkit-full-screen {
            border-radius: 0;
            width: 100vw;
            height: 100vh;
        }

        .dicom-viewer:fullscreen .dicom-viewer__viewport,
        .dicom-viewer:-webkit-full-screen .dicom-viewer__viewport {
            min-height: 100vh;
        }

        .dicom-viewer__viewport {
            height: 100%;
            width: 100%;
            min-height: 480px;
            cursor: crosshair;
        }

        .dicom-viewer__overlay {
            position: absolute;
            top: 0.75rem;
            left: 0.75rem;
            color: #f8f9fa;
            font-size: 0.85rem;
            text-shadow: 0 1px 2px rgba(0, 0, 0, 0.7);
            max-width: 320px;
        }

        .dicom-viewer__overlay p {
            margin-bottom: 0.25rem;
        }

        .dicom-viewer__status {
            margin-top: 1rem;
            display: flex;
            flex-wrap: wrap;
            gap: 1rem;
            font-size: 0.85rem;
            color: rgba(248, 249, 250, 0.9);
        }

        .dicom-attachments {
            max-height: 480px;
            overflow-y: auto;
            border-radius: 16px;
            border: 1px solid rgba(22, 22, 107, 0.08);
        }

        .dicom-attachment-item {
            cursor: pointer;
            transition: background-color 0.2s ease, box-shadow 0.2s ease;
            border: none;
            border-bottom: 1px solid rgba(22, 22, 107, 0.08);
        }

        .dicom-attachment-item:last-child {
            border-bottom: none;
        }

        .dicom-attachment-item:hover {
            background: rgba(85, 110, 230, 0.08);
        }

        .dicom-attachment-item.is-active {
            background: rgba(85, 110, 230, 0.12);
            box-shadow: inset 0 0 0 2px rgba(85, 110, 230, 0.35);
        }

        .dicom-attachment-item.is-disabled {
            cursor: not-allowed;
            opacity: 0.65;
        }

        .dicom-annotations {
            display: grid;
            gap: 0.75rem;
        }

        .dicom-annotations__item {
            border-radius: 14px;
            border: 1px solid rgba(22, 22, 107, 0.08);
            background: #fff;
            padding: 1rem;
        }

        .dicom-annotations__item-title {
            font-size: 0.9rem;
            font-weight: 600;
            color: #16166b;
        }

        .dicom-annotations__item-meta {
            font-size: 0.75rem;
            color: #6c757d;
        }

        .dicom-annotations__empty {
            text-align: center;
            color: #6c757d;
            padding: 1.5rem 1rem;
            border-radius: 14px;
            border: 1px dashed rgba(22, 22, 107, 0.12);
            background: rgba(85, 110, 230, 0.04);
        }

        .vet-exams-report__form textarea.form-control {
            min-height: 240px;
            resize: vertical;
        }

        @media (max-width: 991.98px) {
            .dicom-viewer,
            .dicom-viewer__viewport {
                min-height: 320px;
            }

            .dicom-attachments {
                max-height: none;
            }

            .dicom-viewer__wrapper:fullscreen,
            .dicom-viewer__wrapper:-webkit-full-screen {
                padding: 0.75rem;
            }

            .dicom-viewer__wrapper:fullscreen .dicom-viewer__viewport,
            .dicom-viewer__wrapper:-webkit-full-screen .dicom-viewer__viewport {
                min-height: calc(100vh - 160px);
            }
        }
    </style>
@endsection

@section('content')
<x-form-page
    title="Emitir laudo"
    heading="{{ $exam->examType?->nome ?? 'Laudo de exame' }}"
    :back-url="$backUrl"
>
    <div class="vet-exams-report">
        <div class="d-flex justify-content-end mb-3">
            <a href="{{ route('vet.exams.collect', $exam) }}" class="btn btn-outline-primary btn-sm">
                <i class="ri-file-add-line"></i>
                Ver coleta
            </a>
        </div>
        <p class="text-muted mb-4">
            Paciente: {{ $exam->animal?->nome ?? '—' }}
            @if ($exam->animal?->cliente)
                • Tutor: {{ $exam->animal->cliente->razao_social }}
            @endif
        </p>

        <div class="vet-exams-report__summary-card p-4 mb-4">
            <div class="row g-4">
                <div class="col-sm-6 col-lg-3">
                    <div class="vet-exams-report__summary-label">Exame</div>
                    <div class="vet-exams-report__summary-value">{{ $exam->examType?->nome ?? '—' }}</div>
                </div>
                <div class="col-sm-6 col-lg-3">
                    <div class="vet-exams-report__summary-label">Status</div>
                    <div class="vet-exams-report__summary-value">{{ $statusLabel }}</div>
                </div>
                <div class="col-sm-6 col-lg-3">
                    <div class="vet-exams-report__summary-label">Veterinário</div>
                    <div class="vet-exams-report__summary-value">{{ $exam->medico?->funcionario?->nome ?? '—' }}</div>
                </div>
                <div class="col-sm-6 col-lg-3">
                    <div class="vet-exams-report__summary-label">Atendimento</div>
                    <div class="vet-exams-report__summary-value">
                        @if ($attendanceContext)
                            Atendimento {{ $attendanceContext['code'] ?? $exam->attendance?->codigo ?? '—' }}
                        @else
                            —
                        @endif
                    </div>
                </div>
            </div>
        </div>

        <div class="row g-4">
            <div class="col-lg-4 col-xl-3">
                <div class="card h-100">
                    <div class="card-body">
                        <h3 class="h6 text-uppercase text-muted mb-3">Documentos da coleta</h3>
                        <p class="text-muted small mb-3">
                            Selecione um documento para abrir no visualizador e utilize as ferramentas para medir ou anotar regiões de interesse.
                        </p>
                        <div class="dicom-attachments" id="dicomAttachmentList">
                            @forelse ($viewerAttachments as $attachment)
                                <div
                                    class="dicom-attachment-item p-3 {{ !$attachment['is_supported'] ? 'is-disabled' : '' }}"
                                    data-attachment-id="{{ $attachment['id'] }}"
                                    data-url="{{ $attachment['url'] }}"
                                    data-extension="{{ $attachment['extension'] }}"
                                    data-is-supported="{{ $attachment['is_supported'] ? '1' : '0' }}"
                                    data-is-dicom="{{ $attachment['is_dicom'] ? '1' : '0' }}"
                                    data-name="{{ $attachment['name'] }}"
                                >
                                    <div class="d-flex justify-content-between align-items-start gap-3">
                                        <div>
                                            <div class="fw-semibold text-color mb-1">{{ $attachment['name'] }}</div>
                                            <div class="text-muted small">{{ $attachment['description'] }}</div>
                                            @if (!$attachment['is_supported'])
                                                <div class="text-warning small mt-2">
                                                    <i class="ri-alert-line me-1"></i>
                                                    Visualização não suportada. Faça o download para analisar.
                                                </div>
                                            @endif
                                        </div>
                                        <div class="text-end">
                                            <span class="badge bg-primary-subtle text-primary">{{ strtoupper($attachment['type_label'] ?? $attachment['extension'] ?? 'ARQ') }}</span>
                                            @if ($attachment['download_url'])
                                                <div class="mt-2">
                                                    <a
                                                        href="{{ $attachment['download_url'] }}"
                                                        class="btn btn-outline-secondary btn-sm"
                                                        target="_blank"
                                                        rel="noopener"
                                                    >
                                                        <i class="ri-download-2-line"></i>
                                                    </a>
                                                </div>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            @empty
                                <div class="text-muted text-center py-4">
                                    Nenhum documento foi anexado na coleta deste exame.
                                </div>
                            @endforelse
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-lg-8 col-xl-9">
                <div class="card vet-exams-report__viewer-card mb-4">
                    <div class="card-body">
                        <div class="dicom-viewer__wrapper" id="dicomViewerWrapper">
                            <div class="dicom-toolbar" id="dicomToolbar">
                                <button type="button" class="btn btn-outline-light" data-tool="Wwwc">
                                    <i class="ri-contrast-drop-2-line me-1"></i>
                                    Janela/Nível
                                </button>
                                <button type="button" class="btn btn-outline-light" data-tool="Length">
                                <i class="ri-ruler-line me-1"></i>
                                Medir distância
                            </button>
                            <button type="button" class="btn btn-outline-light" data-tool="RectangleRoi">
                                <i class="ri-focus-2-line me-1"></i>
                                ROI retangular
                            </button>
                            <button type="button" class="btn btn-outline-light" data-tool="EllipticalRoi">
                                <i class="ri-focus-3-line me-1"></i>
                                ROI elíptica
                            </button>
                            <button type="button" class="btn btn-outline-light" data-tool="FreehandRoi">
                                <i class="ri-shape-line me-1"></i>
                                ROI livre
                            </button>
                            <button type="button" class="btn btn-outline-light" data-tool="ArrowAnnotate">
                                <i class="ri-annotation-line me-1"></i>
                                Anotar
                            </button>
                            <button type="button" class="btn btn-outline-light" data-action="fullscreen">
                                <i class="ri-fullscreen-line me-1"></i>
                                <span class="dicom-toolbar__label">Tela cheia</span>
                            </button>
                            <div class="vr text-white opacity-50"></div>
                            <button type="button" class="btn btn-outline-light" data-action="reset">
                                <i class="ri-refresh-line me-1"></i>
                                Resetar
                            </button>
                            <button type="button" class="btn btn-outline-light" data-action="invert">
                                <i class="ri-invert-right-line me-1"></i>
                                Inverter
                            </button>
                            <button type="button" class="btn btn-outline-light" data-action="flipH">
                                <i class="ri-swap-line me-1"></i>
                                Espelhar H
                            </button>
                            <button type="button" class="btn btn-outline-light" data-action="flipV">
                                <i class="ri-swap-line me-1"></i>
                                Espelhar V
                            </button>
                            <button type="button" class="btn btn-outline-light" data-action="rotateLeft">
                                <i class="ri-anticlockwise-2-line me-1"></i>
                                Rotacionar -90°
                            </button>
                            <button type="button" class="btn btn-outline-light" data-action="rotateRight">
                                <i class="ri-clockwise-2-line me-1"></i>
                                Rotacionar +90°
                            </button>
                        </div>

                            <div class="dicom-viewer" id="dicomViewer">
                                <div id="dicomViewport" class="dicom-viewer__viewport" role="presentation" aria-label="Visualizador de imagens médicas"></div>
                                <div class="dicom-viewer__overlay" id="dicomOverlay">
                                    <p class="fw-semibold" id="dicomOverlayTitle">Selecione um documento para iniciar a visualização.</p>
                                    <p class="mb-0" id="dicomOverlayMeta"></p>
                                </div>
                            </div>
                        </div>

                        <div class="dicom-viewer__status mt-3" id="dicomStatus">
                            <div><strong>Ferramenta ativa:</strong> <span id="dicomActiveTool">Janela/Nível</span></div>
                            <div><strong>Zoom:</strong> <span id="dicomZoom">1.0x</span></div>
                            <div><strong>Janela:</strong> <span id="dicomWindow">—</span></div>
                            <div><strong>Imagem:</strong> <span id="dicomActiveAttachment">Nenhum documento carregado</span></div>
                        </div>
                    </div>
                </div>

                <div class="card mb-4">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
                            <h3 class="h6 text-uppercase text-muted mb-0">Laudo do exame</h3>
                            <span class="badge bg-light text-dark">Protocolo #{{ $exam->id }}</span>
                        </div>
                        <form
                            action="{{ route('vet.exams.report.update', $exam) }}"
                            method="POST"
                            class="vet-exams-report__form"
                        >
                            @csrf
                            @method('PUT')
                            @if (request()->filled('attendance'))
                                <input type="hidden" name="attendance" value="{{ request('attendance') }}">
                            @endif
                            <input
                                type="hidden"
                                name="analysis_state"
                                id="vetExamReportAnalysisState"
                                value="{{ e(old('analysis_state', $analysisStateInputValue)) }}"
                            >
                            <div class="mb-3">
                                <label for="vetExamReportContent" class="form-label">Conteúdo do laudo</label>
                                <textarea
                                    id="vetExamReportContent"
                                    name="laudo"
                                    class="form-control @error('laudo') is-invalid @enderror"
                                    placeholder="Descreva as conclusões, observações e recomendações do exame."
                                >{{ old('laudo', $exam->laudo) }}</textarea>
                                @error('laudo')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label for="vetExamReportStatus" class="form-label">Status do exame</label>
                                    <select
                                        id="vetExamReportStatus"
                                        name="status"
                                        class="form-select @error('status') is-invalid @enderror"
                                    >
                                        @foreach ($statusOptions as $option)
                                            <option
                                                value="{{ $option['value'] }}"
                                                @selected(old('status', $exam->status) === $option['value'])
                                            >
                                                {{ $option['label'] }}
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('status')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                                <div class="col-md-6">
                                    <label for="vetExamReportConclusionAt" class="form-label">Data de conclusão</label>
                                    <input
                                        type="datetime-local"
                                        id="vetExamReportConclusionAt"
                                        name="data_conclusao"
                                        value="{{ old('data_conclusao', optional($exam->data_conclusao)?->format('Y-m-d\TH:i')) }}"
                                        class="form-control @error('data_conclusao') is-invalid @enderror"
                                        aria-describedby="vetExamReportConclusionHelp"
                                    >
                                    <div id="vetExamReportConclusionHelp" class="form-text">
                                        Informe quando o laudo ficou pronto. Deixe em branco para usar a data e hora atuais.
                                    </div>
                                    @error('data_conclusao')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            <div class="d-flex justify-content-end gap-2 mt-4">
                                <a href="{{ $backUrl }}" class="btn btn-light">Cancelar</a>
                                <button type="submit" class="btn btn-primary">
                                    <i class="ri-save-3-line me-1"></i>
                                    Salvar laudo
                                </button>
                            </div>
                        </form>
                    </div>
                </div>

                <div class="card">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
                            <h3 class="h6 text-uppercase text-muted mb-0">Anotações e medições</h3>
                            <div class="text-muted small">As anotações ficam vinculadas à imagem atual durante a sessão.</div>
                        </div>
                        <div id="dicomAnnotationsList" class="dicom-annotations">
                            <div class="dicom-annotations__empty">Nenhuma anotação registrada até o momento. Utilize as ferramentas ao lado para marcar pontos de interesse.</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        @if (!$hasSupportedAttachments)
            <div class="alert alert-warning d-flex align-items-start gap-2 mt-4" role="alert">
                <i class="ri-alert-line fs-4"></i>
                <div>
                    <strong>Visualização indisponível:</strong> Os documentos anexados na coleta não possuem formato compatível com o visualizador DICOM.
                    Utilize o botão de download nos anexos para analisá-los manualmente.
                </div>
            </div>
        @endif
    </div>
</x-form-page>
@endsection

@section('js')
    <script src="https://unpkg.com/hammerjs@2.0.8/hammer.min.js"></script>
    <script src="https://unpkg.com/cornerstone-core@2.6.0/dist/cornerstone.min.js"></script>
    <script src="https://unpkg.com/cornerstone-math@0.1.8/dist/cornerstoneMath.min.js"></script>
    <script src="https://unpkg.com/dicom-parser@1.8.13/dist/dicomParser.min.js"></script>
    <script src="https://unpkg.com/cornerstone-wado-image-loader@4.13.1/dist/cornerstoneWADOImageLoader.bundle.min.js"></script>
    <script src="https://unpkg.com/cornerstone-web-image-loader@2.1.1/dist/cornerstoneWebImageLoader.min.js"></script>
    <script src="https://unpkg.com/cornerstone-tools@4.21.1/dist/cornerstoneTools.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const cornerstone = window.cornerstone;
            const cornerstoneMath = window.cornerstoneMath;
            const cornerstoneTools = window.cornerstoneTools;
            const cornerstoneWADOImageLoader = window.cornerstoneWADOImageLoader;
            const cornerstoneWebImageLoader = window.cornerstoneWebImageLoader;
            const dicomParser = window.dicomParser;

            if (!cornerstone || !cornerstoneTools || !cornerstoneWADOImageLoader) {
                console.error('Cornerstone não pôde ser inicializado. Verifique as dependências do visualizador.');
                return;
            }

            cornerstoneTools.external.cornerstone = cornerstone;
            cornerstoneTools.external.cornerstoneMath = cornerstoneMath;
            cornerstoneTools.external.Hammer = window.Hammer;

            cornerstoneWADOImageLoader.external.cornerstone = cornerstone;
            cornerstoneWADOImageLoader.external.dicomParser = dicomParser;
            cornerstoneWADOImageLoader.configure({ useWebWorkers: false });

            if (cornerstoneWebImageLoader) {
                cornerstoneWebImageLoader.external.cornerstone = cornerstone;
                cornerstone.registerImageLoader('http', cornerstoneWebImageLoader.loadImage);
                cornerstone.registerImageLoader('https', cornerstoneWebImageLoader.loadImage);
            }

            cornerstoneTools.init();

            const viewerElement = document.getElementById('dicomViewport');
            const viewerContainer = document.getElementById('dicomViewer');
            const viewerWrapper = document.getElementById('dicomViewerWrapper');
            const overlayTitle = document.getElementById('dicomOverlayTitle');
            const overlayMeta = document.getElementById('dicomOverlayMeta');
            const attachmentList = document.querySelectorAll('#dicomAttachmentList .dicom-attachment-item');
            const toolbarButtons = document.querySelectorAll('#dicomToolbar .btn');
            const annotationsList = document.getElementById('dicomAnnotationsList');
            const activeToolLabel = document.getElementById('dicomActiveTool');
            const zoomLabel = document.getElementById('dicomZoom');
            const windowLabel = document.getElementById('dicomWindow');
            const activeAttachmentLabel = document.getElementById('dicomActiveAttachment');
            const fullscreenButton = document.querySelector('#dicomToolbar .btn[data-action="fullscreen"]');
            const fullscreenIcon = fullscreenButton ? fullscreenButton.querySelector('i') : null;
            const fullscreenLabel = fullscreenButton ? fullscreenButton.querySelector('.dicom-toolbar__label') : null;
            const attachments = @json($viewerAttachmentsJson);
            const initialAnalysisState = @json((object) $analysisStateInitial);
            const analysisStateInput = document.getElementById('vetExamReportAnalysisState');
            const reportForm = document.querySelector('.vet-exams-report__form');
            const attachmentMap = new Map((attachments || []).map(function (item) {
                return [String(item.id), item];
            }));
            const debugChannel = (window.vetExamAnalysisDebug = window.vetExamAnalysisDebug || {});
            let initialStateNormalized = null;
            let activeAttachmentId = null;
            let isRestoringAnalysis = false;

            if (initialAnalysisState && typeof initialAnalysisState === 'object') {
                initialStateNormalized = normalizeAnalysisState(initialAnalysisState);
            }

            let analysisState =
                initialStateNormalized && Object.keys(initialStateNormalized).length > 0
                    ? cloneAnalysisState(initialStateNormalized)
                    : {};

            if (analysisStateInput && typeof analysisStateInput.value === 'string') {
                const rawValue = analysisStateInput.value;
                const trimmedValue = rawValue.trim();

                console.debug('[VetExam] Valor bruto encontrado no input oculto.', {
                    raw: rawValue,
                    trimmed: trimmedValue,
                    length: rawValue.length,
                });

                if (trimmedValue !== '') {
                    const parseResult = tryParseAnalysisState(trimmedValue);

                    if (parseResult.parsed && typeof parseResult.parsed === 'object') {
                        analysisState = normalizeAnalysisState(parseResult.parsed);
                        console.debug('[VetExam] Estado normalizado a partir do input oculto.', {
                            origem: parseResult.source,
                            estado: analysisState,
                        });
                    } else {
                        console.error('[VetExam] Falha ao interpretar o estado do input oculto.', parseResult);

                        if (initialStateNormalized && Object.keys(initialStateNormalized).length > 0) {
                            analysisState = cloneAnalysisState(initialStateNormalized);
                            console.debug('[VetExam] Estado inicial reaplicado após falha na leitura do input oculto.');
                        } else {
                            analysisState = {};
                        }
                    }
                } else if (initialStateNormalized && Object.keys(initialStateNormalized).length > 0) {
                    console.debug('[VetExam] Input oculto vazio, utilizando estado inicial do backend.');
                    analysisState = cloneAnalysisState(initialStateNormalized);
                }
            }

            if (analysisStateInput) {
                analysisStateInput.value = JSON.stringify(analysisState);
                console.debug('[VetExam] Estado persistido no input oculto após normalização.', analysisStateInput.value);
            }

            debugChannel.initialState = initialAnalysisState;
            debugChannel.attachments = attachments;
            debugChannel.input = analysisStateInput;
            debugChannel.initialStateNormalized = initialStateNormalized;
            debugChannel.getState = function () {
                return analysisState;
            };
            debugChannel.logState = function () {
                console.debug('[VetExam] Estado atual completo.', analysisState);
            };

            if (initialStateNormalized) {
                console.debug('[VetExam] Estado inicial normalizado proveniente do backend.', initialStateNormalized);
            }

            console.debug('[VetExam] Estado corrente das análises no carregamento.', analysisState);

            console.info('[VetExam] Estado de análise carregado para o laudo.', {
                examId: {{ $exam->id }},
                attachmentKeys: Object.keys(analysisState),
            });

            if (!viewerElement) {
                return;
            }

            cornerstone.enable(viewerElement);
            cornerstoneTools.addStackStateManager(viewerElement, ['stack']);

            const measurementTools = ['Length', 'RectangleRoi', 'EllipticalRoi', 'FreehandRoi', 'ArrowAnnotate'];
            const baseTools = ['Wwwc', 'Pan', 'Zoom'];

            cornerstoneTools.addTool(cornerstoneTools.WwwcTool);
            cornerstoneTools.addTool(cornerstoneTools.PanTool);
            cornerstoneTools.addTool(cornerstoneTools.ZoomTool, {
                configuration: {
                    invert: false,
                    preventZoomOutsideImage: false,
                },
            });
            cornerstoneTools.addTool(cornerstoneTools.LengthTool);
            cornerstoneTools.addTool(cornerstoneTools.RectangleRoiTool);
            cornerstoneTools.addTool(cornerstoneTools.EllipticalRoiTool);
            cornerstoneTools.addTool(cornerstoneTools.FreehandRoiTool);
            cornerstoneTools.addTool(cornerstoneTools.ArrowAnnotateTool, {
                configuration: {
                    textBox: {
                        drawHandles: true,
                        hasBoundingBox: true,
                    },
                },
            });

            function applyDefaultBindings() {
                cornerstoneTools.setToolActive('Wwwc', { mouseButtonMask: 1 });
                cornerstoneTools.setToolActive('Pan', { mouseButtonMask: 4 });
                cornerstoneTools.setToolActive('Zoom', { mouseButtonMask: 2 });
                measurementTools.forEach(function (toolName) {
                    cornerstoneTools.setToolPassive(toolName);
                });
            }

            applyDefaultBindings();
            updateToolbarButtons('Wwwc');

            function activateTool(toolName) {
                if (!toolName) {
                    return;
                }

                measurementTools.forEach(function (name) {
                    if (name === toolName) {
                        cornerstoneTools.setToolActive(name, { mouseButtonMask: 1 });
                    } else {
                        cornerstoneTools.setToolPassive(name);
                    }
                });

                if (toolName === 'Wwwc') {
                    cornerstoneTools.setToolActive('Wwwc', { mouseButtonMask: 1 });
                }

                cornerstoneTools.setToolActive('Pan', { mouseButtonMask: 4 });
                cornerstoneTools.setToolActive('Zoom', { mouseButtonMask: 2 });
                activeToolLabel.textContent = translateToolName(toolName);
                updateToolbarButtons(toolName);
            }

            function translateToolName(toolName) {
                const labels = {
                    Wwwc: 'Janela/Nível',
                    Length: 'Medir distância',
                    RectangleRoi: 'ROI retangular',
                    EllipticalRoi: 'ROI elíptica',
                    FreehandRoi: 'ROI livre',
                    ArrowAnnotate: 'Anotar',
                };

                return labels[toolName] || toolName;
            }

            function updateToolbarButtons(activeTool) {
                toolbarButtons.forEach(function (button) {
                    const toolName = button.dataset.tool;
                    if (!toolName) {
                        button.classList.remove('active');
                        return;
                    }

                    if (toolName === activeTool) {
                        button.classList.add('active');
                    } else {
                        button.classList.remove('active');
                    }
                });
            }

            function updateAnalysisInput() {
                if (!analysisStateInput) {
                    return;
                }

                try {
                    analysisState = normalizeAnalysisState(analysisState);
                    analysisStateInput.value = JSON.stringify(analysisState);
                    debugChannel.getState = function () {
                        return analysisState;
                    };
                    console.debug('[VetExam] Estado de análise atualizado.', {
                        attachmentKeys: Object.keys(analysisState),
                    });
                } catch (error) {
                    // ignore serialization issues
                }
            }

            function sanitizeViewport(viewport) {
                if (!viewport || typeof viewport !== 'object') {
                    return {};
                }

                const sanitized = {};

                if (typeof viewport.scale === 'number') {
                    sanitized.scale = viewport.scale;
                }

                if (viewport.translation && typeof viewport.translation === 'object') {
                    const translation = {};

                    if (typeof viewport.translation.x === 'number') {
                        translation.x = viewport.translation.x;
                    }

                    if (typeof viewport.translation.y === 'number') {
                        translation.y = viewport.translation.y;
                    }

                    if (Object.keys(translation).length > 0) {
                        sanitized.translation = translation;
                    }
                }

                if (viewport.voi && typeof viewport.voi === 'object') {
                    const voi = {};

                    if (typeof viewport.voi.windowWidth === 'number') {
                        voi.windowWidth = viewport.voi.windowWidth;
                    }

                    if (typeof viewport.voi.windowCenter === 'number') {
                        voi.windowCenter = viewport.voi.windowCenter;
                    }

                    if (Object.keys(voi).length > 0) {
                        sanitized.voi = voi;
                    }
                }

                if (typeof viewport.rotation === 'number') {
                    sanitized.rotation = viewport.rotation;
                }

                if (typeof viewport.invert === 'boolean') {
                    sanitized.invert = viewport.invert;
                }

                if (typeof viewport.hflip === 'boolean') {
                    sanitized.hflip = viewport.hflip;
                }

                if (typeof viewport.vflip === 'boolean') {
                    sanitized.vflip = viewport.vflip;
                }

                return sanitized;
            }

            function cloneMeasurementEntry(entry) {
                try {
                    return JSON.parse(JSON.stringify(entry));
                } catch (error) {
                    console.error('[VetExam] Falha ao clonar medição recuperada.', error, entry);
                    return null;
                }
            }

            function prepareMeasurementForRestore(toolName, entry) {
                const clone = cloneMeasurementEntry(entry);

                if (!clone || typeof clone !== 'object') {
                    console.warn('[VetExam] Medição ignorada por não ser clonável.', { toolName: toolName, entry: entry });
                    return null;
                }

                clone.toolType = toolName;

                clone.invalidated = false;

                if (typeof clone.visible !== 'boolean') {
                    clone.visible = true;
                }

                if (!clone.metadata || typeof clone.metadata !== 'object' || Array.isArray(clone.metadata)) {
                    clone.metadata = {};
                    console.debug('[VetExam] Metadados da medição ajustados para objeto vazio.', {
                        toolName: toolName,
                        uuid: clone.uuid,
                    });
                }

                if (clone.handles && typeof clone.handles === 'object') {
                    Object.keys(clone.handles).forEach(function (handleKey) {
                        const handle = clone.handles[handleKey];

                        if (!handle || typeof handle !== 'object') {
                            delete clone.handles[handleKey];
                            return;
                        }

                        if (typeof handle.highlight !== 'boolean') {
                            handle.highlight = false;
                        }

                        if (typeof handle.active !== 'boolean') {
                            handle.active = false;
                        }
                    });
                } else {
                    clone.handles = {};
                }

                const currentImage = cornerstone.getEnabledElement(viewerElement)?.image;

                if (currentImage && typeof currentImage.imageId === 'string' && !clone.imageId) {
                    clone.imageId = currentImage.imageId;
                }

                console.debug('[VetExam] Medição pronta para restauração.', {
                    toolName: toolName,
                    uuid: clone.uuid,
                    text: clone?.data?.text || clone.text,
                    handles: clone.handles,
                });

                return clone;
            }

            function normalizeStoredToolEntries(entries) {
                if (Array.isArray(entries)) {
                    const filtered = entries.filter(function (item, index) {
                        const isValid = item && typeof item === 'object';

                        if (!isValid) {
                            console.warn('[VetExam] Medição descartada por tipo inesperado dentro do array.', {
                                index: index,
                                entry: item,
                            });
                        }

                        return isValid;
                    });

                    console.debug('[VetExam] Medições normalizadas a partir de array.', {
                        quantidadeOriginal: entries.length,
                        quantidadeFiltrada: filtered.length,
                    });

                    return filtered;
                }

                if (!entries || typeof entries !== 'object') {
                    console.warn('[VetExam] Bloco de medições ignorado por não ser objeto ou array.', {
                        entries: entries,
                    });
                    return [];
                }

                if (entries.handles || entries.toolType || entries.cachedStats || entries.uuid) {
                    console.debug('[VetExam] Medição envolta em objeto simples convertida em array único.');
                    return [entries];
                }

                const values = Object.values(entries).filter(function (item) {
                    return item && typeof item === 'object';
                });

                if (values.length === 0) {
                    console.warn('[VetExam] Nenhuma medição válida encontrada ao normalizar estado armazenado.', {
                        entries: entries,
                    });
                    return [];
                }

                if (
                    values.length === 1 &&
                    (values[0].handles || values[0].toolType || values[0].cachedStats || values[0].uuid)
                ) {
                    console.debug('[VetExam] Medição isolada detectada em objeto de valores.');
                    return values;
                }

                console.debug('[VetExam] Conversão de objeto de medições para array.', {
                    quantidade: values.length,
                });
                return values;
            }

            function cloneAnalysisState(state) {
                if (!state || typeof state !== 'object') {
                    return {};
                }

                if (typeof structuredClone === 'function') {
                    try {
                        return structuredClone(state);
                    } catch (error) {
                        console.warn('[VetExam] Falha ao clonar estado usando structuredClone. Tentando fallback.', {
                            error: error,
                        });
                    }
                }

                try {
                    return JSON.parse(JSON.stringify(state));
                } catch (error) {
                    console.warn('[VetExam] Falha ao clonar estado via JSON. Retornando cópia rasa.', {
                        error: error,
                    });

                    return Object.assign({}, state);
                }
            }

            function tryParseAnalysisState(rawValue) {
                const attempts = [];
                let groupOpened = false;

                function ensureGroup(meta) {
                    if (!groupOpened) {
                        console.groupCollapsed('[VetExam] Tentando interpretar o estado serializado das análises');
                        groupOpened = true;
                    }

                    if (meta) {
                        console.debug('[VetExam] Detalhes iniciais do valor recebido.', meta);
                    }
                }

                if (typeof rawValue !== 'string') {
                    ensureGroup({ tipo: typeof rawValue });
                    console.warn('[VetExam] Valor recebido para interpretação não é uma string.');
                    if (groupOpened) {
                        console.groupEnd();
                    }
                    return { parsed: null, source: null, attempts: attempts };
                }

                const trimmed = rawValue.trim();
                ensureGroup({ comprimento: rawValue.length, amostra: rawValue.slice(0, 200) });

                if (trimmed === '') {
                    console.warn('[VetExam] Valor recebido está vazio após trim.');
                    if (groupOpened) {
                        console.groupEnd();
                    }
                    return { parsed: null, source: null, attempts: attempts };
                }

                function pushAttempt(label, value) {
                    if (typeof value !== 'string') {
                        return;
                    }

                    const candidate = value.trim();

                    if (candidate === '') {
                        return;
                    }

                    ensureGroup();
                    const attempt = { label: label, value: candidate };
                    attempts.push(attempt);
                    console.debug('[VetExam] Tentativa registrada para interpretar o estado.', {
                        origem: label,
                        comprimento: candidate.length,
                        amostra: candidate.slice(0, 200),
                    });
                }

                pushAttempt('raw', trimmed);

                const textarea = document.createElement('textarea');
                textarea.innerHTML = trimmed;
                const htmlDecoded = textarea.value;

                if (htmlDecoded !== trimmed) {
                    pushAttempt('html-decoded', htmlDecoded);
                }

                if (
                    htmlDecoded.startsWith('"') &&
                    htmlDecoded.endsWith('"') &&
                    htmlDecoded.length >= 2
                ) {
                    const unwrapped = htmlDecoded.slice(1, -1).replace(/\\"/g, '"');
                    pushAttempt('unwrapped', unwrapped);
                }

                const singleQuotedKeys = htmlDecoded.replace(/([{,]\s*)'([^']+?)'\s*:/g, '$1"$2":');

                if (singleQuotedKeys !== htmlDecoded) {
                    pushAttempt('single-quoted-keys', singleQuotedKeys);
                }

                const safePattern = /^[\s0-9A-Za-z_{}[\]:.,'"-]+$/;

                for (const attempt of attempts) {
                    try {
                        const parsed = JSON.parse(attempt.value);
                        console.info('[VetExam] Estado interpretado com JSON.parse.', { origem: attempt.label });
                        if (groupOpened) {
                            console.groupEnd();
                        }
                        return { parsed: parsed, source: attempt.label, attempts: attempts };
                    } catch (error) {
                        attempt.error = error;
                        console.warn('[VetExam] Falha ao interpretar com JSON.parse.', {
                            origem: attempt.label,
                            erro: error,
                        });
                    }
                }

                for (const attempt of attempts) {
                    if (!safePattern.test(attempt.value)) {
                        console.debug('[VetExam] Tentativa ignorada para eval por conter caracteres suspeitos.', {
                            origem: attempt.label,
                        });
                        continue;
                    }

                    try {
                        const parsed = new Function('"use strict";return (' + attempt.value + ');')();
                        console.info('[VetExam] Estado interpretado via avaliação controlada.', {
                            origem: attempt.label,
                        });
                        if (groupOpened) {
                            console.groupEnd();
                        }
                        return {
                            parsed: parsed,
                            source: attempt.label + '-evaluated',
                            attempts: attempts,
                        };
                    } catch (error) {
                        attempt.evalError = error;
                        console.warn('[VetExam] Falha ao interpretar via avaliação controlada.', {
                            origem: attempt.label,
                            erro: error,
                        });
                    }
                }

                console.error('[VetExam] Nenhuma tentativa conseguiu interpretar o estado serializado.', {
                    totalTentativas: attempts.length,
                });

                if (groupOpened) {
                    console.groupEnd();
                }

                return { parsed: null, source: null, attempts: attempts };
            }

            function normalizeAnalysisState(state) {
                if (!state || typeof state !== 'object') {
                    return {};
                }

                const normalized = {};

                Object.keys(state).forEach(function (attachmentKey) {
                    const entry = state[attachmentKey];

                    if (!entry || typeof entry !== 'object') {
                        console.debug('[VetExam] Entrada de análise ignorada por não ser objeto.', {
                            attachmentKey: attachmentKey,
                        });
                        return;
                    }

                    const normalizedEntry = {};

                    if (entry.tool_states && typeof entry.tool_states === 'object') {
                        const normalizedToolStates = {};

                        Object.keys(entry.tool_states).forEach(function (toolName) {
                            const normalizedEntries = normalizeStoredToolEntries(entry.tool_states[toolName]);

                            if (normalizedEntries.length > 0) {
                                normalizedToolStates[toolName] = normalizedEntries;
                            }
                        });

                        if (Object.keys(normalizedToolStates).length > 0) {
                            normalizedEntry.tool_states = normalizedToolStates;
                        }
                    }

                    if (entry.viewport && typeof entry.viewport === 'object') {
                        if (Object.keys(entry.viewport).length > 0) {
                            normalizedEntry.viewport = entry.viewport;
                        }
                    }

                    if (Object.keys(normalizedEntry).length > 0) {
                        normalized[attachmentKey] = normalizedEntry;
                        console.debug('[VetExam] Entrada de análise normalizada adicionada.', {
                            attachmentKey: attachmentKey,
                            temFerramentas: Boolean(normalizedEntry.tool_states),
                            temViewport: Boolean(normalizedEntry.viewport),
                        });
                    }
                });

                console.debug('[VetExam] Resultado completo da normalização do estado de análise.', {
                    chaves: Object.keys(normalized),
                    total: Object.keys(normalized).length,
                });
                return normalized;
            }

            function serializeMeasurementEntry(entry) {
                const visited = new WeakSet();

                function walk(value) {
                    if (value === null || value === undefined) {
                        return null;
                    }

                    const valueType = typeof value;

                    if (valueType === 'string' || valueType === 'boolean') {
                        return value;
                    }

                    if (valueType === 'number') {
                        if (Number.isNaN(value) || !Number.isFinite(value)) {
                            return null;
                        }

                        return value;
                    }

                    if (Array.isArray(value)) {
                        const serializedArray = value
                            .map(function (item) {
                                return walk(item);
                            })
                            .filter(function (item) {
                                if (item === null) {
                                    return false;
                                }

                                if (Array.isArray(item)) {
                                    return item.length > 0;
                                }

                                if (typeof item === 'object') {
                                    return Object.keys(item).length > 0;
                                }

                                return true;
                            });

                        return serializedArray.length > 0 ? serializedArray : [];
                    }

                    if (value instanceof Date) {
                        return value.toISOString();
                    }

                    if (valueType === 'object') {
                        if (visited.has(value)) {
                            return null;
                        }

                        visited.add(value);

                        const result = {};

                        Object.keys(value).forEach(function (key) {
                            const serialized = walk(value[key]);

                            if (serialized === null) {
                                return;
                            }

                            if (Array.isArray(serialized) && serialized.length === 0) {
                                if (Array.isArray(value[key]) && value[key].length === 0) {
                                    result[key] = [];
                                }

                                return;
                            }

                            if (typeof serialized === 'object' && !Array.isArray(serialized) && Object.keys(serialized).length === 0) {
                                return;
                            }

                            result[key] = serialized;
                        });

                        return Object.keys(result).length > 0 ? result : null;
                    }

                    return null;
                }

                return walk(entry);
            }

            function captureToolStates() {
                const result = {};

                measurementTools.forEach(function (toolName) {
                    const state = cornerstoneTools.getToolState(viewerElement, toolName);

                    if (!state || !Array.isArray(state.data) || state.data.length === 0) {
                        console.debug('[VetExam] Nenhuma medição ativa para a ferramenta.', {
                            toolName: toolName,
                        });
                        return;
                    }

                    const entries = state.data
                        .map(function (item) {
                            return serializeMeasurementEntry(item);
                        })
                        .filter(function (item) {
                            return item !== null;
                        });

                    if (entries.length > 0) {
                        result[toolName] = entries;
                        console.debug('[VetExam] Medições capturadas para ferramenta.', {
                            toolName: toolName,
                            count: entries.length,
                            entries: entries,
                        });
                    }
                });

                console.debug('[VetExam] Resultado consolidado da captura de medições.', result);
                return result;
            }

            function saveCurrentAttachmentAnalysis() {
                if (isRestoringAnalysis) {
                    console.debug('[VetExam] Salvamento ignorado enquanto restauração está em andamento.');
                    return;
                }

                if (!activeAttachmentId) {
                    return;
                }

                const toolStates = captureToolStates();
                const viewport = cornerstone.getViewport(viewerElement);
                const viewportState = sanitizeViewport(viewport);
                const hasToolStates = Object.values(toolStates).some(function (entries) {
                    return Array.isArray(entries) && entries.length > 0;
                });
                const hasViewportState = Object.keys(viewportState).length > 0;
                const attachmentKey = String(activeAttachmentId);

                if (!hasToolStates && !hasViewportState) {
                    delete analysisState[attachmentKey];
                    updateAnalysisInput();
                    console.info('[VetExam] Estado limpo para o anexo por não possuir medições nem viewport.', {
                        attachmentId: attachmentKey,
                    });
                    return;
                }

                analysisState[attachmentKey] = {
                    tool_states: toolStates,
                    viewport: viewportState,
                };

                updateAnalysisInput();
                console.info('[VetExam] Estado salvo para o anexo ativo.', {
                    attachmentId: attachmentKey,
                    toolStateKeys: Object.keys(toolStates),
                    viewportState: viewportState,
                });
                console.debug('[VetExam] Resumo detalhado do estado salvo.', {
                    attachmentId: attachmentKey,
                    temFerramentas: hasToolStates,
                    temViewport: hasViewportState,
                    totalMedições: Object.values(toolStates).reduce(function (total, items) {
                        return total + (Array.isArray(items) ? items.length : 0);
                    }, 0),
                });
            }

            function persistViewportState() {
                if (isRestoringAnalysis) {
                    console.debug('[VetExam] Persistência de viewport ignorada durante restauração.');
                    return;
                }

                if (!activeAttachmentId) {
                    return;
                }

                const viewport = cornerstone.getViewport(viewerElement);

                if (!viewport) {
                    return;
                }

                const viewportState = sanitizeViewport(viewport);
                const attachmentKey = String(activeAttachmentId);
                const existingState = analysisState[attachmentKey] || {};
                const hasViewportState = Object.keys(viewportState).length > 0;
                const hasToolStates = existingState.tool_states && Object.keys(existingState.tool_states).length > 0;

                if (!hasViewportState) {
                    if (hasToolStates) {
                        analysisState[attachmentKey] = {
                            tool_states: existingState.tool_states,
                        };
                    } else {
                        delete analysisState[attachmentKey];
                    }

                    updateAnalysisInput();
                    console.debug('[VetExam] Estado do viewport removido por ausência de dados.', {
                        attachmentId: attachmentKey,
                    });
                    return;
                }

                analysisState[attachmentKey] = {
                    tool_states: existingState.tool_states || {},
                    viewport: viewportState,
                };

                updateAnalysisInput();
                console.debug('[VetExam] Estado do viewport persistido.', {
                    attachmentId: attachmentKey,
                    viewport: viewportState,
                });
                console.debug('[VetExam] Estado completo do anexo após persistir viewport.', {
                    attachmentId: attachmentKey,
                    estado: analysisState[attachmentKey],
                });
            }

            function restoreAnalysisForAttachment(attachmentId) {
                const attachmentKey = String(attachmentId);
                const state = analysisState[attachmentKey];

                console.groupCollapsed(`[VetExam] Restaurando análise para anexo ${attachmentKey}`);
                console.debug('Estado bruto recuperado:', state);

                try {
                    resetAnnotationsPanel();
                    measurementTools.forEach(function (toolName) {
                        cornerstoneTools.clearToolState(viewerElement, toolName);
                    });

                    if (!state) {
                        cornerstone.updateImage(viewerElement);
                        updateViewportStatus(cornerstone.getEnabledElement(viewerElement)?.image);
                        console.info('[VetExam] Nenhum estado salvo para o anexo selecionado.');
                        return;
                    }

                    const toolStates = state.tool_states || {};
                    console.debug('[VetExam] Estado de ferramentas encontrado para restauração.', toolStates);
                    const restoredMeasurements = [];

                    Object.keys(toolStates).forEach(function (toolName) {
                        const entries = normalizeStoredToolEntries(toolStates[toolName]);

                        if (!Array.isArray(entries) || entries.length === 0) {
                            console.warn('[VetExam] Nenhuma medição válida disponível após normalização.', {
                                toolName: toolName,
                                rawEntries: toolStates[toolName],
                            });
                            return;
                        }

                        entries.forEach(function (entry) {
                            const prepared = prepareMeasurementForRestore(toolName, entry);

                            if (!prepared) {
                                console.warn('[VetExam] Medição ignorada por estar inválida.', {
                                    toolName: toolName,
                                    entry: entry,
                                });
                                return;
                            }

                            restoredMeasurements.push({ toolName: toolName, entry: prepared });
                            cornerstoneTools.addToolState(viewerElement, toolName, prepared);
                        });
                    });

                    const viewportState = state.viewport || {};
                    const currentViewport = cornerstone.getViewport(viewerElement);

                    if (currentViewport && typeof currentViewport === 'object') {
                        const nextViewport = { ...currentViewport };

                        if (typeof viewportState.scale === 'number') {
                            nextViewport.scale = viewportState.scale;
                        }

                        if (viewportState.translation && typeof viewportState.translation === 'object') {
                            nextViewport.translation = {
                                x: typeof viewportState.translation.x === 'number'
                                    ? viewportState.translation.x
                                    : currentViewport.translation?.x || 0,
                                y: typeof viewportState.translation.y === 'number'
                                    ? viewportState.translation.y
                                    : currentViewport.translation?.y || 0,
                            };
                        }

                        if (viewportState.voi && typeof viewportState.voi === 'object') {
                            nextViewport.voi = {
                                windowWidth: typeof viewportState.voi.windowWidth === 'number'
                                    ? viewportState.voi.windowWidth
                                    : currentViewport.voi?.windowWidth,
                                windowCenter: typeof viewportState.voi.windowCenter === 'number'
                                    ? viewportState.voi.windowCenter
                                    : currentViewport.voi?.windowCenter,
                            };
                        }

                        if (typeof viewportState.rotation === 'number') {
                            nextViewport.rotation = viewportState.rotation;
                        }

                        if (typeof viewportState.invert === 'boolean') {
                            nextViewport.invert = viewportState.invert;
                        }

                        if (typeof viewportState.hflip === 'boolean') {
                            nextViewport.hflip = viewportState.hflip;
                        }

                        if (typeof viewportState.vflip === 'boolean') {
                            nextViewport.vflip = viewportState.vflip;
                        }

                        cornerstone.setViewport(viewerElement, nextViewport);
                    }

                    cornerstone.updateImage(viewerElement);
                    renderAnnotations();
                    updateViewportStatus(cornerstone.getEnabledElement(viewerElement)?.image);
                    console.info('[VetExam] Medições restauradas para o anexo.', {
                        attachmentId: attachmentKey,
                        restoredCount: restoredMeasurements.length,
                        tools: restoredMeasurements.map(function (item) {
                            return item.toolName;
                        }),
                        viewportApplied: Object.keys(viewportState).length > 0,
                        restoredMeasurements: restoredMeasurements,
                    });
                    console.debug('[VetExam] Estado final após restauração.', {
                        attachmentId: attachmentKey,
                        estado: analysisState[attachmentKey],
                    });
                } finally {
                    console.groupEnd();
                }
            }

            function updateOverlay(message, meta) {
                overlayTitle.textContent = message || '';
                overlayMeta.textContent = meta || '';
                console.debug('[VetExam] Overlay atualizado.', {
                    titulo: message,
                    meta: meta,
                });
            }

            function updateViewportStatus(image) {
                if (!image) {
                    zoomLabel.textContent = '1.0x';
                    windowLabel.textContent = '—';
                    console.debug('[VetExam] Status do viewport atualizado sem imagem ativa.');
                    return;
                }

                const viewport = cornerstone.getViewport(viewerElement);
                const scale = typeof viewport.scale === 'number' ? viewport.scale : 1;
                zoomLabel.textContent = `${scale.toFixed(2)}x`;

                const windowWidth = viewport?.voi?.windowWidth;
                const windowCenter = viewport?.voi?.windowCenter;
                const formattedWidth = typeof windowWidth === 'number' ? windowWidth.toFixed(0) : '--';
                const formattedCenter = typeof windowCenter === 'number' ? windowCenter.toFixed(0) : '--';
                windowLabel.textContent = `${formattedWidth} / ${formattedCenter}`;
                console.debug('[VetExam] Status do viewport atualizado com imagem.', {
                    scale: scale,
                    windowWidth: formattedWidth,
                    windowCenter: formattedCenter,
                });
            }

            const fullscreenTarget = viewerWrapper || viewerContainer;

            function isFullscreenActive() {
                if (!fullscreenTarget) {
                    return false;
                }

                const fullscreenElement =
                    document.fullscreenElement ||
                    document.webkitFullscreenElement ||
                    document.mozFullScreenElement ||
                    document.msFullscreenElement;

                return fullscreenElement === fullscreenTarget;
            }

            function requestFullscreen(element) {
                const target = element || fullscreenTarget;

                if (!target) {
                    return;
                }

                const request =
                    target.requestFullscreen ||
                    target.webkitRequestFullscreen ||
                    target.mozRequestFullScreen ||
                    target.msRequestFullscreen;

                if (request) {
                    request.call(target);
                }
            }

            function exitFullscreen() {
                const exit =
                    document.exitFullscreen ||
                    document.webkitExitFullscreen ||
                    document.mozCancelFullScreen ||
                    document.msExitFullscreen;

                if (exit) {
                    exit.call(document);
                }
            }

            function updateFullscreenButtonState() {
                if (!fullscreenButton) {
                    return;
                }

                const active = isFullscreenActive();
                fullscreenButton.classList.toggle('active', active);

                if (fullscreenIcon) {
                    fullscreenIcon.classList.toggle('ri-fullscreen-line', !active);
                    fullscreenIcon.classList.toggle('ri-fullscreen-exit-line', active);
                }

                if (fullscreenLabel) {
                    fullscreenLabel.textContent = active ? 'Sair tela cheia' : 'Tela cheia';
                }

                if (viewerElement) {
                    cornerstone.resize(viewerElement, true);
                    setTimeout(function () {
                        cornerstone.resize(viewerElement, true);
                    }, 150);
                }

                console.debug('[VetExam] Estado do botão de tela cheia atualizado.', {
                    ativo: active,
                });
            }

            ['fullscreenchange', 'webkitfullscreenchange', 'mozfullscreenchange', 'MSFullscreenChange'].forEach(function (eventName) {
                document.addEventListener(eventName, updateFullscreenButtonState);
            });

            function resetAnnotationsPanel() {
                annotationsList.innerHTML = '<div class="dicom-annotations__empty">Nenhuma anotação registrada até o momento. Utilize as ferramentas ao lado para marcar pontos de interesse.</div>';
                console.debug('[VetExam] Painel de anotações redefinido para estado vazio.');
            }

            function renderAnnotations() {
                const items = [];

                measurementTools.forEach(function (toolName) {
                    const state = cornerstoneTools.getToolState(viewerElement, toolName);
                    if (!state || !state.data) {
                        console.debug('[VetExam] Nenhuma anotação ativa para ferramenta durante renderização.', {
                            toolName: toolName,
                        });
                        return;
                    }

                    state.data.forEach(function (entry, index) {
                        const metaParts = [];

                        if (entry && entry.cachedStats) {
                            Object.values(entry.cachedStats).forEach(function (stat) {
                                if (stat && typeof stat.text === 'string' && stat.text.trim() !== '') {
                                    metaParts.push(stat.text);
                                }
                            });
                        }

                        const descriptionParts = [];
                        const textContent = entry?.data?.text || entry?.text;

                        if (textContent && textContent.trim() !== '') {
                            descriptionParts.push(textContent.trim());
                        }

                        if (metaParts.length > 0) {
                            descriptionParts.push(metaParts.join(' • '));
                        }

                        if (descriptionParts.length === 0) {
                            descriptionParts.push('Sem comentários adicionais.');
                        }

                        items.push({
                            title: `${translateToolName(toolName)} #${index + 1}`,
                            description: descriptionParts.join(' \u2022 '),
                        });
                        console.debug('[VetExam] Anotação preparada para exibição.', {
                            ferramenta: toolName,
                            indice: index,
                            titulo: `${translateToolName(toolName)} #${index + 1}`,
                            descricao: descriptionParts.join(' \u2022 '),
                        });
                    });
                });

                if (items.length === 0) {
                    resetAnnotationsPanel();
                    console.debug('[VetExam] Nenhuma anotação para exibir após processamento.');
                    return;
                }

                annotationsList.innerHTML = '';

                items.forEach(function (item) {
                    const wrapper = document.createElement('div');
                    wrapper.className = 'dicom-annotations__item';

                    const title = document.createElement('div');
                    title.className = 'dicom-annotations__item-title';
                    title.textContent = item.title;

                    const description = document.createElement('div');
                    description.className = 'dicom-annotations__item-meta mt-2';
                    description.textContent = item.description;

                    wrapper.appendChild(title);
                    wrapper.appendChild(description);
                    annotationsList.appendChild(wrapper);
                });

                console.info('[VetExam] Painel de anotações renderizado.', {
                    quantidade: items.length,
                });
            }

            function promptAnnotationText(event) {
                const { toolType, measurementData } = event.detail || {};

                if (toolType !== 'ArrowAnnotate' || !measurementData) {
                    console.debug('[VetExam] Evento de anotação ignorado por não ser ArrowAnnotate ou sem dados.', {
                        toolType: toolType,
                        measurementData: Boolean(measurementData),
                    });
                    return;
                }

                const note = window.prompt('Descreva a anotação criada:', measurementData?.data?.text || '');

                if (note !== null) {
                    measurementData.data = measurementData.data || {};
                    measurementData.data.text = note.trim();
                    measurementData.text = measurementData.data.text;
                    cornerstone.updateImage(viewerElement);
                    saveCurrentAttachmentAnalysis();
                    console.info('[VetExam] Texto de anotação atualizado a partir do prompt.', {
                        texto: measurementData.data.text,
                    });
                } else {
                    console.debug('[VetExam] Usuário cancelou o prompt de anotação.');
                }
            }

            function clearToolStates() {
                cornerstoneTools.clearToolState(viewerElement, 'stack');
                measurementTools.concat(baseTools).forEach(function (toolName) {
                    cornerstoneTools.clearToolState(viewerElement, toolName);
                });
                resetAnnotationsPanel();
                console.debug('[VetExam] Todos os estados de ferramenta e painel foram limpos.');
            }

            function loadAttachment(element) {
                if (!element) {
                    console.warn('[VetExam] Tentativa de carregar anexo sem elemento fornecido.');
                    return;
                }

                if (activeAttachmentId) {
                    saveCurrentAttachmentAnalysis();
                }

                const attachmentId = element.dataset.attachmentId;
                const attachmentData = attachmentMap.get(String(attachmentId));
                const isSupported = element.dataset.isSupported === '1';
                const url = element.dataset.url;
                const isDicom = element.dataset.isDicom === '1';
                const name = element.dataset.name || 'Documento';
                const metaParts = [];

                activeAttachmentId = null;
                isRestoringAnalysis = true;

                console.groupCollapsed('[VetExam] Iniciando carregamento de anexo no viewer.');
                console.debug('[VetExam] Metadados básicos do elemento selecionado.', {
                    attachmentId: attachmentId,
                    url: url,
                    isDicom: isDicom,
                    isSupported: isSupported,
                    name: name,
                });

                if (attachmentData?.type_label) {
                    metaParts.push(attachmentData.type_label);
                }

                if (attachmentData?.size) {
                    metaParts.push(`Tamanho ${attachmentData.size}`);
                }

                if (attachmentData?.uploaded_at) {
                    metaParts.push(`Enviado em ${attachmentData.uploaded_at}`);
                }

                if (attachmentData?.uploaded_by) {
                    metaParts.push(`Por ${attachmentData.uploaded_by}`);
                }

                attachmentList.forEach(function (item) {
                    item.classList.remove('is-active');
                });

                element.classList.add('is-active');

                if (!isSupported || !url) {
                    cornerstone.reset(viewerElement);
                    const unsupportedMeta = metaParts.length > 0
                        ? metaParts.join(' • ')
                        : 'Faça o download do arquivo para analisá-lo.';
                    updateOverlay('Formato não suportado para visualização.', unsupportedMeta);
                    activeAttachmentLabel.textContent = name;
                    resetAnnotationsPanel();
                    updateAnalysisInput();
                    console.warn('[VetExam] Anexo selecionado não possui suporte para visualização.', {
                        attachmentId: attachmentId,
                    });
                    isRestoringAnalysis = false;
                    console.groupEnd();
                    return;
                }

                const loadingMeta = metaParts.length > 0 ? metaParts.join(' • ') : (isDicom ? 'Documento DICOM' : 'Imagem');
                updateOverlay('Carregando documento selecionado…', loadingMeta);
                const imageId = isDicom ? `wadouri:${url}` : url;

                clearToolStates();

                console.debug('[VetExam] Solicitando carregamento do documento.', {
                    imageId: imageId,
                    attachmentId: attachmentId,
                });

                try {
                    cornerstone
                        .loadAndCacheImage(imageId)
                        .then(function (image) {
                            activeAttachmentId = String(attachmentId);
                            cornerstone.displayImage(viewerElement, image);
                            cornerstoneTools.addToolState(viewerElement, 'stack', {
                                imageIds: [imageId],
                                currentImageIdIndex: 0,
                            });
                            cornerstone.reset(viewerElement);
                            applyDefaultBindings();
                            activateTool('Wwwc');
                            const metaDescription = metaParts.length > 0 ? metaParts.join(' • ') : (isDicom ? 'Documento DICOM' : 'Imagem carregada');
                            updateOverlay(name, metaDescription);
                            activeAttachmentLabel.textContent = name;

                            try {
                                restoreAnalysisForAttachment(activeAttachmentId);
                            } catch (restoreError) {
                                console.error('[VetExam] Falha ao restaurar medições para o anexo carregado.', restoreError);
                            } finally {
                                isRestoringAnalysis = false;
                            }

                            updateViewportStatus(image);
                            console.info('[VetExam] Documento carregado com sucesso.', {
                                attachmentId: attachmentId,
                                imageId: imageId,
                                dimensoes: { width: image.width, height: image.height },
                            });
                            console.groupEnd();
                        })
                        .catch(function (error) {
                            console.error('Não foi possível carregar o documento selecionado.', error);
                            updateOverlay('Erro ao carregar o documento.', 'Verifique se o arquivo está disponível e tente novamente.');
                            activeAttachmentLabel.textContent = name;
                            activeAttachmentId = null;
                            updateAnalysisInput();
                            resetAnnotationsPanel();
                            isRestoringAnalysis = false;
                            console.groupEnd();
                        });
                } catch (error) {
                    console.error('[VetExam] Exceção síncrona ao tentar carregar o documento.', error);
                    updateOverlay('Erro inesperado ao carregar o documento.', 'Atualize a página e tente novamente.');
                    activeAttachmentLabel.textContent = name;
                    activeAttachmentId = null;
                    updateAnalysisInput();
                    resetAnnotationsPanel();
                    isRestoringAnalysis = false;
                    console.groupEnd();
                }
            }

            toolbarButtons.forEach(function (button) {
                button.addEventListener('click', function () {
                    const toolName = this.dataset.tool;
                    const action = this.dataset.action;

                    if (toolName) {
                        activateTool(toolName);
                        return;
                    }

                    if (!action) {
                        return;
                    }

                    if (action === 'fullscreen') {
                        if (!fullscreenTarget) {
                            return;
                        }

                        if (isFullscreenActive()) {
                            exitFullscreen();
                        } else {
                            requestFullscreen(fullscreenTarget);
                        }

                        return;
                    }

                    const viewport = cornerstone.getViewport(viewerElement);

                    switch (action) {
                        case 'reset':
                            cornerstone.reset(viewerElement);
                            updateViewportStatus(cornerstone.getEnabledElement(viewerElement)?.image);
                            break;
                        case 'invert':
                            viewport.invert = !viewport.invert;
                            cornerstone.setViewport(viewerElement, viewport);
                            break;
                        case 'flipH':
                            viewport.hflip = !viewport.hflip;
                            cornerstone.setViewport(viewerElement, viewport);
                            break;
                        case 'flipV':
                            viewport.vflip = !viewport.vflip;
                            cornerstone.setViewport(viewerElement, viewport);
                            break;
                        case 'rotateLeft':
                            viewport.rotation = (viewport.rotation - 90) % 360;
                            cornerstone.setViewport(viewerElement, viewport);
                            break;
                        case 'rotateRight':
                            viewport.rotation = (viewport.rotation + 90) % 360;
                            cornerstone.setViewport(viewerElement, viewport);
                            break;
                    }

                    updateViewportStatus(cornerstone.getEnabledElement(viewerElement)?.image);
                    persistViewportState();
                });
            });

            attachmentList.forEach(function (item) {
                item.addEventListener('click', function () {
                    loadAttachment(this);
                });
            });

            viewerElement.addEventListener(cornerstone.EVENTS.IMAGE_RENDERED, function (event) {
                updateViewportStatus(event.detail?.image);
                persistViewportState();
                console.debug('[VetExam] Evento IMAGE_RENDERED recebido.', {
                    temImagem: Boolean(event.detail?.image),
                });
            });

            viewerElement.addEventListener(cornerstoneTools.EVENTS.MEASUREMENT_ADDED, function (event) {
                if (isRestoringAnalysis) {
                    console.debug('[VetExam] Evento MEASUREMENT_ADDED ignorado durante restauração.', {
                        ferramenta: event.detail?.toolType,
                    });
                    return;
                }

                promptAnnotationText(event);
                renderAnnotations();
                saveCurrentAttachmentAnalysis();
                console.info('[VetExam] Evento MEASUREMENT_ADDED tratado.', {
                    ferramenta: event.detail?.toolType,
                    possuiDados: Boolean(event.detail?.measurementData),
                });
            });

            viewerElement.addEventListener(cornerstoneTools.EVENTS.MEASUREMENT_MODIFIED, function () {
                if (isRestoringAnalysis) {
                    console.debug('[VetExam] Evento MEASUREMENT_MODIFIED ignorado durante restauração.');
                    return;
                }

                renderAnnotations();
                saveCurrentAttachmentAnalysis();
                console.info('[VetExam] Evento MEASUREMENT_MODIFIED tratado.');
            });

            viewerElement.addEventListener(cornerstoneTools.EVENTS.MEASUREMENT_REMOVED, function () {
                if (isRestoringAnalysis) {
                    console.debug('[VetExam] Evento MEASUREMENT_REMOVED ignorado durante restauração.');
                    return;
                }

                renderAnnotations();
                saveCurrentAttachmentAnalysis();
                console.info('[VetExam] Evento MEASUREMENT_REMOVED tratado.');
            });

            if (reportForm) {
                reportForm.addEventListener('submit', function () {
                    if (activeAttachmentId) {
                        saveCurrentAttachmentAnalysis();
                    }

                    updateAnalysisInput();
                    console.info('[VetExam] Formulário de laudo enviado com estado das análises.', {
                        attachmentKeys: Object.keys(analysisState),
                    });
                });
            }

            window.addEventListener('resize', function () {
                cornerstone.resize(viewerElement, true);
            });

            const firstSupported = Array.from(attachmentList).find(function (element) {
                return element.dataset.isSupported === '1' && element.dataset.url;
            });

            if (firstSupported) {
                loadAttachment(firstSupported);
            } else if (attachments.length === 0) {
                updateOverlay('Nenhum documento anexado.', 'Utilize o botão "Ver coleta" para adicionar documentos.');
            } else {
                updateOverlay('Nenhum documento compatível encontrado.', 'Faça o download dos arquivos para analisá-los.');
            }

            updateFullscreenButtonState();

            debugChannel.restore = restoreAnalysisForAttachment;
            debugChannel.saveCurrent = saveCurrentAttachmentAnalysis;
            debugChannel.updateInput = updateAnalysisInput;
        });
    </script>
@endsection

@props([
    'title',
    'backUrl' => null,
    'heading' => null,
])

<div class="page-content">
    <div class="card border-top border-0 border-4 border-primary">
        <div class="card-body p-5">
            <div class="page-breadcrumb d-sm-flex align-items-center mb-3">
                <div class="ms-auto">
                    @if ($backUrl)
                        <a href="{{ $backUrl }}" type="button" class="btn btn-light btn-sm">
                            <i class="bx bx-arrow-back"></i> Voltar
                        </a>
                    @endif
                </div>
            </div>

            <div class="card-title d-flex align-items-center">
                <h5 class="mb-0 text-primary">{{ $heading ?? $title }}</h5>
            </div>
            <hr>

            <div class="pl-lg-4">
                {{ $slot }}
            </div>
        </div>
    </div>
</div>


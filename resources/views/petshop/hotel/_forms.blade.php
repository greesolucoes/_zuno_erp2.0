<div class="row g-3">

    <ul class="nav nav-tabs nav-primary" role="tablist">
        <li class="nav-item" style="flex: 1 !important" role="presentation">
            <a class="px-3 nav-link active" data-bs-toggle="tab" href="#hotel_info_geral" role="tab"
                aria-selected="true">
                <div class="d-flex align-items-center justify-content-center">
                    <div class="tab-title">
                        <i class="ri-file-user-fill"></i>
                        Informações gerais
                    </div>
                </div>
            </a>
        </li>
        <li class="nav-item" style="flex: 1 !important" role="presentation">
            <a class="px-3 nav-link" data-bs-toggle="tab" href="#hotel_agendamento" role="tab">
                <div class="d-flex align-items-center justify-content-center">
                    <div class="tab-title">
                        <i class="ri-calendar-2-line"></i>
                        Agendamento
                    </div>
                </div>
            </a>
        </li>
        <li class="nav-item" style="flex: 1 !important" role="presentation">
            <a class="px-3 nav-link" data-bs-toggle="tab" href="#hotel_servicos_produtos" role="tab">
                <div class="d-flex align-items-center justify-content-center">
                    <div class="tab-title">
                        <i class="ri-box-2-line"></i>
                        Serviços e produtos
                    </div>
                </div>
            </a>
        </li>
    </ul>

    <div class="tab-content">
        <div class="tab-pane fade show active" id="hotel_info_geral" role="tabpanel">
            @include('components.petshop.hoteis.tabs.hotel_info_geral')
        </div>

        <div class="tab-pane fade" id="hotel_agendamento" role="tabpanel">
            @include('components.petshop.hoteis.tabs.hotel_agendamento')
        </div>
        
        <div class="tab-pane fade" id="hotel_servicos_produtos" role="tabpanel">
            @include('components.petshop.hoteis.tabs.hotel_servicos_produtos')
        </div>

    </div>


    <hr class="mt-4">
    <div class="col-12" style="text-align: right;">
        <button type="submit" class="btn btn-success px-5" id="btn-store">Salvar</button>
    </div>
</div>

@section('js')
<script type="text/javascript" src="/js/hotel.js"></script>
@endsection

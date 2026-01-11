<div class="row g-3">
    <ul class="nav nav-tabs nav-primary" role="tablist">
        <li class="nav-item" style="flex: 1 !important" role="presentation">
            <a class="px-3 nav-link active" data-bs-toggle="tab" href="#estetica_info_geral" role="tab"
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
            <a class="px-3 nav-link" data-bs-toggle="tab" href="#estetica_servicos_produtos" role="tab">
                <div class="d-flex align-items-center justify-content-center">
                    <div class="tab-title">
                        <i class="ri-box-2-line"></i>
                        Serviços e produtos
                    </div>
                </div>
            </a>
        </li>
        <li class="nav-item" style="flex: 1 !important" role="presentation">
            <a class="px-3 nav-link" data-bs-toggle="tab" href="#estetica_agendamento" role="tab">
                <div class="d-flex align-items-center justify-content-center">
                    <div class="tab-title">
                        <i class="ri-calendar-2-line"></i>
                        Agendamento
                    </div>
                </div>
            </a>
        </li>
    </ul>
    
    <input type="hidden" id="empresa_id" value="{{ auth()->user()->empresa->empresa_id }}">
    <input type="hidden" id="id_estetica" value="{{ isset($data) ? $data->id : ''}}">

    <div class="tab-content">
        <div class="tab-pane fade show active" id="estetica_info_geral">
            @include('components.petshop.esteticas.tabs.estetica_info_geral')
        </div>  
    </div>
    
    <div class="tab-content">
        <div class="tab-pane fade" id="estetica_servicos_produtos">
            @include('components.petshop.esteticas.tabs.estetica_servicos_produtos')
        </div>  
    </div>
    
    <div class="tab-content">
        <div class="tab-pane fade" id="estetica_agendamento">
            @include('components.petshop.esteticas.tabs.estetica_agendamento')
        </div>  
    </div>

    <hr class="mt-4">
    <div class="col-12" style="text-align: right;">
        <button type="submit" class="btn btn-primary px-5" id="btn-store">Salvar</button>
    </div>
</div>

@section('js')
<script src="https://cdn.jsdelivr.net/npm/dayjs@1/dayjs.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/dayjs@1/plugin/isoWeek.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/dayjs@1/locale/pt-br.js"></script>
<script type="text/javascript" src="/js/estetica_form.js"></script>
@endsection

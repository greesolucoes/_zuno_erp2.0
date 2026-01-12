<div class="row g-3 m-auto">
    <div class="row mt-3">
        <div class="col-md-6 row">
            <button type="button" class="btn btn-identificacao btn-outline-primary link-active px-6" onclick="selectDiv('identificacao')">Reserva</button>
        </div>
        <div class="col-md-6 row m-auto">
            <button type="button" class="btn btn-aliquotas btn-outline-primary" onclick="selectDiv('aliquotas')">Serviços e produtos</button>
        </div>
    </div>

    <input type="hidden" id="empresa_id" value="{{ request()->empresa_id ?? auth()->user()?->empresa?->empresa_id }}">
    <input type="hidden" id="id_estetica" value="{{ isset($data) ? $data->id : ''}}">

    <div class="div-identificacao row mt-4">
        <h6 class="mt-2">Informações gerais</h6>
        <div class="col-12">
            @include('components.petshop.esteticas.tabs.estetica_info_geral')
        </div>

        <hr class="mt-3">

        <h6 class="mt-2">Agendamento</h6>
        <div class="col-12">
            @include('components.petshop.esteticas.tabs.estetica_agendamento')
        </div>
    </div>

    <div class="div-aliquotas row mt-4 d-none">
        <h6 class="mt-2">Serviços e produtos</h6>
        <div class="col-12">
            @include('components.petshop.esteticas.tabs.estetica_servicos_produtos')
        </div>
    </div>

    <hr class="mt-4">
    <div class="col-12">
        <button type="submit" class="btn btn-primary px-5" id="btn-store">Salvar</button>
    </div>
</div>

@section('js')
<script src="https://cdn.jsdelivr.net/npm/dayjs@1/dayjs.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/dayjs@1/plugin/isoWeek.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/dayjs@1/locale/pt-br.js"></script>
<script type="text/javascript" src="/js/estetica_form.js"></script>
@endsection

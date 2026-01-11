<div class="row g-3 m-auto">
    <div class="row mt-3">
        <div class="col-md-6 row">
            <button type="button" class="btn btn-identificacao btn-outline-primary link-active px-6" onclick="selectDiv('identificacao')">Reserva</button>
        </div>
        <div class="col-md-6 row m-auto">
            <button type="button" class="btn btn-aliquotas btn-outline-primary" onclick="selectDiv('aliquotas')">Serviços e produtos</button>
        </div>
    </div>

    <div class="div-identificacao row mt-4">
        <h6 class="mt-2">Informações gerais</h6>
        <div class="col-12">
            @include('components.petshop.hoteis.tabs.hotel_info_geral')
        </div>

        <hr>

        <h6>Agendamento</h6>
        <div class="col-12">
            @include('components.petshop.hoteis.tabs.hotel_agendamento')
        </div>
    </div>

    <div class="div-aliquotas row mt-4 d-none">
        <h6 class="mt-2">Serviços e produtos</h6>
        <div class="col-12">
            @include('components.petshop.hoteis.tabs.hotel_servicos_produtos')
        </div>
    </div>

    <hr class="mt-4">
    <div class="col-12">
        <button type="submit" class="btn btn-primary px-5" id="btn-store">Salvar</button>
    </div>
</div>

@section('js')
<script type="text/javascript" src="/js/hotel.js"></script>
@endsection

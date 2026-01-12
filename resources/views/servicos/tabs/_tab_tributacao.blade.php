<div class="row g-xl-3 g-lg-2">
    <div class="col-md-2">
        {!! 
            Form::tel('aliquota_iss', '% ISS')
            ->attrs(['class' => 'percentual']) 
            ->placeholder('0.00%')
        !!}
    </div>

    <div class="col-md-2">
        {!! 
            Form::tel('aliquota_pis', '% PIS')
            ->attrs(['class' => 'percentual']) 
            ->placeholder('0.00%')
        !!}
    </div>

    <div class="col-md-2">
        {!! 
            Form::tel('aliquota_cofins', '% COFINS')
            ->attrs(['class' => 'percentual']) 
            ->placeholder('0.00%')
        !!}
    </div>

    <div class="col-md-2">
        {!! 
            Form::tel('aliquota_inss', '% INSS')
            ->attrs(['class' => 'percentual']) 
            ->placeholder('0.00%')
        !!}
    </div>

    <div class="col-md-2">
        {!! 
            Form::tel('aliquota_ir', '% IR')
            ->attrs(['class' => 'percentual']) 
            ->placeholder('0.00%')
        !!}
    </div>

    <div class="col-md-2">
        {!! 
            Form::tel('aliquota_csll', '% CSLL')
            ->attrs(['class' => 'percentual']) 
            ->placeholder('0.00%')
        !!}
    </div>
</div>
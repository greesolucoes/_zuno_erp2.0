<div class="row g-3">
    <div class="col-md-3 d-flex align-items-end gap-2 form-input-100">
        {!! Form::select('cliente_id', 'Cliente')->attrs(['class' => 'select2'])->options(isset($item) ? [$item->cliente_id => $item->cliente->razao_social] : [])->required() !!}

        <a style="height: 36px;" href="{{ route('clientes.create') }}" target='_blank'
            class="btn d-flex justify-content-center align-items-center btn-primary btn-sm">
            <i class="ri-add-line"></i>
        </a>
    </div>

    @if (isset($tipos))
        <div class="col-md-3 col-12">
            {!! Form::select('tipo_id', 'Tipo', ['' => 'Selecione o tipo'] + $tipos->pluck('nome', 'id')->all())->required()->attrs(['class' => 'form-select select2']) !!}
        </div>
    @endif

    <div class="col-md-3 col-12">
        {!! Form::date('datahora_entrega', 'Data e hora da entrega')->type('datetime-local')->required() !!}
    </div>

    <div class="col-md-3 col-12">
        {!! Form::text('valor', 'Valor')->placeholder('Digite o valor aqui...')->value(isset($item) ? __moeda($item->valor) : '')->attrs(['class' => 'moeda'])->required() !!}
    </div>

    @isset($item)
        <div class="col-md-3">
            {!! Form::select('status', 'Status', [
                '' => 'Selecione o status',
                'pendente' => 'Pendente',
                'entregue' => 'Entregue',
                'cancelado' => 'Cancelado',
            ])->required()->attrs(['class' => 'form-select select2']) !!}
        </div>

        <div class="col-md-3">
            {!! Form::select('foi_pago', 'Foi pago?', ['' => 'Selecione se foi pago', 'S' => 'Sim', 'N' => 'Não'])->required()->attrs(['class' => 'form-select select2']) !!}
        </div>
        @endif

        <div class="col-md-6 col-12">
            {!! Form::text('motorista_nome', 'Nome do entregador')->placeholder('Digite o nome do entregador aqui...') !!}
        </div>

    </div>

    <h4 class='mt-5 mb-3'>Endereço</h4>

    <div class='row g-3'>

        <div class="col-md-2">
            {!! Form::text('cep', 'CEP')->attrs(['class' => 'cep'])->required() !!}
        </div>

        <div class="col-md-4">
            @isset($item)
                {!! Form::select('cidade_id', 'Cidade')->attrs(['class' => 'select2'])->options($item != null && $item->cidade ? [$item->cidade_id => $item->cidade->info] : [])->required() !!}
            @else
                {!! Form::select('cidade_id', 'Cidade')->attrs(['class' => 'select2'])->required() !!}
            @endisset
        </div>

        <div class="col-md-3">
            {!! Form::text('rua', 'Rua')->attrs(['class' => ''])->required() !!}
        </div>

        <div class="col-md-1">
            {!! Form::text('numero', 'Número')->attrs(['class' => ''])->required() !!}
        </div>

        <div class="col-md-2">
            {!! Form::text('bairro', 'Bairro')->attrs(['class' => ''])->required() !!}
        </div>

        <div class="col-md-4">
            {!! Form::text('complemento', 'Complemento')->attrs(['class' => '']) !!}
        </div>

        <div class="col-12">
            <button type="submit" class="btn btn-primary px-5" id="btn-store">Salvar</button>
        </div>
    </div>

    @section('js')
        <script type="text/javascript">
            function findCidade(codigo_ibge) {
                $('#inp-cidade_id').html('')
                $.get(path_url + "api/cidadePorCodigoIbge/" + codigo_ibge)
                    .done((res) => {
                        var newOption = new Option(res.info, res.id, false, false);
                        $('#inp-cidade_id').append(newOption).trigger('change');
                    })
                    .fail((err) => {
                        console.log(err)
                    })
            }

            $(document).on("blur", "#inp-cep", function() {
                let cep = $(this).val().replace(/[^0-9]/g, '')

                if (cep.length == 8) {
                    $.get('https://viacep.com.br/ws/' + cep + '/json/')
                        .done((data) => {
                            if (data != null) {
                                $("#inp-rua").val(data.logradouro)
                                $('#inp-bairro').val(data.bairro)
                                $('#inp-cidade_id').val(data.localidade)
                                findCidade(data.ibge)
                            }
                        })
                        .fail((err) => {
                            console.log(err)
                            // swal("Algo errado", err.responseJSON['detalhes'], "warning")
                        })
                }
            })
        </script>
    @endsection

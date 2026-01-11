<div class="modal fade" id="modal_view_cliente-{{ $cliente->id }}" tabindex="-1"
    aria-labelledby="clienteBackdropModal">
    <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable" style="margin: 0px auto !important">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="clienteBackdropModal">
                    <img src='/logo_simples_branco_laranja.svg' alt='Logo Diprosoft' width='40' height='28' />
                    Informações do Cliente
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>

            <div class="modal-body">
                <div class="row g-xl-3 g-lg-2 px-3">
                    <div class="col-md-3">
                        {!! 
                            Form::text('', 
                                $cliente->is_cpf ? 'CPF' : 'CNPJ',
                            )
                            ->value($item->cpf_cnpj)
                            ->attrs(['class' => 'cpf_cnpj'])
                            ->disabled()
                        !!}
                    </div>
                    <div class="col-md-4">
                        {!! 
                            Form::text('', 'Cliente')
                            ->value($cliente->razao_social)
                            ->disabled()
                        !!}
                    </div>
                   @if ($cliente->is_cpf == false)
                        <div class="col-md-3">
                            {!!
                                Form::text('', 'Nome Fantasia')
                                ->value(isset($cliente->nome_fantasia) ? $cliente->nome_fantasia : '')
                                ->disabled()
                            !!}
                        </div>
                    @endif
                    @if (!empty($cliente->ie))
                        <div class="col-md-2">
                            {!!
                                Form::text('', 'IE')
                                ->value($cliente->ie)
                                ->disabled()
                            !!}
                        </div>
                    @endif
                    <div class="col-md-1">
                        {!! 
                            Form::text('', 'Ativo')
                            ->value($cliente->status == '1' ? 'Sim' : 'Não')
                            ->disabled()
                        !!}
                    </div>
                    <div class="col-md-2">
                        {!! 
                            Form::text('', 'Contribuinte')
                            ->value($cliente->contribuinte == '1' ? 'Sim' : 'Não')
                            ->disabled()
                        !!}
                    </div>
                    <div class="col-md-2">
                        {!! 
                            Form::text('', 'Consumidor Final')
                            ->value($cliente->consumidor_final == '1' ? 'Sim' : 'Não')
                            ->disabled()
                        !!}
                    </div>

                    <hr>

                    @if ($cliente->is_cpf == false)
                        <div class="col-md-3">
                            {!! 
                                Form::text('', 'Responsável')
                                ->value(isset($cliente->contato) ? $cliente->contato : '')
                                ->disabled()
                            !!}
                        </div>
                    @endif
                    <div class="col-md-2">
                        {!! 
                            Form::text('', 'Whatsapp')
                            ->value(isset($cliente->telefone) ? $cliente->telefone : '')
                            ->disabled()
                        !!}
                    </div>
                    <div class="col-md-2">
                        {!! 
                            Form::text('', 'Telefone fixo')
                            ->value(isset($cliente->telefone_secundario) ? $cliente->telefone_secundario : '')
                            ->disabled()
                        !!}
                    </div>
                    <div class="col-md-2">
                        {!! 
                            Form::text('', '3º Telefone')
                            ->value(isset($cliente->telefone_terciario) ? $cliente->telefone_terciario : '')
                            ->disabled()
                        !!}
                    </div>
                    <div class="col-md-4">
                        {!! 
                            Form::text('', 'E-mail')
                            ->value(isset($cliente->email) ? $cliente->email : '')
                            ->disabled()
                        !!}
                    </div>

                    <hr>

                    @if (
                        isset($item) && $item->endereco_url &&
                        (isset($item->rua) || isset($item->numero) || isset($item->bairro) || isset($item->cidade_id))
                    )
                        <div id="map_address_container">
                            <div class="col-md-4">
                                <a 
                                    class="address_link"
                                    href={{ $item->endereco_url }}
                                    target="_blank"
                                >
                                    <i class="ri-road-map-line"></i>
                                    Abrir endereço no mapa
                                </a>
                            </div>
                        </div>
                    @endif

                    <div class="col-md-2">
                        {!! 
                            Form::text('', 'CEP')
                            ->value(isset($cliente->cep) ? $cliente->cep : '')
                            ->attrs(['class' => 'cep'])
                            ->disabled()
                        !!}
                    </div>
                    <div class="col-md-3">
                        {!! 
                            Form::text('', 'Cidade')
                            ->value(isset($cliente->cidade->info) ? $cliente->cidade->info : '')
                            ->disabled()
                        !!}
                    </div>
                    <div class="col-md-3">
                        {!! 
                            Form::text('', 'Rua')
                            ->value(isset($cliente->rua) ? $cliente->rua : '')
                            ->disabled()
                        !!}
                    </div>
                    <div class="col-md-2">
                        {!! 
                            Form::text('', 'Número')
                            ->value(isset($cliente->numero) ? $cliente->numero : '')
                            ->disabled()
                        !!}
                    </div>
                    <div class="col-md-3">
                        {!! 
                            Form::text('', 'Bairro')
                            ->value(isset($cliente->bairro) ? $cliente->bairro : '')
                            ->disabled()
                        !!}
                    </div>
                    <div class="col-md-3">
                        {!! 
                            Form::text('', 'Complemento')
                            ->value(isset($cliente->complemento) ? $cliente->complemento : '')
                            ->disabled()
                        !!}
                    </div>

                    <div class="d-flex align-items-center gap-3 justify-content-end mt-5">
                        <button class="btn btn-success px-3 float-end" data-bs-dismiss="modal">
                            Fechar
                        </button>
                        @can('clientes_edit')
                            <a 
                                class="btn btn-danger px-3 float-end"
                                href="{{ route('clientes.edit', [$item->id, 'page' => request()->query('page', 1)]) }}"
                            >
                                Ir para edição
                            </a>
                        @endcan
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

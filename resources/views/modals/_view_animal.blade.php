<div class="modal fade" id="modal_view_animal-{{ $nome->id }}" tabindex="-1"
    aria-labelledby="animalBackdropModal">
    <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable" style="margin: 0px auto !important">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="animalBackdropModal">
                    <img src='/logo_simples_branco_laranja.svg' alt='Logo Diprosoft' width='40' height='28' />
                    Informações do Pet
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="row g-xl-3 g-lg-2 px-3">
                    <div class="col-md-3">
                       <div class="w-100">
                            {!!
                                Form::text('nome', 'Nome do pet')
                                ->value(isset($item) ? $item->nome : '')
                                ->disabled()
                            !!}
                        </div>
                    </div>

                    <div class="col-md-3">
                        <div class="w-100">
                            {!!
                                Form::text('', 'Cliente')
                                ->value($item->cliente->razao_social)
                                ->disabled()
                            !!}
                        </div>
                    </div>


                    <hr>

                    <div class="col-md-3">
                        <div class="w-100">
                            {!!
                                Form::text('', 'Especie')
                                ->value($item->especie->nome ?? '--')
                                ->disabled()
                            !!}
                        </div>
                    </div>


                    <div class="col-md-3">
                        <div class="w-100">
                            {!!
                                Form::text('', 'Raça')
                                ->value($item->raca->nome ?? '--')
                                ->disabled()
                            !!}
                        </div>
                    </div>

                    <div class="col-md-3">
                        <div class="w-100">
                            {!!
                                Form::text('', 'Pelagem')
                                ->value($item->pelagem->nome ?? '--')
                                ->disabled()
                            !!}
                        </div>
                    </div>

                    <div class="col-md-3">
                        <div class="w-100">
                            {!!
                                Form::text('', 'Cor')
                                ->value(isset($item) ? $item->cor : '')
                                ->disabled()
                            !!}
                         </div>
                    </div>

                    <hr>

                    <div class="col-md-3">
                        <div class="w-100">
                            {!!
                                Form::text('', 'Sexo')
                                ->value(isset($item) ? ($item->sexo == 'F' ? 'Fêmea' : 'Macho') : '')
                                ->disabled()
                            !!}
                        </div>
                    </div>

                    <div class="col-md-3">
                        <div class="w-100">
                            {!!
                                Form::text('', 'Peso')
                                ->value(isset($item) ? $item->peso : '')
                                ->disabled()
                            !!}
                        </div>
                    </div>

                    <div class="col-md-3">
                        <div class="w-100">
                            {!!
                                Form::text('', 'Porte')
                                ->value(isset($item) ? $item->porte : '')
                                ->disabled()
                            !!}
                        </div>
                    </div>

                    <div class="col-md-3">
                        <div class="w-100">
                            {!!
                                Form::text('', 'Origem')
                                ->value(isset($item) ? $item->origem : '')
                                ->disabled()
                            !!}
                        </div>
                    </div>

                    <hr>

                   <div class="col-md-3">
                        <div class="w-100">
                            {!!
                                Form::text('', 'Data de nascimento')
                                ->value(isset($item->data_nascimento) ? __data_pt($item->data_nascimento, false ) : '')
                                ->disabled()
                            !!}
                        </div>
                    </div>

                    <div class="col-md-3">
                        <div class="w-100">
                            {!!
                                Form::text('', 'Chip')
                                ->value(isset($item) ? $item->chip : '')
                                ->disabled()
                            !!}
                        </div>
                    </div>

                    <div class="col-md-3">
                        <div class="w-100">
                            {!!
                                Form::text('', 'Possui pedigree?')
                                ->value(isset($item) ? $item->tem_pedigree == '1' ? 'Sim' : 'Não' : '')
                                ->disabled()
                            !!}
                        </div>
                    </div>

                    <div class="col-md-3">
                        <div class="w-100">
                            {!!
                                Form::text('', 'Número do pedigree')
                                ->value(isset($item) ? $item->pedigree: '')
                                ->disabled()
                            !!}
                        </div>
                    </div>

                    <hr>

                    <div class="col-md-6">
                        <div class="w-100">
                            {!!
                                Form::textarea('', 'Observação')
                                ->value(isset($item) ? $item->observacao : '')
                                ->attrs(['rows' => '6', 'style' => 'resize:none;'])
                                ->disabled()
                            !!}
                        </div>
                    </div>

                    <div class="d-flex align-items-center gap-3 justify-content-end mt-5">
                        <button class="btn btn-success px-3 float-end" data-bs-dismiss="modal">
                            Fechar
                        </button>
                        @can('clientes_edit')
                            <a
                                class="btn btn-danger px-3 float-end"
                                href="{{ route('animais.pacientes.edit', [$item->id, 'page' => request()->query('page', 1)]) }}"
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


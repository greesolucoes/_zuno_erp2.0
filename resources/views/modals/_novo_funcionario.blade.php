<div class="modal fade" id="modal_colaborador" tabindex="-1" aria-labelledby="modalColaboradorLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header bg-dark text-white">
                <h5 class="modal-title d-flex align-items-center" id="modalColaboradorLabel">
                    <img src='/logo_simples_branco_laranja.svg' alt='Logo Diprosoft' width='40' height='28' />
                    Cadastrar Colaborador
                </h5>
                <button
                    type="button"
                    class="btn-close btn-close-white"
                    @if (isset($back_modal)) 
                        data-bs-toggle="modal" 
                        data-bs-target="{{ $back_modal }}"
                    @else 
                        data-bs-dismiss="modal" 
                    @endif
                >
                </button>
            </div>

            <div class="modal-body">
                <h4 class="mb-3">Dados do colaborador</h4>

                <form id="form-colaborador" name="form-colaborador" method="POST">
                    @csrf

                    <div class="row g-3">
                        <div class="col-md-5">
                            {!! Form::text('nome', 'Nome')->placeholder('Digite o nome aqui...')->required() !!}
                        </div>

                        <div class="col-md-3">
                            {!! Form::text('cpf_cnpj', 'CPF/CNPJ')->attrs(['class' => 'cpf_cnpj'])->placeholder('Digite o CPF/CNPJ aqui...') !!}
                        </div>

                        <div class="col-md-4">
                            {!! Form::text('telefone', 'Celular')->attrs(['class' => 'fone'])->placeholder('Digite o celular com DDD') !!}
                        </div>

                        <div class="col-md-2">
                            {!! Form::tel('comissao_percent', 'Comissão (%)')->attrs(['class' => 'percentual'])->placeholder('Digite a comissão em % aqui...') !!}
                        </div>

                        <div class="col-md-2">
                            {!! Form::tel('salario', 'Salário')->attrs(['class' => 'moeda'])->placeholder('Digite o salário aqui...') !!}
                        </div>

                        <div class="col-md-2">
                            {!! Form::text('codigo', 'Código')->placeholder('Digite o código aqui...') !!}
                        </div>

                        <div class="col-md-3">
                            {!! Form::select('cidade_id', 'Cidade')
                                ->attrs(['class' => 'select2 cidade_id2'])
                                ->required() !!}
                        </div>

                        <div class="col-md-5">
                            {!! Form::text('rua', 'Rua')->placeholder('Digite a rua aqui...') !!}
                        </div>

                        <div class="col-md-1">
                            {!! Form::tel('numero', 'Número')->placeholder('Número aqui...') !!}
                        </div>

                        <div class="col-md-2">
                            {!! Form::text('bairro', 'Bairro')->placeholder('Digite o bairro aqui...') !!}
                        </div>

                        <div class="col-md-12 mt-4 d-flex gap-3 align-items-center">
                            <label for="inp-has_user">Possui usuário?</label>
                            <input type="checkbox" id="inp-has_user" name="has_user" />
                        </div>

                        <div class="col-12" id="user-container">
                            <div class="row">
                                <div class="col-md-3">
                                    {!! Form::text('user_name', 'Nome do usuário')->placeholder('Digite o nome do usuário')->value(old('user_name', $item->usuario->name ?? '')) !!}
                                </div>

                                <div class="col-md-3">
                                    {!! Form::text('user_email', 'E-mail de acesso')->placeholder('Digite o e-mail de acesso')->value(old('user_email', $item->usuario->email ?? '')) !!}
                                </div>

                                <div class="col-md-2">
                                    {!! Form::select('user_admin', 'Admin', ['' => 'Selecione', 0 => 'Não', 1 => 'Sim'], $item->usuario->admin ?? null)->attrs(['class' => 'form-select']) !!}
                                </div>

	                            </div>
	                        </div>

                        <div class="col-12 mt-4 text-end">
                            <button type="button" class="btn btn-success px-5" id="btn-store-funcionario">Salvar</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

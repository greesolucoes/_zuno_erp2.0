<ul class="nav nav-tabs nav-primary" role="tablist">
    <li class="nav-item" role="presentation">
        <a class="nav-link px-3 active" data-bs-toggle="tab" href="#dados" role="tab" aria-selected="true">
            <div class="d-flex align-items-center">
                <div class="tab-title">
                    <i class="ri-file-text-line"></i>
                    Dados
                </div>
            </div>
        </a>
    </li>
    <li class="nav-item" role="presentation">
        <a class="nav-link px-3" data-bs-toggle="tab" href="#pagamento" role="tab" aria-selected="false">
            <div class="d-flex align-items-center">
                <div class="tab-title">
                    <i class="ri-money-dollar-box-line"></i>
                    Pagamento
                </div>
            </div>
        </a>
    </li>
</ul>

<div class="tab-content">
    <div class="tab-pane fade show active" id="dados" role="tabpanel" data-label="Dados">
        <div class="row g-3 mt-3">
            @isset($clientes)
                <div class="col-md-6 col-12">
                    {!! Form::select('cliente_id', 'Cliente')
                        ->attrs(['class' => 'select2 cliente_id', 'id' => 'cliente_id'])
                        ->placeholder('Selecione o cliente...')
                        ->options(is_array($clientes) ? $clientes : [])
                        ->required() !!}
                </div>
            @endisset

            <div class="col-md-6 col-12">
                {!! Form::text('name', 'Nome')
                    ->required()
                    ->disabled()
                    ->attrs(array_merge(['id' => 'name'], isset($clientes) ? ['readonly' => true] : [])) !!}
            </div>

            <div class="col-md-6 col-12">
                {!! Form::text('email', 'E-mail')
                    ->disabled()
                    ->required()
                    ->attrs(array_merge(['type' => 'email', 'id' => 'email'], isset($clientes) ? ['readonly' => true] : [])) !!}
            </div>

            @isset($planos)
                <div class="col-md-6 col-12">
                    {!! Form::select('plano_id', 'Plano')
                        ->placeholder('Selecione o plano...')
                        ->attrs(['class' => 'select2 plano_id', 'id' => 'plano_id'])
                        ->options(is_array($planos) ? $planos : [])
                        ->required() !!}
                </div>
            @else
                <div class="col-md-6 col-12">
                    {!! Form::text('password', 'Senha')->type('password')->required() !!}
                </div>
                <div class="col-md-6 col-12">
                    {!! Form::text('password_confirmation', 'Confirmar Senha')->type('password')->required() !!}
                </div>
            @endisset
        </div>
    </div>

    <div class="tab-pane fade" id="pagamento" role="tabpanel" data-label="Pagamento">
        <div class="row g-3 mt-3">
            @if(isset($edit) && isset($conta))
                <div class="col-md-2">
                    {!! Form::date('data_inicial', 'Data Inicial')
                        ->value(optional($data)->data_inicial)
                        ->disabled() !!}
                </div>

                <div class="col-md-2">
                    {!! Form::date('data_final', 'Data Final')
                        ->value(optional($data)->data_final)
                        ->disabled() !!}
                </div>

                <div class="col-12"></div>

                <div class="col-md-2">
                    {!! Form::text('valor_integral', 'Valor Integral')
                        ->attrs(['class' => 'moeda'])
                        ->value(__moeda($conta->valor_integral ?? 0))
                        ->disabled() !!}
                </div>

                <div class="col-md-3">
                    {!! Form::text('forma_pagamento', 'Forma de Pagamento')
                        ->value(optional($conta->formaPagamento)->nome)
                        ->disabled() !!}
                </div>

                <div class="col-md-2">
                    {!! Form::date('data_vencimento', 'Data Vencimento')
                        ->value($conta->data_vencimento)
                        ->disabled() !!}
                </div>

                <div class="col-md-2">
                    {!! Form::select('status', 'Conta Recebida', ['0' => 'Não', '1' => 'Sim'])
                        ->attrs(['class' => 'form-select'])
                        ->value($conta->status)
                        ->disabled() !!}
                </div>

                <div class="col-md-4">
                    {!! Form::textarea('descricao', 'Descrição')
                        ->attrs([
                            'rows' => 4,
                            'style' => 'resize: none;',
                            'class' => 'text-uppercase'
                        ])
                        ->value($conta->descricao)
                        ->disabled() !!}
                </div>

                @if($conta->parcelas->isNotEmpty())
                    <div class="col-12">
                        <div class="table-responsive">
                            <table class="table table-sm">
                                <thead>
                                    <tr>
                                        <th>Parcela</th>
                                        <th>Vencimento</th>
                                        <th>Valor</th>
                                        <th>Status</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($conta->parcelas as $parcela)
                                        <tr>
                                            <td>{{ $parcela->numero }}</td>
                                            <td>{{ __data_pt($parcela->data_vencimento, 0) }}</td>
                                            <td>R$ {{ __moeda($parcela->valor_atualizado) }}</td>
                                            <td>{{ $parcela->status === 'paga' ? 'Pago' : 'Em aberto' }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                @endif
            @else
                <div class="col-md-2">
                    {!! Form::date('data_inicial', 'Data Inicial')->required() !!}
                </div>

                <div class="col-md-2">
                    {!! Form::date('data_final', 'Data Final')->required() !!}
                </div>

                <div class="col-12"></div>

                <div class="col-md-2">
                    {!! Form::text('valor_integral', 'Valor Integral')
                        ->attrs(['class' => 'moeda'])
                        ->placeholder('R$ 0,00')
                        ->required() !!}
                </div>

                <div class="col-md-3">
                    {!! Form::select('forma_pagamento_id', 'Forma de Pagamento')
                        ->attrs(['class' => 'form-select'])
                        ->options(['' => 'Selecione uma forma de pagamento'] + $formas_pagamento->toArray())
                        ->required() !!}
                </div>

                <hr class="mt-3">

                <div class="col-md-2">
                    {!! Form::date('data_vencimento', 'Data Vencimento')->required() !!}
                </div>

                <div class="col-md-2">
                    {!! Form::select('status', 'Conta Recebida', ['0' => 'Não', '1' => 'Sim'])
                        ->attrs(['class' => 'form-select'])
                        ->required() !!}
                </div>

                <hr class="mt-3">

                <div class="col-md-4">
                    {!! Form::textarea('descricao', 'Descrição')
                        ->attrs([
                            'rows' => 4,
                            'style' => 'resize: none;',
                            'class' => 'text-uppercase'
                        ])
                        ->placeholder('Descrição da conta a receber') !!}
                </div>
            @endif
        </div>
    </div>
</div>

<div class="col-12 text-end mt-5">
    <button type="submit" class="btn btn-primary px-5" id="btn-store">Salvar</button>
</div>

@section('js')
    <script src="{{ asset('js/petshop_plano_user.js') }}"></script>
@endsection

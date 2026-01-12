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
</ul>

<div class="tab-content">
    <div class="tab-pane fade show active" id="dados" role="tabpanel" data-label="Dados">
        <div class="row g-3 mt-3">
            <div class="col-md-6 col-12">
                {!! Form::text('name', 'Nome')
                    ->required()
                    ->attrs(['id' => 'name']) !!}
            </div>

            <div class="col-md-6 col-12">
                {!! Form::text('email', 'E-mail')
                    ->required()
                    ->attrs(['type' => 'email', 'id' => 'email']) !!}
            </div>

            @if (!isset($edit))
                <div class="col-md-6 col-12">
                    {!! Form::text('password', 'Senha')->type('password')->required() !!}
                </div>
                <div class="col-md-6 col-12">
                    {!! Form::text('password_confirmation', 'Confirmar Senha')->type('password')->required() !!}
                </div>
            @endif
        </div>
    </div>
</div>

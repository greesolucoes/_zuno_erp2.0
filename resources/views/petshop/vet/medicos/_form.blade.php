@php
    $selectedEmployee = old('funcionario_id', optional($medico ?? null)->funcionario_id);
    $statusValue = old('status', optional($medico ?? null)->status ?? 'ativo');
    $employeeOptions = ['' => 'Selecione um colaborador'] + $employees->mapWithKeys(function ($employee) {
        $cargo = $employee->cargo?->nome ? ' - ' . $employee->cargo->nome : '';
        return [$employee->id => trim($employee->nome . $cargo)];
    })->toArray();
@endphp

<div class="row g-3">
    <div class="col-md-6 col-12">
        {!! Form::select('funcionario_id', 'Colaborador', $employeeOptions)
            ->attrs(['class' => 'form-select'])
            ->value($selectedEmployee)
            ->required() !!}
    </div>

    <div class="col-md-6 col-12">
        {!! Form::text('crmv', 'CRMV')
            ->placeholder('Digite o CRMV')
            ->attrs(['maxlength' => 30])
            ->value(old('crmv', optional($medico ?? null)->crmv))
            ->required() !!}
    </div>

    <div class="col-md-6 col-12">
        {!! Form::text('especialidade', 'Especialidade')
            ->placeholder('Clínica geral, ortopedia, cardiologia...')
            ->attrs(['maxlength' => 255])
            ->value(old('especialidade', optional($medico ?? null)->especialidade)) !!}
    </div>

    <div class="col-md-6 col-12">
        {!! Form::select('status', 'Status', [
            'ativo' => 'Ativo',
            'inativo' => 'Inativo',
        ])->attrs(['class' => 'form-select'])
            ->value($statusValue)
            ->required() !!}
    </div>

    <div class="col-md-6 col-12">
        {!! Form::text('email', 'E-mail profissional')
            ->placeholder('nome.sobrenome@clinicavet.com')
            ->attrs(['maxlength' => 255, 'type' => 'email'])
            ->value(old('email', optional($medico ?? null)->email)) !!}
    </div>

    <div class="col-md-6 col-12">
        {!! Form::text('telefone', 'Telefone de contato')
            ->placeholder('(00) 00000-0000')
            ->attrs(['maxlength' => 30])
            ->value(old('telefone', optional($medico ?? null)->telefone)) !!}
    </div>

    <div class="col-12">
        {!! Form::textarea('observacoes', 'Observações')
            ->placeholder('Informações adicionais, horários preferenciais, certificações...')
            ->attrs(['rows' => 4, 'style' => 'resize:none;'])
            ->value(old('observacoes', optional($medico ?? null)->observacoes)) !!}
    </div>
</div>

<div class="d-flex justify-content-end gap-2 mt-4">
    <button type="submit" class="btn btn-success px-5">
        <i class="ri-save-3-fill"></i>
        Salvar
    </button>
</div>
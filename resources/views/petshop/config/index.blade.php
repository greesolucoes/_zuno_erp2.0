@extends('default.layout', ['title' => 'Configurações Pet Shop'])

@section('content')
    <div class="page-content">
        <div class="card">
            <div class="card-body p-4">
                <div class="page-breadcrumb d-sm-flex align-items-center mb-3">
                    <div class="ms-auto"></div>
                </div>

                <div class="col">
                    <h6 class="mb-0 text-uppercase">Configurações do Pet Shop</h6>

                    <p class="mt-3 mb-1">Link público para o formulário do cliente:</p>
                    <p class="mb-3"><a href="{{ $link }}" target="_blank">{{ $link }}</a></p>

                    {!! Form::open()->post()->route('petshop.config.store') !!}
                        <div class="form-check form-switch mt-3">
                            <input class="form-check-input" type="checkbox" id="usar-agendamento-alternativo"
                                name="usar_agendamento_alternativo" value="1"
                                {{ $config->usar_agendamento_alternativo ? 'checked' : '' }}>
                            <label class="form-check-label" for="usar-agendamento-alternativo">Usar agendamento alternativo?</label>
                        </div>

                        <div id="horarios-container" class="mt-3 {{ $config->usar_agendamento_alternativo ? '' : 'd-none' }}">
                            <table class="table mb-0 table-striped">
                                <thead>
                                    <tr>
                                        <th>Dia da semana</th>
                                        <th>Início</th>
                                        <th>Fim</th>
                                        <th></th>
                                    </tr>
                                </thead>
                                <tbody id="horarios-body">
                                    @foreach($horarios as $index => $horario)
                                        <tr>
                                            <td>
                                                <select name="horarios[{{ $index }}][dia_semana]" class="form-select">
                                                    <option value="">Selecione</option>
                                                    @foreach(['Domingo','Segunda','Terça','Quarta','Quinta','Sexta','Sábado'] as $i => $dia)
                                                        <option value="{{ $i }}" {{ $horario->dia_semana == $i ? 'selected' : '' }}>{{ $dia }}</option>
                                                    @endforeach
                                                </select>
                                            </td>
                                            <td><input type="time" name="horarios[{{ $index }}][hora_inicio]" value="{{ $horario->hora_inicio }}" class="form-control"></td>
                                            <td><input type="time" name="horarios[{{ $index }}][hora_fim]" value="{{ $horario->hora_fim }}" class="form-control"></td>
                                            <td><button type="button" class="btn btn-danger btn-sm remove-horario">X</button></td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                            <div class="mt-2">
                                <button type="button" class="btn btn-primary btn-sm" id="add-horario">Adicionar horário</button>
                            </div>
                        </div>

                        <div class="mt-4">
                            <button type="submit" class="btn btn-success px-5">Salvar</button>
                        </div>
                    {!! Form::close() !!}
                </div>
            </div>
        </div>
    </div>
@endsection

@section('js')
    <script>
        const toggleContainer = () => {
            document.getElementById('horarios-container').classList.toggle('d-none', !document.getElementById('usar-agendamento-alternativo').checked);
        }

        document.getElementById('usar-agendamento-alternativo').addEventListener('change', toggleContainer);
        toggleContainer();

        document.getElementById('add-horario').addEventListener('click', function () {
            const tbody = document.getElementById('horarios-body');
            const index = tbody.children.length;
            const row = document.createElement('tr');
            row.innerHTML = `
                <td>
                    <select name="horarios[${index}][dia_semana]" class="form-select">
                        <option value="">Selecione</option>
                        ${['Domingo','Segunda','Terça','Quarta','Quinta','Sexta','Sábado'].map((d,i) => `<option value=\"${i}\">${d}</option>`).join('')}
                    </select>
                </td>
                <td><input type="time" name="horarios[${index}][hora_inicio]" class="form-control"></td>
                <td><input type="time" name="horarios[${index}][hora_fim]" class="form-control"></td>
                <td><button type="button" class="btn btn-danger btn-sm remove-horario">X</button></td>
            `;
            tbody.appendChild(row);
        });

        document.addEventListener('click', function (e) {
            if (e.target.classList.contains('remove-horario')) {
                e.target.closest('tr').remove();
            }
        });
    </script>
@endsection

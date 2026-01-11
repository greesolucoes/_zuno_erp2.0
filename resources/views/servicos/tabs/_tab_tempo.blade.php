<h4 class="text-color required mb-3">Tempo de execução</h4>

<table class="table table-dynamic table-duracao-servico table-responsive">
    <thead>
        <tr>
            <th>Dias</th>
            <th>Horas</th>
            <th>Minutos</th>
        </tr>
    </thead>
    <tbody>
        <tr>
            <td>
                {!!
                    Form::tel('dias', '')
                    ->attrs([
                        'data-mask' => '0000'
                    ])
                    ->value(isset($dias) && $dias > 0 ? $dias : null)
                    ->placeholder('0 dias')
                !!}
            </td>
            <td>
                {!!
                    Form::tel('horas', '')
                    ->attrs([
                        'data-mask' => '00'
                    ])
                    ->value(isset($horas) && $horas > 0 ? $horas : null)
                    ->placeholder('0 horas')
                !!}
            </td>
            <td>
                {!!
                    Form::tel('minutos', '')
                    ->attrs([
                        'data-mask' => '00'
                    ])
                    ->value(isset($minutos) && $minutos > 0 ? $minutos : null)
                    ->placeholder('0 minutos')
                !!}
            </td>
        </tr>
    </tbody>
</table>
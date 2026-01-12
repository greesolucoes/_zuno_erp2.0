<div class="row g-3">
  <div class="col-md-4 col-12 d-flex align-items-end gap-2 ">
    <div class="w-100">
      {!!Form::text('nome', 'Nome do pet')->placeholder('Digite o nome do pet')->required()!!}
    </div>
  </div>

  @if(isset($clientes))
  <div class="col-md-4 col-12 d-flex align-items-end gap-2 form__cliente">
    <div class="w-100">
      @isset($item)
      {!!Form::select('cliente_id', 'Cliente', [$item->cliente->id => $item->cliente->razao_social] + $clientes->pluck('razao_social', 'id')->all())
      ->attrs(['class' => 'form-select select2'])
      ->required()
      !!}
      @else
      {!!Form::select('cliente_id', 'Cliente', ['' => 'Selecione'] + $clientes->pluck('razao_social', 'id')->all())
      ->attrs(['class' => 'form-select select2'])
      ->required()
      !!}
      @endif
    </div>
    <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#modal_novo_cliente" type="button">
      <i class="bx bx-plus"></i>
    </button>
  </div>

  @endif

  <hr>
  
  @if(isset($especies))
  <div class="col-md-3 col-6 d-flex align-items-end gap-2">
    <div class="w-100">
      {!!Form::select('especie_id', 'Espécie', ['' => 'Selecione a espécie'] + $especies->pluck('nome', 'id')->all())
        ->required()
        ->attrs(['class' => 'form-select select2'])
        !!}
      </div>
      <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#modal_especie" type="button">
      <i class="bx bx-plus"></i>
      
    </button>
  </div>
  @endif
  
  @if(isset($racas))
  <div class="col-md-3 col-6 d-flex align-items-end gap-2">
    <div class="w-100">
      {!!Form::select('raca_id', 'Raça', ['' => 'Selecione a raça'] + $racas->pluck('nome', 'id')->all())
        ->required()
        ->disabled()
        ->attrs(['class' => 'form-select select2'])
        !!}
      </div>
      <button 
        class="btn btn-primary" data-bs-toggle="modal"  data-bs-target="#modal_raca" type="button" disabled  id="btn-nova-raca">
      <i class="bx bx-plus"></i>
    </button>
  </div>
  @endif
  
  @if(isset($pelagens))
    <div class="col-md-3 col-6 d-flex align-items-end gap-2">
      <div class="w-100">
        {!!Form::select('pelagem_id', 'Pelagem', ['' => 'Selecione a pelagem'] + $pelagens->pluck('nome', 'id')
          ->all())
          ->attrs(['class' => 'form-select select2'])
        !!}
      </div>
      <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#modal_pelagem"
        type="button">
        <i class="bx bx-plus"></i>
      </button>
    </div>
  @endif
      <div class="col-md-3 col-6 d-flex align-items-end gap-2">
        <div class="w-100">
        {!! Form::text('cor', 'Cor')
          ->value(isset($item) ? $item->cor : '')
          ->attrs(['class' => 'text-uppercase'])
          ->placeholder('Digite a Cor')
        !!}
        </div>
      </div>
  <hr>
  
  <div class="col-md-2 col-6">
    {!!Form::select('sexo', 'Sexo', ['' => 'Selecione', 'M' => 'Macho', 'F' => 'Fêmea', 'I' => 'Indefinido'])
    ->required()
    ->attrs(['class' => 'form-select select2'])
    !!}
  </div>

  <div class="col-md-2 col-6">
    {!!Form::text('peso', 'Peso')->placeholder('Digite o peso')!!}
  </div>

  <div class="col-md-2 col-6">
    {!!
      Form::text('porte', 'Porte')
      ->placeholder('Digite o porte')
      ->required()
      ->attrs(['class' => 'text-uppercase'])
    !!}
  </div>

  <div class="col-md-2 col-6">
    {!!Form::text('origem', 'Origem')
      ->value(isset($item) ? $item->origem : '')
      ->placeholder('Digite a origem')!!}
  </div>

  <hr>

  <div class="col-md-2 col-6">
    {!!
      Form::date('data_nascimento_pet', 'Data de nascimento')
      ->value(isset($item) ? $item->data_nascimento : '')
    !!}
  </div>

  <div class="col-md-2 col-6">
    {!!Form::text('chip', 'Chip')
    ->value(isset($item) ? $item->chip : '')
    ->placeholder('Digite chip')!!}
  </div>

  <div class="col-md-2 col-6">
    {!!Form::select('tem_pedigree', 'Possui pedigree?', ['' => 'Selecione', 'S' => 'Sim', 'N' => 'Não'])
    ->required()
    ->attrs(['class' => 'form-select select2'])
    !!}
  </div>

  <div class="col-md-2 col-6">
    {!!Form::text('pedigree', 'Número do pedigree')->placeholder('Digite o número do pedigree')!!}
  </div>

  <hr>

  <div class="col-md-6">
    {!!Form::textarea('observacao', 'Observações')
    ->value(isset($item) ? $item->observacao : '')
    ->attrs(['rows' => '6', 'style' => 'resize:none;'])
    ->placeholder('Digite as observações')!!}
  </div>

  <div class="col-12">
    <button type="submit" class="btn btn-primary px-5" id="btn-store">Salvar</button>
  </div>
</div>

@section('js')
<script type="text/javascript" src="/js/novo_pet.js"></script>
<script type="text/javascript" src="/js/novo_cliente.js"></script>
<script type="text/javascript">
  function ageCalculator(inputAge) {
    //collect input from HTML form and convert into date format
    var userinput = inputAge;
    var dob = new Date(userinput);

    //check user provide input or not
    if (userinput == null || userinput == '') {
      document.getElementById("message").innerHTML = "**Choose a date please!";
      return false;
    }

    //execute if the user entered a date
    else {
      //extract the year, month, and date from user date input
      var dobYear = dob.getYear();
      var dobMonth = dob.getMonth();
      var dobDate = dob.getDate();

      //get the current date from the system
      var now = new Date();
      //extract the year, month, and date from current date
      var currentYear = now.getYear();
      var currentMonth = now.getMonth();
      var currentDate = now.getDate();

      //declare a variable to collect the age in year, month, and days
      var age = {};
      var ageString = "";

      //get years
      yearAge = currentYear - dobYear;

      //get months
      if (currentMonth >= dobMonth)
        //get months when current month is greater
        var monthAge = currentMonth - dobMonth;
      else {
        yearAge--;
        var monthAge = 12 + currentMonth - dobMonth;
      }

      //get days
      if (currentDate >= dobDate)
        //get days when the current date is greater
        var dateAge = currentDate - dobDate;
      else {
        monthAge--;
        var dateAge = 31 + currentDate - dobDate;

        if (monthAge < 0) {
          monthAge = 11;
          yearAge--;
        }
      }
      //group the age in a single variable
      age = {
        years: yearAge,
        months: monthAge,
        days: dateAge
      };


      if ((age.years > 0) && (age.months > 0) && (age.days > 0))
        ageString = age.years + "." + age.months;
      else if ((age.years == 0) && (age.months == 0) && (age.days > 0))
        //    ageString = "Only " + age.days + " days old!";
        ageString = '0.0';
      //when current month and date is same as birth date and month
      else if ((age.years > 0) && (age.months == 0) && (age.days == 0))
        ageString = age.years + '.0';
      else if ((age.years > 0) && (age.months > 0) && (age.days == 0))
        ageString = age.years + "." + age.months;
      else if ((age.years == 0) && (age.months > 0) && (age.days > 0))
        ageString = '0.' + age.months;
      else if ((age.years > 0) && (age.months == 0) && (age.days > 0))
        ageString = age.years + ".0";
      else if ((age.years == 0) && (age.months > 0) && (age.days == 0))
        ageString = '0.' + age.months;
      //when current date is same as dob(date of birth)
      else ageString = "Digite uma data válida";

      //display the calculated age
      return ageString;

    }
  }

  $("#inp-data_nascimento").blur(function() {
    const age = ageCalculator($("#inp-data_nascimento").val());

    $("#inp-idade").val(age);
  });
</script>
@endsection

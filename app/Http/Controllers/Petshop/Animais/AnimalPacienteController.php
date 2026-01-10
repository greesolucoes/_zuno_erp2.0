<?php

namespace App\Http\Controllers\Petshop\Animais;

use App\Http\Controllers\Controller;
use App\Imports\ProdutoImport;
use App\Models\Agendamento;
use App\Models\Cliente;
use App\Models\Petshop\Animal;
use App\Models\Petshop\Consulta;
use App\Models\Petshop\Especie;
use App\Models\Petshop\Pelagem;
use App\Models\Petshop\Raca;
use App\Models\Petshop\Vacinacao;
use App\Services\Petshop\Vet\HistoricoMedicoPacienteService;
use App\Services\LogService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Maatwebsite\Excel\Facades\Excel;

class AnimalPacienteController extends Controller
{
  public function index(Request $request)
  {

    $empresaId = Auth::user()?->empresa?->empresa_id;

    $pesquisa = $request->input('pesquisa');

    $query = Animal::where('empresa_id', $empresaId)
      ->withCount(['vacinacoes as vaccine_cards_count' => function ($q) use ($empresaId) {
        $q->where('empresa_id', $empresaId);
      }])
      ->when($pesquisa, function ($q) use ($pesquisa) {
        // Abre um grupo de OR para as condições de pesquisa
        $q->where(function ($subQuery) use ($pesquisa) {
          $subQuery->where('nome', 'LIKE', "%{$pesquisa}%") // Busca no nome do animal
            ->orWhereHas('cliente', function ($clienteQuery) use ($pesquisa) {
              // OU busca no relacionamento do cliente
              $clienteQuery->where('razao_social', 'LIKE', "%{$pesquisa}%")
                ->orWhere('nome_fantasia', 'LIKE', "%{$pesquisa}%");
            });
        });
      });

    $data = $query->paginate(env("PAGINACAO"))->appends($request->all());

    return view('petshop.animais.pacientes.index', compact('data'));
  }

  public function crm(Request $request, HistoricoMedicoPacienteService $historyService, $id)
  {
    $empresaId = Auth::user()?->empresa?->empresa_id;

    $animal = Animal::with(['cliente', 'especie', 'raca'])
      ->where('empresa_id', $empresaId)
      ->findOrFail($id);

    __validaObjetoEmpresa($animal);

    $year = (int) $request->input('year', now()->year);

    $timeline = $historyService->build($empresaId, $animal, $year);

    return view('petshop.animais.pacientes.crm', [
      'animal' => $animal,
      'selectedYear' => $timeline['selectedYear'],
      'availableYears' => $timeline['availableYears'],
      'timeline' => $timeline['timeline'],
      'stats' => $timeline['stats'],
      'hasEvents' => $timeline['hasEvents'],
    ]);
  }

  public function create()
  {
    $empresa_id = Auth::user()?->empresa?->empresa_id;

    $pelagens = Pelagem::where('empresa_id', $empresa_id)->get();
    $especies = Especie::where('empresa_id', $empresa_id)->get();
    $racas    = Raca::where('empresa_id', $empresa_id)->get();
    $clientes = Cliente::where('empresa_id', $empresa_id)->get();


    return view('petshop.animais.pacientes.create', compact('pelagens', 'especies', 'racas', 'clientes'));
  }

  public function store(Request $request)
  {
    $empresa_id = Auth::user()?->empresa?->empresa_id;


    $request->validate([
      'cliente_id'      => 'required',
      'especie_id'      => 'required',
      'raca_id'         => 'required',
      'nome'            => 'required',
      'sexo'            => 'required',
      'tem_pedigree'    => 'required',
      'porte'           => 'required',
    ]);

    try {
      Animal::create([
        'cliente_id'      => $request->cliente_id,
        'especie_id'      => $request->especie_id,
        'raca_id'         => $request->raca_id,
        'pelagem_id'      => $request->pelagem_id,
        'cor'             => $request->cor,
        'nome'            => $request->nome,
        'data_nascimento' => $request->data_nascimento_pet,
        'peso'            => $request->peso,
        'sexo'            => $request->sexo,
        'idade'           => Carbon::parse($request->data_nascimento_pet)->age,
        'tem_pedigree'    => $request->tem_pedigree === 'S' ? true : false,
        'porte'           => $request->porte,
        'chip'            => $request->chip,
        'pedigree'        => $request->pedigree,
        'origem'          => $request->origem,
        'observacao'      => $request->observacao,
        'empresa_id'      => $empresa_id,
      ]);

      session()->flash("flash_success", "Paciente cadastrado com sucesso!");
    } catch (\Exception $e) {
      session()->flash("flash_error", "Algo deu errado: " . $e->getMessage());
    }

    return redirect()->route('animais.pacientes.index');
  }

  public function edit($id)
  {
    $empresa_id = Auth::user()?->empresa?->empresa_id;

    $item = Animal::findOrFail($id);
    __validaObjetoEmpresa($item);

    $pelagens = Pelagem::where('empresa_id', $empresa_id)->get();
    $especies = Especie::where('empresa_id', $empresa_id)->get();
    $racas = Raca::where('empresa_id', $empresa_id)->get();
    $clientes = Cliente::where('empresa_id', $empresa_id)->get();

    $item->tem_pedigree = $item->tem_pedigree ? 'S' : 'N';

    $data = Vacinacao::where('empresa_id', $empresa_id)
      ->where('animal_id', $id)
      ->with('animal')
      ->paginate(env("PAGINACAO"));

    $consultas = Consulta::where('empresa_id', $empresa_id)
      ->where('animal_id', $id)
      ->with('animal')
      ->paginate(env("PAGINACAO"));

    $agendamentos = Agendamento::where('empresa_id', $empresa_id)
      ->where('animal_id', $id)
      ->with('animal')
      ->paginate(env("PAGINACAO"));

    return view('petshop.animais.pacientes.edit', compact('item', 'pelagens', 'especies', 'racas', 'clientes', 'data', "consultas", "agendamentos"));
  }

  public function update(Request $request, $id)
  {

    $request->validate([
      'cliente_id'      => 'required',
      'especie_id'      => 'required',
      'raca_id'         => 'required',
      'nome'            => 'required',
      'sexo'            => 'required',
      'tem_pedigree'    => 'required',
      'porte'           => 'required',
    ]);

    try {
      $item = Animal::findOrFail($id);
      __validaObjetoEmpresa($item);

      $item->update([
        'cliente_id'      => $request->cliente_id,
        'especie_id'      => $request->especie_id,
        'raca_id'         => $request->raca_id,
        'pelagem_id'      => $request->pelagem_id,
        'cor'             => $request->cor,
        'nome'            => $request->nome,
        'data_nascimento' => $request->data_nascimento_pet,
        'peso'            => $request->peso,
        'sexo'            => $request->sexo,
        'idade'           => $request->idade,
        'tem_pedigree'    => $request->tem_pedigree === 'S' ? true : false,
        'porte'           => $request->porte,
        'chip'            => $request->chip,
        'pedigree'        => $request->pedigree,
        'origem'          => $request->origem,
        'observacao'      => $request->observacao,
      ]);

      session()->flash("flash_success", "Paciente atualizado com sucesso!");
    } catch (\Exception $e) {
      session()->flash("flash_error", "Algo deu errado: " . $e->getMessage());
    }

    return redirect()->route('animais.pacientes.index');
  }

  public function import(Request $request)
  {
    return view('petshop.animais.pacientes.import');
  }

  public function downloadModelo()
  {
      return response()->download(base_path('/public/files/') . 'import_pets_csv_template.xlsx');
  }

  /**
   * Cadastra os pets passados pela planilha no banco de dados
   *
   * @param Request $request
  */
  public function storePlanilha(Request $request)
  {
    if ($request->hasFile('file')) {
      ini_set('max_execution_time', 0);
      ini_set('memory_limit', -1);

      $rows = Excel::toArray(new ProdutoImport, $request->file);

      $validation_errors = $this->__validatePlanilha($rows);

      $imported_count = 0;
      $duplicated_count = 0;

      if ($validation_errors) {
        session()->flash('flash_error', $validation_errors);
        return redirect()->route('animais.pacientes.import');
      }

      foreach ($rows as $key => $row) {
        foreach ($row as $r) {
          if ($r[0] != 'NOME DO PET' && isset($r[1])) {
            $data = $this->preparaPlanilha($r, $request->empresa_id);

            $duplicate_animal = Animal::where('empresa_id', request()->empresa_id)
              ->where('nome', $data['nome'])
              ->where('sexo', $data['sexo'])
              ->where('porte', $data['porte'])
              ->where('data_nascimento', $data['data_nascimento'])
              ->where('cliente_id', $data['cliente_id'])
              ->where('especie_id', $data['especie_id'])
              ->where('raca_id', $data['raca_id'])
            ->first();

            if (isset($duplicate_animal)) {
              $duplicated_count++;

              continue;
            } else {
              try {
                Animal::create($data);

                $imported_count++;
              } catch (\Exception $e) {
                LogService::logMessage($e->getMessage(), 'ERROR');
                __createLog(request()->empresa_id, 'Importar Pets', 'importar pet', $e->getMessage());

                if ($imported_count > 0) {
                  session()->flash('flash_success', 'Total de animais importados: ' . $imported_count);
                }

                if ($duplicated_count > 0) {
                  session()->flash('flash_warning', 'Total de animais duplicados: ' . $duplicated_count);
                }

                session()->flash('flash_error', 'Algo deu errado ao importar o animal da linha: ' . $key . '.');

                return redirect()->route('animais.pacientes.import');
              }
            }
          }
        }

        session()->flash('flash_success', 'Total de animais importados: ' . $imported_count);
        if ($duplicated_count > 0) {
          session()->flash('flash_warning', 'Total de animais duplicados: ' . $duplicated_count);
        }

        return redirect()->route('animais.pacientes.index');
      }
    }
  }

  /**
    * Prepara os dados da planilha para importar os dados no banco de dados

    * @param array $row Linha da planilha com as informações do pet

    * @param int $empresa_id ID da empresa

    * @return array Informações do pet formatadas para criação deles no banco de dados
  */
  private function preparaPlanilha($row, $empresa_id)
  {
    // Preparação do cliente

    $cliente = null;

    $cpf_cnpj = trim((string) $row[2]);
    $mask = '##.###.###/####-##';

    if (strlen($cpf_cnpj) == 11) {
      $mask = '###.###.###.##';
    }
    if (!str_contains($cpf_cnpj, ".")) {
      $cpf_cnpj = __mask($cpf_cnpj, $mask);
    }

    $cliente = Cliente::where('empresa_id', $empresa_id)
      ->where('cpf_cnpj', $cpf_cnpj)
    ->first();

    if (!isset($cliente)) {
      $cliente = Cliente::create([
        'empresa_id' => $empresa_id,
        'razao_social' => trim((string) $row[1]),
        'nome_fantasia' => trim((string) $row[1]),
        'cpf_cnpj' => $cpf_cnpj,
      ]);
    }

    // Preparação da pelagem

    $pelagem = null;

    $pelagem = Pelagem::where('empresa_id', $empresa_id)
      ->where('nome', trim((string) $row[3]))
    ->first();

    if (!isset($pelagem)) {
      $pelagem = Pelagem::create([
        'empresa_id' => $empresa_id,
        'nome' => trim((string) $row[3]),
      ]);
    }

    // Preparação da espécie

    $especie = null;

    $especie = Especie::where('empresa_id', $empresa_id)
      ->where('nome', trim((string) $row[5]))
    ->first();

    if (!isset($especie)) {
      $especie = Especie::create([
        'empresa_id' => $empresa_id,
        'nome' => trim((string) $row[5]),
      ]);
    }

    // Preparação da raça

    $raca = null;

    $raca = Raca::where('empresa_id', $empresa_id)
      ->where('especie_id', $especie->id)
      ->where('nome', $row[6])
    ->first();

    if (!isset($raca)) {
      $raca = Raca::create([
        'empresa_id' => $empresa_id,
        'especie_id' => $especie->id,
        'nome' => trim((string) $row[6]),
      ]);
    }

    // Nome do pet
    $nome = trim((string) $row[0]);
    $cor = ($row[4] == '') ? null : trim((string) $row[4]);
    $sexo = trim((string) $row[7]);
    $peso = ($row[8] == '') ? null : __convert_value_bd((string) $row[8]);
    $porte = trim((string) $row[9]);
    $origem = ($row[10] == '') ? null : trim((string) $row[10]);

    $data_nascimento = null;

    if(isset($row[11])){
      $data_nascimento = Carbon::parse(
        \PhpOffice\PhpSpreadsheet\Shared\Date::excelToDateTimeObject($row[11])->format('Y-m-d')
      )->format('Y-m-d');
    }


    $chip = ($row[12] == '') ? null : trim((string) $row[12]);
    $tem_pedigree = trim((string) $row[13]);
    $pedigree = ($row[14] == '') ? null : trim((string) $row[14]);
    $observacao = ($row[15] == '') ? null : trim((string) $row[15]);

    return [
      'nome' => $nome,
      'empresa_id' => $empresa_id,
      'cliente_id' => $cliente->id,
      'pelagem_id' => $pelagem->id,
      'cor'        => $cor,
      'especie_id' => $especie->id,
      'raca_id' => $raca->id,
      'sexo' => $sexo,
      'peso' => $peso,
      'porte' => $porte,
      'origem' => $origem,
      'data_nascimento' => $data_nascimento,
      'chip' => $chip,
      'tem_pedigree' => $tem_pedigree,
      'pedigree' => $pedigree,
      'observacao' => $observacao
    ];
  }

  /**
   * Valida os campos da planilha antes de seguir com a importação
   *
   * @param array $rows Linhas da planilha
   *
   * @return string Mensagens de erros encontradas durante a validação
   */
  private function __validatePlanilha($rows)
  {
    $cont = 1;
    $msg = '';

    foreach ($rows as $row)
    {
      foreach ($row as $r)
      {
        if ($r[0] != 'NOME DO PET') {
          $nome_pet = $r[0];
          $nome_cliente = $r[1];
          $cpf_cnpj_cliente = $r[2];
          $especie = $r[5];
          $raca = $r[6];
          $sexo = $r[7];
          $porte = $r[9];
          $tem_pedigree = $r[13];

          if (strlen($nome_pet) == 0) {
            $msg .= "Coluna 'Nome do Pet' em branco na linha: $cont | ";
          }

          if (strlen($nome_cliente) == 0) {
            $msg .= "Coluna 'Nome do Tutor' em branco na linha: $cont | ";
          }

          if (strlen($cpf_cnpj_cliente) == 0) {
            $msg .= "Coluna 'CPF do Tutor' em branco na linha: $cont | ";
          }

          if (strlen($especie) == 0) {
            $msg .= "Coluna 'Especie' em branco na linha: $cont | ";
          }

          if (strlen($raca) == 0) {
            $msg .= "Coluna 'Raça' em branco na linha: $cont | ";
          }

          if (strlen($sexo) == 0) {
            $msg .= "Coluna 'Sexo' em branco na linha: $cont | ";
          }

          if ((strlen($sexo) == 0) && ((strtoupper($sexo) != 'M') || (strtoupper($sexo)!= 'F'))) {
            $msg .= "A Coluna 'Sexo' deve ser somente 'M' ou 'F' na linha: $cont | ";
          }

          if (strlen($porte) == 0) {
            $msg .= "Coluna 'Porte' em branco na linha: $cont | ";
          }

          if ((strlen($tem_pedigree) == 0) && ((strtoupper($tem_pedigree) != '1') || (strtoupper($tem_pedigree)!= '0'))) {
            $msg .= "A Coluna 'Possui Pedigree' deve ser somente '1' ou '0' na linha: $cont | ";
          }

          if ($msg != "") {
              return $msg;
          }
        }
        $cont++;
      }
    }

    return $msg;
  }

  public function destroy($id)
  {
    try {
      $item = Animal::findOrFail($id);
      __validaObjetoEmpresa($item);

     $item->delete();

      session()->flash("flash_success", "Pet excluído com sucesso!");
    } catch (\Exception $e) {
        $erro_msg = $e->getMessage();

    if ($e->getCode() == '23000') {
        switch (true) {
            case str_contains($erro_msg, 'ordem_servicos_animal_id_foreign'):
                session()->flash("flash_error", "Não foi possível excluir este Pet, pois o mesmo está vinculado a uma ordem de serviço.");
                break;

            case str_contains($erro_msg, 'agendamentos_animal_id_foreign'):
                session()->flash("flash_error", "Não foi possível excluir este Pet, pois o mesmo está vinculado a um agendamento.");
                break;

            case str_contains($erro_msg, 'creches_animal_id_foreign'):
                session()->flash("flash_error", "Não foi possível excluir este Pet, pois o mesmo está vinculado a um serviço de creche.");
                break;

            case str_contains($erro_msg, 'hoteis_animal_id_foreign'):
                session()->flash("flash_error", "Não foi possível excluir este Pet, pois o mesmo está vinculado a um serviço de hotel.");
                break;

            default:
                session()->flash("flash_error", "Erro de integridade referencial (código 23000).");
                break;
    }

        return redirect()->route('animais.pacientes.index');
    }

      session()->flash("flash_error", "Algo deu errado: " . $e->getMessage());
    }

    return redirect()->route('animais.pacientes.index');
  }
}

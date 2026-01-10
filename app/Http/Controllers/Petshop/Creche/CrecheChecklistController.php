<?php

namespace App\Http\Controllers\Petshop\Creche;

use App\Http\Controllers\Controller;
use App\Models\Empresa;
use App\Models\Petshop\Creche;
use App\Models\Petshop\CrecheChecklist;
use App\Models\Petshop\Especie;
use App\Models\Petshop\Raca;
use App\Utils\UploadUtil;
use Dompdf\Dompdf;
use Illuminate\Http\Request;

class CrecheChecklistController extends Controller
{
    protected UploadUtil $uploadUtil;

    public function __construct(UploadUtil $uploadUtil)
    {
        $this->uploadUtil = $uploadUtil;
    }

    public function create(Request $request, $crecheId)
    {
        $creche = Creche::with(['animal', 'cliente'])->findOrFail($crecheId);
        $tipo = $request->get('tipo', 'entrada');
        $checklist = CrecheChecklist::where('creche_id', $creche->id)
            ->where('tipo', $tipo)
            ->first();

        $empresaId = auth()->user()->empresa->empresa_id ?? null;
        $especies = Especie::where('empresa_id', $empresaId)->get();
        $racas = Raca::where('empresa_id', $empresaId)->get();

        return view('creche_checklist.create', compact('creche', 'checklist', 'especies', 'racas', 'tipo'));
    }

    public function store(Request $request, $crecheId)
    {
        $creche = Creche::findOrFail($crecheId);

        $data = $request->except('_token', 'anexos', 'anexos_to_remove', 'anexos_url', 'tipo');
        $tipo = $request->get('tipo', 'entrada');
        $anexos = $request->input('anexos_url', []);

        if ($request->filled('anexos_to_remove')) {
            $anexos = array_filter($anexos, function ($anexo) use ($request) {
                return !in_array($anexo, $request->anexos_to_remove);
            });

            foreach ($request->anexos_to_remove as $removeUrl) {
                $path = ltrim(parse_url($removeUrl, PHP_URL_PATH), '/');
                $this->uploadUtil->unlinkImageByPath($path);
            }
        }

        if ($request->hasFile('anexos')) {
            foreach ($request->file('anexos') as $file) {
                $newAnexo = $this->uploadUtil->uploadFile($file, '/creche_checklist');
                $anexos[] = env('AWS_URL') . '/uploads/creche_checklist/' . $newAnexo;
            }
        }

        if (count($anexos) > 0) {
            $data['anexos'] = $anexos;
        }

        CrecheChecklist::updateOrCreate(
            ['creche_id' => $creche->id, 'tipo' => $tipo],
            [
                'empresa_id' => auth()->user()->empresa->empresa_id ?? null,
                'checklist' => $data,
            ]
        );

        $estado = $creche->estado;
        if ($tipo === 'entrada') {
            $estado = 'em_andamento';
        } elseif ($tipo === 'saida') {
            $estado = 'concluido';
        }

        $creche->update([
            'situacao_checklist' => true,
            'estado' => $estado,
        ]);

        if (!$request->imprimir) {
            return redirect()->route('creches.index')->with('flash_success', 'Checklist salvo com sucesso!');
        } else {
            return $this->imprimir($request, $crecheId);
        }
    }

    public function imprimir(Request $request, $id)
    {
        $creche = Creche::with('animal.cliente')->findOrFail($id);
        $item = CrecheChecklist::where('creche_id', $creche->id)
            ->where('tipo', $request->tipo)
            ->first();

        $config = Empresa::where('id', request()->empresa_id)->first();

        $animal = $creche->animal;

        $p = view('creche_checklist.imprimir', compact('config', 'item', 'animal'));

        $domPdf = new Dompdf(["enable_remote" => true]);
        $domPdf->loadHtml($p);
        $domPdf->setPaper("A4");
        $domPdf->render();

        $domPdf->stream("
            Checklist" . $request->tipo == 'entrada' ? ' de Entrada' : ' de Saída' . " animal.pdf",
            ["Attachment" => false]
        );
    }
}
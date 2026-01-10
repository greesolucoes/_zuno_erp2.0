<?php

namespace App\Http\Controllers\Petshop\Hotel;

use App\Http\Controllers\Controller;
use App\Models\Empresa;
use App\Models\Petshop\Hotel;
use App\Models\Petshop\HotelChecklist;
use Illuminate\Http\Request;
use App\Models\Petshop\Especie;
use App\Models\Petshop\Raca;
use App\Utils\UploadUtil;
use Dompdf\Dompdf;

class HotelChecklistController extends Controller
{
    protected UploadUtil $uploadUtil;

    public function __construct(UploadUtil $uploadUtil)
    {
        $this->uploadUtil = $uploadUtil;
    }

    public function create(Request $request, $hotelId)
    {
        $hotel = Hotel::with(['animal', 'cliente'])->findOrFail($hotelId);
        $tipo = $request->get('tipo', 'entrada');
        $checklist = HotelChecklist::where('hotel_id', $hotel->id)
            ->where('tipo', $tipo)
            ->first();

        $empresaId = auth()->user()->empresa->empresa_id ?? null;
        $especies = Especie::where('empresa_id', $empresaId)->get();
        $racas = Raca::where('empresa_id', $empresaId)->get();

        return view('hotel_checklist.create', compact('hotel', 'checklist', 'especies', 'racas','tipo'));
    
    }

    public function store(Request $request, $hotelId)
    {
        $hotel = Hotel::findOrFail($hotelId);

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
                $newAnexo = $this->uploadUtil->uploadFile($file, '/hotel_checklist');
                $anexos[] = env('AWS_URL') . '/uploads/hotel_checklist/' . $newAnexo;
            }
        }

        if (count($anexos) > 0) {
            $data['anexos'] = $anexos;
        }

        HotelChecklist::updateOrCreate(
            ['hotel_id' => $hotel->id, 'tipo' => $tipo],
            [
                'empresa_id' => auth()->user()->empresa->empresa_id ?? null,
                'checklist' => $data,
            ]
        );

        $estado = $hotel->estado;
        if ($tipo === 'entrada') {
            $estado = 'em_andamento';
        } elseif ($tipo === 'saida') {
            $estado = 'concluido';
        }

        $hotel->update([
            'situacao_checklist' => true,
            'estado' => $estado,
        ]);

        if (!$request->imprimir) {
            return redirect()->route('hoteis.index')->with('flash_success', 'Checklist salvo com sucesso!');
        } else {
            return $this->imprimir($request, $hotelId);
        }
    }

    public function imprimir(Request $request, $id)
    {
        $hotel = Hotel::with('animal.cliente')->findOrFail($id);
        $item = HotelChecklist::where('hotel_id', $hotel->id)
            ->where('tipo', $request->tipo)
            ->first();

        $animal = $hotel->animal;

        $config = Empresa::where('id', request()->empresa_id)->first();

        $p = view('hotel_checklist.imprimir', compact('config', 'item', 'animal'));

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
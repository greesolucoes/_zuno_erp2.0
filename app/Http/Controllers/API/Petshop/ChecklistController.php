<?php

namespace App\Http\Controllers\API\Petshop;

use App\Http\Controllers\Controller;
use App\Models\Petshop\Creche;
use App\Models\Petshop\CrecheChecklist;
use App\Models\Petshop\Hotel;
use App\Models\Petshop\HotelChecklist;
use App\Utils\UploadUtil;
use Illuminate\Http\Request;


class ChecklistController extends Controller
{
    protected UploadUtil $upload_util;

    public function __construct(UploadUtil $upload_util)
    {
        $this->upload_util = $upload_util;
    }

    public function updateOrCreate(Request $request)
    {
        $modulo = $request->input('modulo');

        $request->merge([
            'anexos_url' => collect($request->anexos_url ?? [])
                ->filter(fn ($item) => !is_null($item))
                ->values()
                ->toArray(),
        ]);

        if ($modulo === 'HOTEL') {
            return $this->updateOrCreateHotelChecklist($request);
        } elseif ($modulo === 'CRECHE') {
            return $this->updateOrCreateCrecheChecklist($request);
        } else {
            return response()->json([
                'success' => false,
                'message' => 'Módulo inválido para o checklist.'
            ], 400);
        }
    }

    private function updateOrCreateHotelChecklist(Request $request)
    {
        try {
            $hotel = Hotel::findOrFail($request->reserva_id);

            $data = $request->except('checklist_id', 'modulo', 'reserva_id', 'empresa_id', 'anexos', 'anexos_to_remove', 'anexos_url', 'tipo', 'go_print');

            $tipo = $request->get('tipo');

            $anexos = $request->input('anexos_url', []);

            if ($request->filled('anexos_to_remove')) {
                $anexos = array_filter($anexos, function ($anexo) use ($request) {
                    return !in_array($anexo, $request->anexos_to_remove);
                });

                foreach ($request->anexos_to_remove as $remove_url) {
                    $path = ltrim(parse_url($remove_url, PHP_URL_PATH), '/');
                    $this->upload_util->unlinkImageByPath($path);
                }
            }

            if ($request->hasFile('anexos')) {
                foreach ($request->file('anexos') as $file) {
                    $newAnexo = $this->upload_util->uploadFile($file, '/hotel_checklist');
                    $anexos[] = env('AWS_URL') . '/uploads/hotel_checklist/' . $newAnexo;
                }
            }

            if (count($anexos) > 0) {
                $data['anexos'] = $anexos;
            }

            HotelChecklist::updateOrCreate(
                ['hotel_id' => $hotel->id, 'tipo' => $tipo],
                [
                    'empresa_id' => $request->empresa_id ?? null,
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

            if ($request->go_print) {
                $print_url = route('hoteis.checklist.imprimir', [
                    'hotel' => $hotel->id,
                    'tipo' => $request->tipo,
                    'empresa_id' => $request->empresa_id
                ]);

                return response()->json([
                    'success' => true, 
                    'message' => 'Checklist atualizado com sucesso.',
                    'print_url' => $print_url
                ], 200);
            } else {
                return response()->json([
                    'success' => true, 
                    'message' => 'Checklist atualizado com sucesso.'
                ], 200);
            }

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Ocorreu um erro desconhecido ao atualizar o checklist...',
                'exception' => $e->getMessage()
            ], 500);
        }
    }

    private function updateOrCreateCrecheChecklist(Request $request)
    {
        try {
            $creche = Creche::findOrFail($request->reserva_id);

            $data = $request->except('checklist_id', 'modulo', 'reserva_id', 'empresa_id', 'anexos', 'anexos_to_remove', 'anexos_url', 'tipo', 'go_print');

            $tipo = $request->get('tipo');

            $anexos = $request->input('anexos_url', []);

            if ($request->filled('anexos_to_remove')) {
                $anexos = array_filter($anexos, function ($anexo) use ($request) {
                    return !in_array($anexo, $request->anexos_to_remove);
                });

                foreach ($request->anexos_to_remove as $remove_url) {
                    $path = ltrim(parse_url($remove_url, PHP_URL_PATH), '/');
                    $this->upload_util->unlinkImageByPath($path);
                }
            }

            if ($request->hasFile('anexos')) {
                foreach ($request->file('anexos') as $file) {
                    $newAnexo = $this->upload_util->uploadFile($file, '/creche_checklist');
                    $anexos[] = env('AWS_URL') . '/uploads/creche_checklist/' . $newAnexo;
                }
            }

            if (count($anexos) > 0) {
                $data['anexos'] = $anexos;
            }

            CrecheChecklist::updateOrCreate(
                ['creche_id' => $creche->id, 'tipo' => $tipo],
                [
                    'empresa_id' => $request->empresa_id ?? null,
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
            
            if ($request->go_print) {
                $print_url = route('creches.checklist.imprimir', [
                    'creche' => $creche->id,
                    'tipo' => $request->tipo,
                    'empresa_id' => $request->empresa_id
                ]);

                return response()->json([
                    'success' => true, 
                    'message' => 'Checklist atualizado com sucesso.',
                    'print_url' => $print_url
                ], 200);
            } else {
                return response()->json([
                    'success' => true, 
                    'message' => 'Checklist atualizado com sucesso.'
                ], 200);
            }
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Ocorreu um erro desconhecido ao atualizar o checklist...',
                'exception' => $e->getMessage()
            ], 500);
        }
    }
}
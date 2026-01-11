<?php

namespace App\Http\Controllers\API\Petshop;

use App\Http\Controllers\Controller;
use App\Models\Petshop\Turma;
use App\Services\TurmaService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class TurmaController extends Controller
{
    private TurmaService $turma_service;
     
    public function __construct(
        TurmaService $turma_service,
    ) {
        $this->turma_service = $turma_service;
    }

    public function search(Request $request)
    {
        $this->_validate($request, 'search');

        try {
            $data = Turma::where('empresa_id', $request->empresa_id)
                ->when(!empty($request->pesquisa), function ($query) use ($request) {
                    return $query->where('nome', 'like', '%' . $request->pesquisa . '%');
                })
                ->when(!empty($request->data_entrada) && !empty($request->data_saida), function ($query) use ($request) {
                    $query->withCount(['creches as reservas_ativas' => function ($q) use ($request) {
                        $q->where(function ($sub) use ($request) {
                            $sub->where('data_entrada', '<', $request->data_saida)
                                ->where('data_saida', '>', $request->data_entrada);
                        });
                    }])
                    ->havingRaw('reservas_ativas < turmas.capacidade');
                })
                ->where('status', 'disponivel')
                ->get();
            
            return response()->json([
                'success' => true,
                'data' => $data
            ], 200);
        } catch (\Exception $e) {
            __saveLogError($e, $request->empresa_id);
            return response()->json(['success' => false, 'message' => $e->getMessage()], 400);
        }
    }

    public function checkTurmaIsFree (Request $request)
    {
        $this->_validate($request, 'checkTurmaIsFree');

        $turma_data = (object) [
            'turma_id' => $request->turma_id,
            'empresa_id' => $request->empresa_id,
            'data_entrada' => $request->data_entrada,
            'data_saida' => $request->data_saida,
            'reserva_id' => $request->reserva_id
        ];

        $turma_is_free = !$this->turma_service->checkIfTurmaIsBusy($turma_data);
        
        return response()->json([
            'success' => $turma_is_free
        ]);
    }

    private function _validate(Request $request, string $context = 'search')
    {
        $rules = [
            'empresa_id' => 'required',
        ];
        $messages = [];

        if ($context === 'checkTurmaIsFree') {
            $rules = [
                'empresa_id' => 'required',
                'turma_id' => 'required',
                'data_entrada' => 'required',
                'data_saida' => 'required',
            ];
        }

        $this->validate($request, $rules, $messages);
    }
}

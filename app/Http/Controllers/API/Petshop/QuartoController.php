<?php

namespace App\Http\Controllers\API\Petshop;

use App\Http\Controllers\Controller;
use App\Models\Petshop\Quarto;
use App\Services\QuartoService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class QuartoController extends Controller
{
    public function search(Request $request)
    {
        $this->_validate($request, 'search');

        try {
            $data = Quarto::where('empresa_id', $request->empresa_id)
                ->when(!empty($request->pesquisa), function ($query) use ($request) {
                    return $query->where('nome', 'like', '%' . $request->pesquisa . '%');
                })
                ->when(!empty($request->checkin) && !empty($request->checkout), function ($query) use ($request) {
                    $query->withCount(['reservasHotel as reservas_ativas' => function ($q) use ($request) {
                        $q->where(function ($sub) use ($request) {
                            $sub->where('checkin', '<', $request->checkout)
                                ->where('checkout', '>', $request->checkin);
                        });
                    }])
                    ->havingRaw('reservas_ativas < quartos.capacidade');
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

    public function checkQuartoIsFree (Request $request)
    {
        $this->_validate($request, 'checkQuartoIsFree');

        $quarto_service = new QuartoService ();

        $quarto_data = (object) [
            'quarto_id' => $request->quarto_id,
            'empresa_id' => $request->empresa_id,
            'checkin' => $request->checkin,
            'checkout' => $request->checkout,
            'reserva_id' => $request->reserva_id
        ];

        $quarto_is_free = !$quarto_service->checkIfQuartoIsBusy($quarto_data);
        
        return response()->json([
            'success' => $quarto_is_free
        ]);
    }

    private function _validate(Request $request, string $context = 'search')
    {
        $rules = [
            'empresa_id' => 'required',
        ];
        $messages = [];

        if ($context === 'checkQuartoIsFree') {
            $rules = [
                'empresa_id' => 'required',
                'quarto_id' => 'required',
                'checkin' => 'required',
                'checkout' => 'required',
            ];
        }

        $this->validate($request, $rules, $messages);
    }
}

<?php

namespace App\Http\Controllers\API\Petshop;

use App\Http\Controllers\Controller;
use App\Models\Petshop\QuartoEvento;
use Illuminate\Http\Request;

class QuartoEventoController extends Controller
{
    public function isRangeDateForQuartoFree(Request $request)
    {
        $this->_validate($request);

        try {
            $is_free = QuartoEvento::where('quarto_id', $request->quarto_id)
                ->when($request->id, function ($query) use ($request) {
                    return $query->whereNot('id', $request->id);
                })
                ->where(function ($query) use ($request) {
                    $query->where('inicio', '<', $request->fim)
                        ->where('fim', '>', $request->inicio);
                })
                ->exists();

            return response()->json([
                'success' => !$is_free
            ], 200);
        } catch (\Exception $e) {
            __saveLogError($e, $request->empresa_id ?? request()->empresa_id);
            return response()->json(['success' => false, 'message' => $e->getMessage()], 400);
        }
    }

    private function _validate(Request $request)
    {
        $rules = [
            'quarto_id' => 'required',
            'inicio' => 'required',
            'fim' => 'required',
        ];
        $messages = [];
        $this->validate($request, $rules, $messages);
    }
}

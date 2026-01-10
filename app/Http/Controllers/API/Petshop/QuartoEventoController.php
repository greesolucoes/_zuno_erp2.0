<?php

namespace App\Http\Controllers\API\Petshop;

use App\Http\Controllers\Controller;
use App\Models\Petshop\QuartoEvento;
use Illuminate\Http\Request;

class QuartoEventoController extends Controller
{
    public function isRangeDateForQuartoFree(Request $request)
    {
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
        ]);
    }
}

<?php

namespace App\Http\Controllers\Petshop\Estetica;

use App\Http\Controllers\Controller;

class EsteticaConfigController extends Controller
{
    /**
     * Display the esteticista configuration page.
     */
    public function index()
    {
        return view('estetica_config.index');
    }
}

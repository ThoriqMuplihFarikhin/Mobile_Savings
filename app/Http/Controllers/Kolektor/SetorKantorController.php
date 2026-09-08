<?php

namespace App\Http\Controllers\Kolektor;

use App\Http\Controllers\Controller;

class SetorKantorController extends Controller
{
    public function index()
    {
        return view('pages.kolektor.setor-kantor');
    }
}

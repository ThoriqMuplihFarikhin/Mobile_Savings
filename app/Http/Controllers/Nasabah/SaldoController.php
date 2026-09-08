<?php

namespace App\Http\Controllers\Nasabah;

use App\Http\Controllers\Controller;

class SaldoController extends Controller
{
    public function index()
    {
        return view('pages.nasabah.saldo');
    }
}

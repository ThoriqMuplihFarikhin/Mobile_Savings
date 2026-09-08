<?php

namespace App\Http\Controllers\Kolektor;

use App\Http\Controllers\Controller;

class DaftarNasabahController extends Controller
{
    public function index()
    {
        return view('pages.kolektor.daftar-nasabah');
    }
}

<?php

namespace App\Http\Controllers\Kolektor;

use App\Http\Controllers\Controller;
use Illuminate\View\View;

class DaftarNasabahController extends Controller
{
    public function index(): View
    {
        return view('pages.kolektor.daftar-nasabah');
    }
}

<?php

namespace App\Http\Controllers\Kolektor;

use App\Http\Controllers\Controller;

class RiwayatAbsensiController extends Controller
{
    public function index()
    {
        return view('pages.kolektor.riwayat-absensi');
    }
}

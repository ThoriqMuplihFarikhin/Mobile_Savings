<?php

namespace App\Http\Controllers\Nasabah;

use App\Http\Controllers\Controller;
use Illuminate\View\View;

class RiwayatController extends Controller
{
    public function index(): View
    {
        return view('pages.nasabah.riwayat');
    }

    public function riwayatTabungan(): View
    {
        return view('pages.nasabah.riwayat-tabungan');
    }

    public function progresPaket(): View
    {
        return view('pages.nasabah.progres-paket');
    }

    public function komplain(): View
    {
        return view('pages.nasabah.komplain');
    }
}

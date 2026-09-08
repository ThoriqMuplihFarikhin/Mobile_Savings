<?php

namespace App\Http\Controllers\Nasabah;

use App\Http\Controllers\Controller;

class RiwayatController extends Controller
{
    public function index()
    {
        return view('pages.nasabah.riwayat');
    }

    public function riwayatTabungan()
    {
        return view('pages.nasabah.riwayat-tabungan');
    }

    public function progresPaket()
    {
        return view('pages.nasabah.progres-paket');
    }

    public function komplain()
    {
        return view('pages.nasabah.komplain');
    }
}

<?php

namespace App\Http\Controllers\Kolektor;

use App\Http\Controllers\Controller;

class SetoranController extends Controller
{
    public function index()
    {
        return view('pages.kolektor.setoran');
    }

    public function penarikanOffline()
    {
        return view('pages.kolektor.penarikan-offline');
    }

    public function verifikasiPenarikan()
    {
        return view('pages.kolektor.verifikasi-penarikan');
    }
}

<?php

namespace App\Http\Controllers\Kolektor;

use App\Http\Controllers\Controller;
use Illuminate\View\View;

class SetoranController extends Controller
{
    public function index(): View
    {
        return view('pages.kolektor.setoran');
    }

    public function penarikanOffline(): View
    {
        return view('pages.kolektor.penarikan-offline');
    }

    public function verifikasiPenarikan(): View
    {
        return view('pages.kolektor.verifikasi-penarikan');
    }
}

<?php

namespace App\Http\Controllers\Kolektor;

use App\Http\Controllers\Controller;
use Illuminate\View\View;

class TransaksiHariIniController extends Controller
{
    public function index(): View
    {
        return view('pages.kolektor.transaksi');
    }
}

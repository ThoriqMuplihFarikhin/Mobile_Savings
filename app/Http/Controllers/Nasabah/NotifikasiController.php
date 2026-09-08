<?php

namespace App\Http\Controllers\Nasabah;

use App\Http\Controllers\Controller;

class NotifikasiController extends Controller
{
    public function index()
    {
        return view('pages.nasabah.notifikasi');
    }
}

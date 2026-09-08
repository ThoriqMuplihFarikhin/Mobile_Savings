<?php

namespace App\Http\Controllers\Nasabah;

use App\Http\Controllers\Controller;

class PengaturanController extends Controller
{
    public function index()
    {
        return view('pages.nasabah.pengaturan');
    }
}

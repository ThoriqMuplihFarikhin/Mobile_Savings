<?php

namespace App\Http\Controllers\Kolektor;

use App\Http\Controllers\Controller;

class PengaturanController extends Controller
{
    public function index()
    {
        return view('pages.kolektor.pengaturan');
    }
}

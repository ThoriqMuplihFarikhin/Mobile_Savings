<?php

namespace App\Http\Controllers\Kolektor;

use App\Http\Controllers\Controller;

class JadwalController extends Controller
{
    public function index()
    {
        return view('pages.kolektor.jadwal');
    }
}

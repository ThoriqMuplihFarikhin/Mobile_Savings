<?php

namespace App\Http\Controllers\Kolektor;

use App\Http\Controllers\Controller;

class AbsenController extends Controller
{
    public function index()
    {
        return view('pages.kolektor.absen');
    }
}

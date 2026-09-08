<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;

class VerifikasiController extends Controller
{
    public function index()
    {
        return view('pages.admin.verifikasi');
    }
}

<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\View\View;

class VerifikasiController extends Controller
{
    public function index(): View
    {
        return view('pages.admin.verifikasi');
    }
}

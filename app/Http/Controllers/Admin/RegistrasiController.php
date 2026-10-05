<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\View\View;

class RegistrasiController extends Controller
{
    public function index(): View
    {
        return view('pages.admin.registrasi');
    }
}

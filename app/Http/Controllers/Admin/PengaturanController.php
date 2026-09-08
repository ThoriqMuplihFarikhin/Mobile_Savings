<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;

class PengaturanController extends Controller
{
    public function index()
    {
        return view('pages.admin.pengaturan');
    }
}

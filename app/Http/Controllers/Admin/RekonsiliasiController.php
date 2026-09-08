<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;

class RekonsiliasiController extends Controller
{
    public function index()
    {
        return view('pages.admin.rekonsiliasi');
    }
}

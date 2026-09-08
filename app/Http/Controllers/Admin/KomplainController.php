<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;

class KomplainController extends Controller
{
    public function index()
    {
        return view('pages.admin.komplain');
    }
}

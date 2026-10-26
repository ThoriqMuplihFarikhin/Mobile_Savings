<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\View\View;

class InputSetoranController extends Controller
{
    public function index(): View
    {
        return view('pages.admin.input-setoran');
    }
}

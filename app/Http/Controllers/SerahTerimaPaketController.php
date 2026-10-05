<?php

namespace App\Http\Controllers;

use Illuminate\View\View;

class SerahTerimaPaketController extends Controller
{
    public function index(): View
    {
        return view('serah-terima-paket');
    }
}

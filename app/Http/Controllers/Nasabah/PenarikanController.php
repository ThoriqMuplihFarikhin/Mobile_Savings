<?php

namespace App\Http\Controllers\Nasabah;

use App\Http\Controllers\Controller;
use Illuminate\View\View;

class PenarikanController extends Controller
{
    public function index(): View
    {
        return view('pages.nasabah.penarikan');
    }
}

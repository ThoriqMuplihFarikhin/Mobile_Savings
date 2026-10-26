<?php

namespace App\Http\Controllers\Nasabah;

use App\Http\Controllers\Controller;
use Illuminate\View\View;

class NotifikasiController extends Controller
{
    public function index(): View
    {
        return view('pages.nasabah.notifikasi');
    }
}

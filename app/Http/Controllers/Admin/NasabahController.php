<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\View\View;

class NasabahController extends Controller
{
    public function index(): View
    {
        return view('pages.admin.nasabah');
    }

    public function kolektor(): View
    {
        return view('pages.admin.kolektor');
    }

    public function bermasalah(): View
    {
        return view('pages.admin.bermasalah');
    }

    public function monitoringSetoran(): View
    {
        return view('pages.admin.monitoring-setoran');
    }
}

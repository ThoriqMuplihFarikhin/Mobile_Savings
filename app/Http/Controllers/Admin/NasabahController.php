<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;

class NasabahController extends Controller
{
    public function index()
    {
        return view('pages.admin.nasabah');
    }

    public function kolektor()
    {
        return view('pages.admin.kolektor');
    }

    public function bermasalah()
    {
        return view('pages.admin.bermasalah');
    }

    public function monitoringSetoran()
    {
        return view('pages.admin.monitoring-setoran');
    }
}

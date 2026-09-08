<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;

class MonitoringAbsensiController extends Controller
{
    public function index()
    {
        return view('pages.admin.monitoring-absensi');
    }
}

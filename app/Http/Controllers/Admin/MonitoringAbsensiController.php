<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\View\View;

class MonitoringAbsensiController extends Controller
{
    public function index(): View
    {
        return view('pages.admin.monitoring-absensi');
    }
}

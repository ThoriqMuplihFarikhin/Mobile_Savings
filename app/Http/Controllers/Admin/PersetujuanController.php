<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Support\AntreanPersetujuan;
use Illuminate\View\View;

class PersetujuanController extends Controller
{
    public function index(): View
    {
        return view('pages.admin.persetujuan', [
            'antrean' => AntreanPersetujuan::ringkas(),
        ]);
    }
}

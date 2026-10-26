<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\View\View;

class TinjauanPenarikanOfflineController extends Controller
{
    public function index(): View
    {
        return view('pages.admin.tinjauan-penarikan-offline');
    }
}

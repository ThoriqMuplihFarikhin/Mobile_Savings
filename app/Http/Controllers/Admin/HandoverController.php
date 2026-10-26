<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\View\View;

class HandoverController extends Controller
{
    public function index(): View
    {
        return view('pages.admin.handover');
    }
}

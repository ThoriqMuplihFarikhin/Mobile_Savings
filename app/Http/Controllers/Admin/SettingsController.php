<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\View\View;

class SettingsController extends Controller
{
    public function profile(): View
    {
        return view('pages.admin.settings.profile');
    }

    public function security(): View
    {
        return view('pages.admin.settings.security');
    }

    public function appearance(): View
    {
        return view('pages.admin.settings.appearance');
    }
}

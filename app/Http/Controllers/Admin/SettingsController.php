<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;

class SettingsController extends Controller
{
    public function profile()
    {
        return view('pages.admin.settings.profile');
    }

    public function security()
    {
        return view('pages.admin.settings.security');
    }

    public function appearance()
    {
        return view('pages.admin.settings.appearance');
    }
}

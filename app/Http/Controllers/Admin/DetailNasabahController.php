<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;

class DetailNasabahController extends Controller
{
    public function index(User $user)
    {
        return view('pages.admin.nasabah-detail', ['user' => $user]);
    }
}

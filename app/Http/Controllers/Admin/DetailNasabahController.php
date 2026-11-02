<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\View\View;

class DetailNasabahController extends Controller
{
    public function index(User $user): View
    {
        abort_unless($user->role === 'nasabah', 404);

        return view('pages.admin.nasabah', [
            'detailUserId' => $user->id,
        ]);
    }
}

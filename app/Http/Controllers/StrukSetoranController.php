<?php

namespace App\Http\Controllers;

use App\Models\KolektorNasabah;
use App\Models\TransaksiSetoran;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class StrukSetoranController extends Controller
{
    public function show(TransaksiSetoran $setoran): View
    {
        $user = Auth::user();

        abort_unless($user !== null, 403);

        $berwenang = $user->isAdmin()
            || $setoran->nasabah_id === $user->id
            || KolektorNasabah::where('kolektor_id', $user->id)
                ->where('nasabah_id', $setoran->nasabah_id)
                ->where('status', 'aktif')
                ->exists();

        abort_unless($berwenang, 403);

        $setoran->load(['nasabah', 'produk', 'inputBy']);

        return view('struk-setoran', ['setoran' => $setoran]);
    }
}

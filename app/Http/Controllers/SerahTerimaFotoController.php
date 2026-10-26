<?php

namespace App\Http\Controllers;

use App\Models\KepesertaanPaket;
use App\Models\KolektorNasabah;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

class SerahTerimaFotoController extends Controller
{
    public function show(KepesertaanPaket $kepesertaan): StreamedResponse
    {
        $user = Auth::user();

        abort_unless($user !== null, 403);

        $berwenang = $user->isAdmin()
            || $kepesertaan->nasabah_id === $user->id
            || KolektorNasabah::where('kolektor_id', $user->id)
                ->where('nasabah_id', $kepesertaan->nasabah_id)
                ->where('status', 'aktif')
                ->exists();

        abort_unless($berwenang, 403);

        $path = $kepesertaan->bukti_foto_url;

        if ($path === null || ! Storage::disk('local')->exists($path)) {
            if ($path !== null && Storage::disk('public')->exists($path)) {
                report(new \RuntimeException("Berkas serah terima masih berada di disk public: {$path}"));
            }

            abort(404);
        }

        return Storage::disk('local')->response($path);
    }
}

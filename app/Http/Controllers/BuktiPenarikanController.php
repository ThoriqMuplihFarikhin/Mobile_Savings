<?php

namespace App\Http\Controllers;

use App\Models\TransaksiPenarikan;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

/**
 * Penyaji bukti penarikan offline (D24): berkas berada pada disk privat
 * `local`, hanya admin dan kolektor pembuat penarikan yang boleh melihat.
 */
class BuktiPenarikanController extends Controller
{
    public function show(TransaksiPenarikan $penarikan): StreamedResponse
    {
        $user = Auth::user();

        abort_unless($user !== null, 403);

        $berwenang = $user->isAdmin()
            || $penarikan->dibayar_oleh === $user->id
            || $penarikan->diverifikasi_oleh === $user->id;

        abort_unless($berwenang, 403);

        $path = $penarikan->bukti_path;

        if ($path === null || ! Storage::disk('local')->exists($path)) {
            abort(404);
        }

        return Storage::disk('local')->response($path, null, [
            'Cache-Control' => 'private, no-store, max-age=0',
            'Pragma' => 'no-cache',
        ]);
    }
}

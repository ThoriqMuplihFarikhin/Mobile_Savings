<?php

namespace App\Http\Controllers;

use App\Models\AbsensiKolektor;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

class AbsensiFotoController extends Controller
{
    public function show(AbsensiKolektor $absensi, string $jenis): StreamedResponse
    {
        $user = Auth::user();

        abort_unless($user !== null && ($user->isAdmin() || $absensi->kolektor_id === $user->id), 403);

        $path = $jenis === 'selfie' ? $absensi->foto_selfie_path : $absensi->tanda_tangan_path;

        if ($path === null || ! Storage::disk('local')->exists($path)) {
            if ($path !== null && Storage::disk('public')->exists($path)) {
                report(new \RuntimeException("Berkas absensi masih berada di disk public: {$path}"));
            }

            abort(404);
        }

        return Storage::disk('local')->response($path);
    }
}

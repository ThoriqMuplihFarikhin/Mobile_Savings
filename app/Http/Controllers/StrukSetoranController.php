<?php

namespace App\Http\Controllers;

use App\Models\KolektorNasabah;
use App\Models\TransaksiSetoran;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class StrukSetoranController extends Controller
{
    public function show(Request $request, TransaksiSetoran $setoran): View|JsonResponse
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

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json([
                'id' => $setoran->id,
                'nomor' => 'SET-'.str_pad((string) $setoran->id, 6, '0', STR_PAD_LEFT),
                'tanggal' => Carbon::parse($setoran->tanggal_transaksi)->translatedFormat('d M Y'),
                'nasabah' => $setoran->nasabah?->name ?? '-',
                'produk' => $setoran->produk?->nama ?? '-',
                'kolektor' => $setoran->inputBy?->name ?? '-',
                'nominal' => (float) $setoran->nominal,
                'nominal_formatted' => 'Rp '.number_format((float) $setoran->nominal, 0, ',', '.'),
                'status' => $setoran->status,
                'status_label' => $setoran->status === 'dikoreksi' ? 'Dikoreksi' : 'Diterima',
                'nominal_asli' => $setoran->nominal_asli !== null ? (float) $setoran->nominal_asli : null,
                'nominal_asli_formatted' => $setoran->nominal_asli !== null
                    ? 'Rp '.number_format((float) $setoran->nominal_asli, 0, ',', '.')
                    : null,
                'url' => route('struk.setoran', $setoran),
            ]);
        }

        return view('struk-setoran', ['setoran' => $setoran]);
    }
}

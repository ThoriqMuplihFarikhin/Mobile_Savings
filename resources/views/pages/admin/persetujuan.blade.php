<x-layouts::admin title="Persetujuan">
    @php
        $kartu = [
            ['kode' => 'penarikan', 'label' => 'Penarikan Menunggu', 'ket' => 'Menunggu persetujuan 1/2 (D11)', 'url' => route('admin.penarikan.index')],
            ['kode' => 'verifikasi', 'label' => 'Verifikasi Nasabah Baru', 'ket' => 'Pendaftaran menunggu verifikasi', 'url' => route('admin.verifikasi.index')],
            ['kode' => 'setoran_kantor', 'label' => 'Setoran Kantor', 'ket' => 'Setoran kolektor ke kantor pending', 'url' => route('admin.rekonsiliasi.index')],
            ['kode' => 'komplain', 'label' => 'Komplain Baru', 'ket' => 'Komplain belum diproses', 'url' => route('admin.komplain.index')],
            ['kode' => 'izin', 'label' => 'Izin Kolektor', 'ket' => 'Pengajuan izin menunggu', 'url' => route('admin.monitoring-absensi.index')],
            ['kode' => 'bermasalah', 'label' => 'Nasabah Bermasalah', 'ket' => 'Kepesertaan perlu review', 'url' => route('admin.bermasalah.index')],
        ];
    @endphp

    <div class="mx-auto max-w-7xl space-y-6">
        <div class="mb-8">
            <h1 class="text-2xl font-semibold tracking-tight text-gray-900 dark:text-white">Persetujuan</h1>
            <p class="mt-1 text-sm text-gray-500 dark:text-slate-400">
                Antrean yang menunggu keputusan admin. Ketuk kartu untuk membuka halaman antreiannya.
            </p>
        </div>

        <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
            @foreach ($kartu as $k)
                <a href="{{ $k['url'] }}" wire:navigate data-test="kartu-antrean-{{ $k['kode'] }}"
                   class="flex items-start justify-between gap-3 rounded-2xl bg-white dark:bg-zinc-800 p-5 shadow-lg transition hover:ring-2 hover:ring-emerald-500/40">
                    <div class="min-w-0">
                        <p class="text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-slate-400">
                            {{ $k['label'] }}
                        </p>
                        <p class="mt-2 text-3xl font-bold text-gray-900 dark:text-white" data-test="hitung-{{ $k['kode'] }}">
                            {{ $antrean[$k['kode']] }}
                        </p>
                        <p class="mt-1 text-xs text-gray-500 dark:text-slate-400">{{ $k['ket'] }}</p>
                    </div>
                    <span class="shrink-0 rounded-full bg-emerald-50 dark:bg-emerald-950 px-2.5 py-1 text-xs font-semibold text-emerald-700 dark:text-emerald-300">
                        Buka
                    </span>
                </a>
            @endforeach
        </div>
    </div>
</x-layouts::admin>

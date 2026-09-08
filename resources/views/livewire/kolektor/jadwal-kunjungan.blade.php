<div>
    <div class="mb-8 flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <h1 class="text-2xl font-semibold tracking-tight text-[#171717]">Jadwal Kunjungan</h1>
            <p class="mt-1 text-sm text-[#888888]">{{ \Carbon\Carbon::parse($tanggal)->translatedFormat('l, d M Y') }}</p>
        </div>
        <input type="date" wire:model.live="tanggal"
            class="h-10 rounded-md border border-[#ebebeb] bg-white px-3 text-sm text-[#171717] focus:border-[#171717] focus:outline-none focus:ring-2 focus:ring-[#171717]/10" />
    </div>

    @if (session('success'))
        <div class="mb-4 flex items-center gap-2.5 rounded-lg bg-[#d3e5ff] px-4 py-3 text-sm text-[#0761d1]">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" /></svg>
            {{ session('success') }}
        </div>
    @endif

    <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
        @forelse($jadwalHari as $item)
            <div class="rounded-xl bg-white p-5 shadow-[0px_1px_1px_#00000005,0px_2px_2px_#0000000a,inset_0_0_0_1px_#ebebeb]">
                <div class="mb-3 flex items-center gap-3">
                    <div class="flex h-10 w-10 items-center justify-center rounded-full bg-[#d3e5ff] text-[#0070f3]">
                        <span class="text-sm font-bold">{{ substr($item['profil']->nama, 0, 1) }}</span>
                    </div>
                    <div>
                        <p class="text-sm font-medium text-[#171717]">{{ $item['profil']->nama }}</p>
                        <p class="text-xs text-[#888888]">{{ $item['profil']->user->no_hp }}</p>
                    </div>
                </div>
                <p class="mb-3 text-xs text-[#888888]">{{ $item['profil']->alamat }}</p>

                <div class="flex gap-2">
                    @if($item['status'] === 'dikunjungi')
                        <span class="inline-flex items-center gap-1 rounded-full bg-[#d3e5ff] px-3 py-1 font-mono text-xs text-[#0761d1]">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-3 w-3" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" /></svg>
                            Dikunjungi
                        </span>
                    @elseif($item['status'] === 'dilewati')
                        <span class="inline-flex items-center gap-1 rounded-full bg-[#ffefcf] px-3 py-1 font-mono text-xs text-[#ab570a]">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-3 w-3" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M13 5l7 7-7 7M5 5l7 7-7 7" /></svg>
                            Dilewati
                        </span>
                    @elseif($item['status'] === 'tidak_ada')
                        <span class="inline-flex items-center gap-1 rounded-full bg-[#fafafa] px-3 py-1 font-mono text-xs text-[#888888] shadow-[inset_0_0_0_1px_#ebebeb]">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-3 w-3" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636" /></svg>
                            Tidak Ada
                        </span>
                    @else
                        <button wire:click="updateStatus({{ $item['profil']->user_id }}, 'dikunjungi')" class="rounded-full bg-[#171717] px-3 py-1.5 text-xs font-medium text-white transition hover:opacity-90">Dikunjungi</button>
                        <button wire:click="updateStatus({{ $item['profil']->user_id }}, 'dilewati')" class="rounded-full border border-[#ebebeb] bg-white px-3 py-1.5 text-xs font-medium text-[#171717] transition hover:bg-[#fafafa]">Dilewati</button>
                        <button wire:click="updateStatus({{ $item['profil']->user_id }}, 'tidak_ada')" class="rounded-full border border-[#ebebeb] bg-white px-3 py-1.5 text-xs font-medium text-[#888888] transition hover:bg-[#fafafa]">Tidak Ada</button>
                    @endif
                </div>
            </div>
        @empty
            <div class="col-span-full flex flex-col items-center gap-3 py-16 text-[#888888]">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-12 w-12 text-[#ebebeb]" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" /></svg>
                <p class="text-sm">Tidak ada jadwal kunjungan untuk tanggal ini.</p>
            </div>
        @endforelse
    </div>
</div>
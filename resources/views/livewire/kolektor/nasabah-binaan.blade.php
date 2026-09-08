<div>
    <div class="mb-8">
        <h1 class="text-2xl font-semibold tracking-tight text-[#171717]">Nasabah Binaan</h1>
        <p class="mt-1 text-sm text-[#888888]">Daftar nasabah yang Anda tangani.</p>
    </div>

    <div class="mb-4">
        <div class="relative">
            <svg xmlns="http://www.w3.org/2000/svg" class="absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-[#888888]" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" /></svg>
            <input type="text" wire:model.live.debounce.300ms="search" placeholder="Cari nama atau no HP..."
                class="h-10 w-full rounded-md border border-[#ebebeb] bg-white py-0 pl-10 pr-4 text-sm text-[#171717] placeholder-[#a1a1a1] focus:border-[#171717] focus:outline-none focus:ring-2 focus:ring-[#171717]/10" />
        </div>
    </div>

    <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
        @forelse($nasabahList as $profil)
            <div class="rounded-xl bg-white p-5 shadow-[0px_1px_1px_#00000005,0px_2px_2px_#0000000a,inset_0_0_0_1px_#ebebeb]">
                <div class="mb-3 flex items-center gap-3">
                    <div class="flex h-12 w-12 items-center justify-center rounded-full bg-[#d3e5ff] text-[#0070f3]">
                        <span class="text-lg font-bold">{{ substr($profil->nama, 0, 1) }}</span>
                    </div>
                    <div>
                        <p class="text-sm font-semibold text-[#171717]">{{ $profil->nama }}</p>
                        <p class="text-sm text-[#888888]">{{ $profil->user->no_hp }}</p>
                    </div>
                </div>
                <div class="space-y-1 text-sm text-[#4d4d4d]">
                    <p>{{ $profil->alamat }}</p>
                    @if($profil->tanggal_lahir)
                        <p class="text-xs text-[#888888]">Lahir: {{ \Carbon\Carbon::parse($profil->tanggal_lahir)->translatedFormat('d M Y') }}</p>
                    @endif
                </div>
                <div class="mt-3">
                    <span class="inline-flex items-center rounded-full bg-[#d3e5ff] px-2.5 py-0.5 font-mono text-xs text-[#0761d1]">Aktif</span>
                </div>
            </div>
        @empty
            <div class="col-span-full flex flex-col items-center gap-3 py-16 text-[#888888]">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-12 w-12 text-[#ebebeb]" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z" /></svg>
                <p class="text-sm">Belum ada nasabah binaan.</p>
            </div>
        @endforelse
    </div>

    <div class="mt-4">{{ $nasabahList->links() }}</div>
</div>
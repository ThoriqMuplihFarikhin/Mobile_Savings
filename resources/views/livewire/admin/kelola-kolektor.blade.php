<div class="space-y-6">
    <x-admin.page-header
        breadcrumb="Operasional"
        title="Kelola Kolektor"
        subtitle="Manajemen data kolektor dan penugasan nasabah."
    >
        <x-slot:actions>
            <button type="button" wire:click="toggleForm"
                class="inline-flex h-10 items-center justify-center gap-1.5 rounded-xl bg-primary px-4 text-xs font-semibold text-on-primary shadow-xs transition hover:bg-primary-hover active:scale-[0.98]">
                <span>+</span>
                <span>Tambah Kolektor</span>
            </button>
        </x-slot:actions>
    </x-admin.page-header>

    @if (session('success'))
        <div class="flex items-center gap-2.5 rounded-xl border border-primary/20 bg-primary-soft p-3.5 text-xs font-medium text-on-primary-soft">
            <svg xmlns="http://www.w3.org/2000/svg" class="size-4 shrink-0 text-primary" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" /></svg>
            <span>{{ session('success') }}</span>
        </div>
    @endif
    @if (session('error'))
        <div class="flex items-center gap-2.5 rounded-xl border border-danger/20 bg-danger-soft p-3.5 text-xs font-medium text-danger">
            <svg xmlns="http://www.w3.org/2000/svg" class="size-4 shrink-0 text-danger" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" /></svg>
            <span>{{ session('error') }}</span>
        </div>
    @endif

    {{-- Kartu Utama --}}
    <div class="overflow-visible rounded-2xl border border-hairline/80 bg-white dark:bg-surface shadow-xs">
        {{-- Toolbar Pencarian --}}
        <div class="p-4 sm:p-5 pb-3">
            <div class="relative w-full max-w-sm">
                <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3.5 text-mute">
                    <svg class="size-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-5.197-5.197m0 0A7.5 7.5 0 105.196 5.196a7.5 7.5 0 0010.607 10.607z" />
                    </svg>
                </div>
                <input type="text" wire:model.live.debounce.300ms="search" placeholder="Cari nama atau no. HP..."
                    class="h-10 w-full rounded-xl border border-hairline bg-surface dark:bg-surface pl-10 pr-4 text-xs text-ink placeholder:text-mute focus:border-primary focus:outline-none focus:ring-1 focus:ring-primary" />
            </div>
        </div>

        {{-- Tabel Desktop --}}
        <div class="overflow-x-auto">
            <table class="hidden md:table w-full text-left">
                <thead class="border-y border-hairline/60 bg-transparent text-[11px] font-semibold uppercase tracking-wider text-mute">
                    <tr>
                        <th class="px-5 py-3.5">Nama</th>
                        <th class="px-5 py-3.5">No. HP</th>
                        <th class="px-5 py-3.5">Nasabah Aktif</th>
                        <th class="px-5 py-3.5">Status</th>
                        <th class="px-5 py-3.5">Penarikan Langsung</th>
                        <th class="px-5 py-3.5 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-hairline/50">
                    @forelse($kolektor as $item)
                        <tr class="transition hover:bg-inset/30">
                            <td class="px-5 py-4 text-xs font-medium text-ink">
                                {{ $item->name }}
                            </td>
                            <td class="px-5 py-4 text-xs text-body tabular-nums">
                                {{ $item->no_hp }}
                            </td>
                            <td class="px-5 py-4 text-xs text-ink tabular-nums">
                                {{ $item->nasabah_aktif_count }}
                            </td>
                            <td class="px-5 py-4">
                                @if($item->status_akun === 'aktif')
                                    <x-admin.status-chip status="Aktif" />
                                @elseif($item->status_akun === 'terkunci')
                                    <x-admin.status-chip status="Terkunci" />
                                @else
                                    <x-admin.status-chip status="Nonaktif" />
                                @endif
                            </td>
                            <td class="px-5 py-4">
                                <x-admin.status-chip :status="$item->izin_penarikan_langsung ? 'Diizinkan' : 'Diblokir'" />
                            </td>
                            <td class="px-5 py-4 text-right">
                                <div class="inline-flex items-center justify-end gap-2">
                                    <button type="button" wire:click="toggleAssign({{ $item->id }})"
                                        class="inline-flex items-center justify-center rounded-xl border border-hairline/80 bg-white dark:bg-surface px-3 py-1.5 text-xs font-medium text-ink shadow-2xs hover:bg-inset active:scale-[0.98] transition">
                                        Atur nasabah
                                    </button>
                                    <button type="button" wire:click="edit({{ $item->id }})"
                                        class="inline-flex items-center justify-center rounded-xl border border-hairline/80 bg-white dark:bg-surface px-3 py-1.5 text-xs font-medium text-ink shadow-2xs hover:bg-inset active:scale-[0.98] transition">
                                        Edit
                                    </button>
                                    <div x-data="{ open: false }" class="relative inline-block text-left">
                                        <button type="button" @click="open = !open" @click.outside="open = false"
                                            class="inline-flex size-8 items-center justify-center rounded-xl border border-hairline/80 bg-white dark:bg-surface text-mute hover:text-ink shadow-2xs hover:bg-inset transition"
                                            aria-label="Aksi lainnya">
                                            <svg class="size-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 6.75a1 1 0 110-2 1 1 0 010 2zm0 6a1 1 0 110-2 1 1 0 010 2zm0 6a1 1 0 110-2 1 1 0 010 2z" />
                                            </svg>
                                        </button>
                                        <div x-show="open" x-cloak
                                            class="absolute right-0 top-full mt-1.5 w-52 z-30 rounded-xl border border-hairline bg-white dark:bg-surface-2 p-1.5 shadow-xl">
                                            {{-- Toggle Status --}}
                                            <button type="button" wire:click="toggleStatus({{ $item->id }})" @click="open = false"
                                                @if($item->status_akun === 'aktif') wire:confirm="Yakin menonaktifkan kolektor ini? Akun dikunci dan kas yang belum disetor tetap dipantau di dashboard kas." @endif
                                                class="flex w-full items-center gap-2 rounded-lg px-3 py-2 text-xs font-medium text-ink hover:bg-inset transition text-left">
                                                {{ $item->status_akun === 'aktif' ? 'Nonaktifkan' : 'Aktifkan' }}
                                            </button>

                                            {{-- Toggle Izin Penarikan Langsung --}}
                                            <button type="button" wire:click="toggleIzinPenarikanLangsung({{ $item->id }})" @click="open = false"
                                                class="flex w-full items-center gap-2 rounded-lg px-3 py-2 text-xs font-medium text-ink hover:bg-inset transition text-left">
                                                {{ $item->izin_penarikan_langsung ? 'Blokir penarikan langsung' : 'Izinkan penarikan langsung' }}
                                            </button>

                                            {{-- Buka Kunci --}}
                                            @if($item->status_akun === 'terkunci' || ($item->login_terkunci_hingga && $item->login_terkunci_hingga->isFuture()))
                                                <button type="button" wire:click="bukaKunci({{ $item->id }})" @click="open = false"
                                                    class="flex w-full items-center gap-2 rounded-lg px-3 py-2 text-xs font-medium text-primary hover:bg-primary-soft transition text-left">
                                                    Buka kunci
                                                </button>
                                            @endif

                                            {{-- Reset PIN --}}
                                            <button type="button" wire:click="confirmResetPin({{ $item->id }})" @click="open = false"
                                                class="flex w-full items-center gap-2 rounded-lg px-3 py-2 text-xs font-medium text-danger hover:bg-danger-soft transition text-left">
                                                Reset PIN
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-5 py-12 text-center">
                                <div class="flex flex-col items-center gap-2">
                                    <svg class="size-10 text-mute/50" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                                    </svg>
                                    <p class="text-xs text-mute font-medium">Tidak ada data kolektor.</p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- Kartu Mobile --}}
        <div data-test="kartu-tabel" class="divide-y divide-hairline md:hidden">
            @forelse($kolektor as $item)
                <div class="p-4 space-y-3">
                    <div class="flex items-center justify-between gap-3">
                        <div class="min-w-0">
                            <p class="text-xs font-semibold text-ink">{{ $item->name }}</p>
                            <p class="text-[11px] text-mute tabular-nums">{{ $item->no_hp }}</p>
                        </div>
                        <x-admin.status-chip :status="$item->status_akun === 'aktif' ? 'Aktif' : ($item->status_akun === 'terkunci' ? 'Terkunci' : 'Nonaktif')" />
                    </div>
                    <div class="grid grid-cols-2 gap-2 rounded-xl bg-inset/40 p-3 text-xs">
                        <div>
                            <span class="block text-[11px] text-mute">Nasabah Aktif</span>
                            <span class="tabular-nums font-semibold text-ink">{{ $item->nasabah_aktif_count }} nasabah</span>
                        </div>
                        <div>
                            <span class="block text-[11px] text-mute">Penarikan Langsung</span>
                            <x-admin.status-chip :status="$item->izin_penarikan_langsung ? 'Diizinkan' : 'Diblokir'" class="mt-0.5" />
                        </div>
                    </div>
                    <div class="flex flex-wrap items-center gap-2 pt-1">
                        <button type="button" wire:click="toggleAssign({{ $item->id }})"
                            class="min-h-11 flex-1 inline-flex items-center justify-center rounded-xl border border-hairline bg-white dark:bg-surface px-3 py-2 text-xs font-semibold text-ink shadow-2xs hover:bg-inset transition active:scale-[0.98]">
                            Atur nasabah
                        </button>
                        <button type="button" wire:click="edit({{ $item->id }})"
                            class="min-h-11 flex-1 inline-flex items-center justify-center rounded-xl border border-hairline bg-white dark:bg-surface px-3 py-2 text-xs font-semibold text-ink shadow-2xs hover:bg-inset transition active:scale-[0.98]">
                            Edit
                        </button>
                        <div x-data="{ open: false }" class="relative">
                            <button type="button" @click="open = !open" @click.outside="open = false"
                                class="min-h-11 size-11 inline-flex items-center justify-center rounded-xl border border-hairline bg-white dark:bg-surface text-mute hover:text-ink shadow-2xs hover:bg-inset transition"
                                aria-label="Aksi lainnya">
                                <svg class="size-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 6.75a1 1 0 110-2 1 1 0 010 2zm0 6a1 1 0 110-2 1 1 0 010 2zm0 6a1 1 0 110-2 1 1 0 010 2z" />
                                </svg>
                            </button>
                            <div x-show="open" x-cloak
                                class="absolute right-0 bottom-full mb-1.5 w-56 z-30 rounded-xl border border-hairline bg-white dark:bg-surface-2 p-1.5 shadow-xl">
                                <button type="button" wire:click="toggleStatus({{ $item->id }})" @click="open = false"
                                    @if($item->status_akun === 'aktif') wire:confirm="Yakin menonaktifkan kolektor ini? Akun dikunci dan kas yang belum disetor tetap dipantau di dashboard kas." @endif
                                    class="flex w-full items-center gap-2 rounded-lg px-3 py-2 text-xs font-medium text-ink hover:bg-inset transition text-left">
                                    {{ $item->status_akun === 'aktif' ? 'Nonaktifkan' : 'Aktifkan' }}
                                </button>
                                <button type="button" wire:click="toggleIzinPenarikanLangsung({{ $item->id }})" @click="open = false"
                                    class="flex w-full items-center gap-2 rounded-lg px-3 py-2 text-xs font-medium text-ink hover:bg-inset transition text-left">
                                    {{ $item->izin_penarikan_langsung ? 'Blokir penarikan langsung' : 'Izinkan penarikan langsung' }}
                                </button>
                                @if($item->status_akun === 'terkunci' || ($item->login_terkunci_hingga && $item->login_terkunci_hingga->isFuture()))
                                    <button type="button" wire:click="bukaKunci({{ $item->id }})" @click="open = false"
                                        class="flex w-full items-center gap-2 rounded-lg px-3 py-2 text-xs font-medium text-primary hover:bg-primary-soft transition text-left">
                                        Buka kunci
                                    </button>
                                @endif
                                <button type="button" wire:click="confirmResetPin({{ $item->id }})" @click="open = false"
                                    class="flex w-full items-center gap-2 rounded-lg px-3 py-2 text-xs font-medium text-danger hover:bg-danger-soft transition text-left">
                                    Reset PIN
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            @empty
                <div class="p-8 text-center text-xs text-mute">
                    Tidak ada data kolektor.
                </div>
            @endforelse
        </div>

        {{-- Footer Kartu --}}
        <div class="flex items-center justify-between px-5 py-3.5 border-t border-hairline/60 text-xs text-mute">
            <span>Menampilkan {{ $kolektor->count() }} dari {{ $kolektor->total() }} data</span>
            @if($kolektor->hasPages())
                <div>{{ $kolektor->links() }}</div>
            @else
                <span>Halaman 1 dari 1</span>
            @endif
        </div>
    </div>

    {{-- Modal Tambah / Edit Kolektor --}}
    @if($showForm)
        <div class="fixed inset-0 z-50 flex items-center justify-center p-4 overflow-y-auto" role="dialog" aria-modal="true" aria-labelledby="modal-form-kolektor-title">
            <div wire:click="toggleForm" class="fixed inset-0 bg-black/40 transition-opacity" aria-hidden="true"></div>
            <div class="relative w-full max-w-lg rounded-2xl border border-hairline bg-white dark:bg-surface p-6 shadow-2xl transition-all">
                <div class="flex items-start justify-between border-b border-hairline/60 pb-3">
                    <div>
                        <h3 id="modal-form-kolektor-title" class="text-base font-semibold text-ink">{{ $editId ? 'Edit Kolektor' : 'Tambah Kolektor Baru' }}</h3>
                        <p class="mt-0.5 text-xs text-mute">Masukkan data akun petugas kolektor penanggung jawab.</p>
                    </div>
                    <button type="button" wire:click="toggleForm"
                        class="flex size-8 items-center justify-center rounded-xl text-mute transition hover:bg-inset hover:text-ink"
                        aria-label="Tutup modal">
                        <svg class="size-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>
                <form wire:submit="save" class="mt-4 space-y-4">
                    <div>
                        <label class="mb-1.5 block text-xs font-medium text-ink">Nama Lengkap</label>
                        <input type="text" wire:model="name"
                            class="h-10 w-full rounded-xl border border-hairline bg-surface px-3.5 text-xs text-ink placeholder:text-mute focus:border-primary focus:outline-none focus:ring-1 focus:ring-primary"
                            placeholder="Nama kolektor" />
                        @error('name') <p class="mt-1 text-xs text-danger">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="mb-1.5 block text-xs font-medium text-ink">No. HP</label>
                        <input type="text" wire:model="noHp"
                            class="h-10 w-full rounded-xl border border-hairline bg-surface px-3.5 text-xs text-ink placeholder:text-mute focus:border-primary focus:outline-none focus:ring-1 focus:ring-primary"
                            placeholder="08xxxxxxxxxx" />
                        @error('noHp') <p class="mt-1 text-xs text-danger">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="mb-1.5 block text-xs font-medium text-ink">
                            PIN awal (6 angka)
                            @if($editId)
                                <span class="text-mute font-normal">(Kosongkan bila tidak ingin diubah)</span>
                            @endif
                        </label>
                        <input type="password" wire:model="pin" maxlength="6"
                            class="h-10 w-full rounded-xl border border-hairline bg-surface px-3.5 text-xs text-ink placeholder:text-mute focus:border-primary focus:outline-none focus:ring-1 focus:ring-primary"
                            placeholder="123456" />
                        @error('pin') <p class="mt-1 text-xs text-danger">{{ $message }}</p> @enderror
                    </div>
                    <div class="flex items-center justify-end gap-2.5 border-t border-hairline/60 pt-4">
                        <button type="button" wire:click="toggleForm"
                            class="h-10 rounded-xl border border-hairline bg-white dark:bg-surface px-4 text-xs font-semibold text-ink shadow-2xs transition hover:bg-inset">
                            Batal
                        </button>
                        <button type="submit" wire:loading.attr="disabled"
                            class="inline-flex h-10 items-center justify-center rounded-xl bg-primary px-5 text-xs font-semibold text-on-primary shadow-xs transition hover:bg-primary-hover disabled:opacity-50">
                            {{ $editId ? 'Simpan Perubahan' : 'Tambah Kolektor' }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    @endif

    {{-- Modal / Drawer Atur Nasabah --}}
    @if($showAssign && $selectedKolektor)
        <div class="fixed inset-0 z-50 flex items-center justify-center p-4 overflow-y-auto" role="dialog" aria-modal="true" aria-labelledby="modal-assign-nasabah-title">
            <div wire:click="$set('showAssign', false)" class="fixed inset-0 bg-black/40 transition-opacity" aria-hidden="true"></div>
            <div class="relative w-full max-w-lg rounded-2xl border border-hairline bg-white dark:bg-surface p-6 shadow-2xl transition-all">
                <div class="flex items-start justify-between border-b border-hairline/60 pb-3">
                    <div>
                        <h3 id="modal-assign-nasabah-title" class="text-base font-semibold text-ink">Atur Nasabah: {{ $selectedKolektor->name }}</h3>
                        <p class="mt-0.5 text-xs text-mute">Daftar nasabah yang ditugaskan dan penambahan penugasan baru.</p>
                    </div>
                    <button type="button" wire:click="$set('showAssign', false)"
                        class="flex size-8 items-center justify-center rounded-xl text-mute transition hover:bg-inset hover:text-ink"
                        aria-label="Tutup modal">
                        <svg class="size-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>

                {{-- Seksi 1: Nasabah yang ditugaskan --}}
                <div class="mt-4">
                    <h4 class="text-xs font-semibold uppercase tracking-wider text-mute mb-2.5">Nasabah yang Ditugaskan</h4>
                    <div class="max-h-52 space-y-2 overflow-y-auto pr-1">
                        @forelse($selectedKolektor->kolektorNasabahs()->where('status', 'aktif')->with('nasabah')->get() as $assign)
                            <div class="flex items-center justify-between rounded-xl border border-hairline/60 bg-inset/40 px-3.5 py-2.5">
                                <div class="min-w-0">
                                    <b class="block text-xs font-semibold text-ink truncate">{{ $assign->nasabah->name ?? '-' }}</b>
                                    @if(isset($assign->nasabah->no_hp))
                                        <span class="block text-[11px] text-mute">{{ $assign->nasabah->no_hp }}</span>
                                    @endif
                                </div>
                                <button type="button" wire:click="removeAssign({{ $assign->id }})"
                                    class="rounded-lg border border-danger/30 bg-white dark:bg-surface px-2.5 py-1 text-xs font-medium text-danger hover:bg-danger-soft transition">
                                    Hapus
                                </button>
                            </div>
                        @empty
                            <div class="rounded-xl border border-dashed border-hairline p-4 text-center">
                                <p class="text-xs text-mute">Belum ada nasabah yang ditugaskan.</p>
                            </div>
                        @endforelse
                    </div>
                </div>

                {{-- Seksi 2: Tambah penugasan --}}
                <div class="mt-5 border-t border-hairline/60 pt-4">
                    <h4 class="text-xs font-semibold uppercase tracking-wider text-mute mb-2">Tambah Penugasan</h4>
                    <div class="space-y-3">
                        <div>
                            <select wire:model.live="assignNasabahId"
                                class="h-10 w-full rounded-xl border border-hairline bg-surface px-3.5 text-xs text-ink focus:border-primary focus:outline-none focus:ring-1 focus:ring-primary">
                                <option value="">Pilih nasabah aktif...</option>
                                @foreach($availableNasabah as $nasabah)
                                    <option value="{{ $nasabah->user_id }}">{{ $nasabah->nama }} ({{ $nasabah->user->no_hp ?? '—' }})</option>
                                @endforeach
                            </select>
                            @error('assignNasabahId') <p class="mt-1 text-xs text-danger">{{ $message }}</p> @enderror
                        </div>
                        <div class="flex justify-end">
                            <button type="button" wire:click="assignNasabah" {{ empty($assignNasabahId) ? 'disabled' : '' }}
                                class="inline-flex h-9 items-center justify-center rounded-xl bg-primary px-4 text-xs font-semibold text-on-primary shadow-xs transition hover:bg-primary-hover disabled:opacity-50">
                                Tugaskan
                            </button>
                        </div>
                    </div>
                </div>

                <div class="mt-6 flex justify-end border-t border-hairline/60 pt-4">
                    <button type="button" wire:click="$set('showAssign', false)"
                        class="h-10 rounded-xl border border-hairline bg-white dark:bg-surface px-4 text-xs font-semibold text-ink shadow-2xs transition hover:bg-inset">
                        Tutup
                    </button>
                </div>
            </div>
        </div>
    @endif

    {{-- Modal Konfirmasi Reset PIN --}}
    @if($tampilKonfirmasiResetPin)
        <div class="fixed inset-0 z-50 flex items-center justify-center p-4 overflow-y-auto" role="dialog" aria-modal="true" aria-labelledby="modal-reset-pin-kolektor-title">
            <div wire:click="$set('tampilKonfirmasiResetPin', false)" class="fixed inset-0 bg-black/40 transition-opacity" aria-hidden="true"></div>
            <div class="relative w-full max-w-md rounded-2xl border border-hairline bg-white dark:bg-surface p-6 shadow-2xl transition-all">
                <div class="flex items-start justify-between">
                    <div class="flex items-center gap-3">
                        <div class="flex size-10 shrink-0 items-center justify-center rounded-full bg-danger-soft text-danger">
                            <svg class="size-5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126zM12 15.75h.007v.008H12v-.008z" />
                            </svg>
                        </div>
                        <div>
                            <h3 id="modal-reset-pin-kolektor-title" class="text-sm font-semibold text-ink">Reset PIN?</h3>
                            <p class="mt-0.5 text-xs text-mute">Tindakan ini memerlukan konfirmasi.</p>
                        </div>
                    </div>
                    <button type="button" wire:click="$set('tampilKonfirmasiResetPin', false)"
                        class="flex size-8 items-center justify-center rounded-xl text-mute transition hover:bg-inset hover:text-ink"
                        aria-label="Tutup modal">
                        <svg class="size-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>
                <p class="mt-3 text-xs leading-relaxed text-subtle">PIN pengguna akan diganti menjadi PIN acak baru dan seluruh sesi aktifnya dihentikan. Pengguna wajib mengganti PIN setelah login berikutnya.</p>
                @error('reset_pin') <p class="mt-2 text-xs text-danger">{{ $message }}</p> @enderror
                <div class="mt-6 flex justify-end gap-2.5">
                    <button type="button" wire:click="$set('tampilKonfirmasiResetPin', false)" class="h-10 rounded-xl border border-hairline bg-white dark:bg-surface px-4 text-xs font-semibold text-ink transition hover:bg-inset">Batal</button>
                    <button type="button" wire:click="resetPin" class="h-10 rounded-xl bg-danger px-4 text-xs font-semibold text-white transition hover:opacity-90">Reset PIN</button>
                </div>
            </div>
        </div>
    @endif
</div>

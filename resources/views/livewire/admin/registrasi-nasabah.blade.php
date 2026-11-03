<div class="space-y-5">
    {{-- Header Halaman --}}
    <x-admin.page-header
        title="Registrasi Nasabah"
        subtitle="Daftarkan nasabah baru langsung oleh admin."
        breadcrumb="Nasabah"
    />

    {{-- Banner Notifikasi Flash --}}
    @if (session('success'))
        <div class="flex items-center gap-2.5 rounded-[10px] bg-primary-soft px-4 py-3 text-xs font-medium text-on-primary-soft border border-primary/20">
            <svg class="size-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
            </svg>
            <span>{{ session('success') }}</span>
        </div>
    @endif

    {{-- 2-Kolom: Form Registrasi (Kiri) & Terdaftar Terbaru (Kanan) --}}
    <div class="grid grid-cols-1 lg:grid-cols-[1.5fr_1fr] gap-5 items-start">
        {{-- Kolom Kiri: Form Registrasi Nasabah --}}
        <div class="rounded-[14px] border border-hairline bg-surface p-5 shadow-xs">
            <div class="border-b border-hairline pb-3 mb-4">
                <h2 class="text-base font-semibold text-ink">Form Registrasi Nasabah</h2>
                <p class="text-xs text-mute mt-0.5">Isi data calon nasabah untuk membuka rekening tabungan.</p>
            </div>

            <form wire:submit="submit" class="space-y-4">
                <div class="grid gap-4 sm:grid-cols-2">
                    {{-- Nama Lengkap --}}
                    <div class="{{ $modeOffline ? 'sm:col-span-2' : '' }}">
                        <label class="mb-1.5 block text-xs font-medium text-ink">Nama Lengkap</label>
                        <input
                            type="text"
                            wire:model="nama"
                            placeholder="Nama nasabah"
                            class="h-[38px] w-full rounded-[10px] border border-hairline bg-surface px-3 text-sm text-ink placeholder-mute focus:border-ink focus:outline-none focus:ring-1 focus:ring-accent"
                        />
                        @error('nama') <p class="mt-1 text-xs text-danger">{{ $message }}</p> @enderror
                    </div>

                    {{-- No. HP (sembunyi jika mode offline) --}}
                    @if(! $modeOffline)
                        <div>
                            <label class="mb-1.5 block text-xs font-medium text-ink">No. HP</label>
                            <input
                                type="text"
                                wire:model="noHp"
                                placeholder="08xxxxxxxxxx"
                                class="h-[38px] w-full rounded-[10px] border border-hairline bg-surface px-3 text-sm text-ink placeholder-mute focus:border-ink focus:outline-none focus:ring-1 focus:ring-accent"
                            />
                            @error('noHp') <p class="mt-1 text-xs text-danger">{{ $message }}</p> @enderror
                        </div>
                    @endif

                    {{-- Alamat --}}
                    <div class="sm:col-span-2">
                        <label class="mb-1.5 block text-xs font-medium text-ink">Alamat</label>
                        <textarea
                            wire:model="alamat"
                            rows="2"
                            placeholder="Alamat lengkap nasabah"
                            class="w-full rounded-[10px] border border-hairline bg-surface px-3 py-2 text-sm text-ink placeholder-mute focus:border-ink focus:outline-none focus:ring-1 focus:ring-accent"
                        ></textarea>
                        @error('alamat') <p class="mt-1 text-xs text-danger">{{ $message }}</p> @enderror
                    </div>

                    {{-- Tanggal Lahir --}}
                    <div>
                        <label for="tanggalLahir" class="mb-1.5 block text-xs font-medium text-ink">Tanggal Lahir</label>
                        <x-ui.tanggal
                            id="tanggalLahir"
                            wire:model="tanggalLahir"
                            :max="now()->toDateString()"
                            placeholder="Pilih tanggal lahir"
                            class="h-[38px] w-full rounded-[10px] border border-hairline bg-surface px-3 text-sm text-ink focus:border-ink focus:outline-none focus:ring-1 focus:ring-accent cursor-pointer"
                        />
                        @error('tanggalLahir') <p class="mt-1 text-xs text-danger">{{ $message }}</p> @enderror
                    </div>

                    {{-- Jenis Kelamin --}}
                    <div>
                        <label class="mb-1.5 block text-xs font-medium text-ink">Jenis Kelamin</label>
                        <select
                            wire:model="jenisKelamin"
                            class="h-[38px] w-full rounded-[10px] border border-hairline bg-surface px-3 text-sm text-ink focus:border-ink focus:outline-none focus:ring-1 focus:ring-accent"
                        >
                            <option value="laki-laki">Laki-laki</option>
                            <option value="perempuan">Perempuan</option>
                        </select>
                    </div>

                    {{-- Pekerjaan --}}
                    <div class="sm:col-span-2">
                        <label class="mb-1.5 block text-xs font-medium text-ink">Pekerjaan</label>
                        <input
                            type="text"
                            wire:model="pekerjaan"
                            placeholder="Contoh: Pedagang, Wiraswasta, Karyawan"
                            class="h-[38px] w-full rounded-[10px] border border-hairline bg-surface px-3 text-sm text-ink placeholder-mute focus:border-ink focus:outline-none focus:ring-1 focus:ring-accent"
                        />
                        @error('pekerjaan') <p class="mt-1 text-xs text-danger">{{ $message }}</p> @enderror
                    </div>

                    {{-- Mode Offline Checkbox --}}
                    <div class="sm:col-span-2 pt-1">
                        <label class="flex items-start gap-2.5 text-xs text-ink cursor-pointer select-none">
                            <input
                                type="checkbox"
                                wire:model.live="modeOffline"
                                class="size-4 mt-0.5 rounded border-hairline text-accent focus:ring-accent cursor-pointer"
                            />
                            <span class="leading-relaxed text-mute">
                                <strong class="text-ink font-medium">Nasabah tanpa aplikasi (Mode Offline).</strong> Kolektor yang mencatat setoran dan penarikan langsung melalui buku tabungan fisik.
                            </span>
                        </label>
                    </div>
                </div>

                {{-- Aksi Formulir --}}
                <div class="flex items-center justify-end pt-3 border-t border-hairline">
                    <button
                        type="submit"
                        wire:loading.attr="disabled"
                        class="min-h-11 h-[38px] inline-flex items-center justify-center rounded-[10px] bg-primary px-5 text-xs font-semibold text-white transition hover:bg-primary-hover active:scale-95 disabled:opacity-50 cursor-pointer shadow-xs"
                    >
                        Daftarkan
                    </button>
                </div>
            </form>
        </div>

        {{-- Kolom Kanan: Terdaftar Terbaru --}}
        <div class="rounded-[14px] border border-hairline bg-surface shadow-xs overflow-hidden">
            <div class="p-4 border-b border-hairline">
                <h2 class="text-base font-semibold text-ink">Terdaftar Terbaru</h2>
                <p class="text-xs text-mute mt-0.5">Daftar nasabah yang baru saja didaftarkan.</p>
            </div>

            @if($nasabah->isNotEmpty())
                <div class="divide-y divide-hairline">
                    @foreach($nasabah as $item)
                        <div class="flex items-center gap-3 px-4 py-3 hover:bg-surface-2 transition">
                            <span class="flex size-9 shrink-0 items-center justify-center rounded-full bg-primary-soft text-on-primary-soft text-xs font-semibold select-none">
                                {{ $item->initials() }}
                            </span>
                            <div class="min-w-0 flex-1">
                                <a
                                    href="{{ route('admin.nasabah.index', ['detail' => $item->id]) }}"
                                    wire:navigate
                                    class="truncate text-sm font-medium text-ink hover:text-primary transition-colors block"
                                    title="Lihat detail nasabah"
                                >
                                    {{ $item->name }}
                                </a>
                                <span class="block text-xs text-mute truncate mt-0.5">
                                    <span class="tabular-nums">{{ $item->no_hp ?? 'Offline' }}</span>
                                    @if($item->nasabahProfil?->alamat)
                                        &middot; {{ $item->nasabahProfil->alamat }}
                                    @endif
                                </span>
                            </div>
                            <div class="shrink-0">
                                <x-admin.status-chip :status="$item->status_akun === 'aktif' ? 'Aktif' : ($item->status_akun === 'terkunci' ? 'Terkunci' : 'Pending')" />
                            </div>
                        </div>
                    @endforeach
                </div>
                @if($nasabah->hasPages())
                    <div class="p-3 border-t border-hairline bg-surface-2">
                        {{ $nasabah->links() }}
                    </div>
                @endif
            @else
                <div class="p-4">
                    <x-admin.empty-state
                        title="Belum ada nasabah"
                        description="Nasabah yang didaftarkan akan muncul di sini."
                    />
                </div>
            @endif
        </div>
    </div>
</div>
<div class="mx-auto max-w-2xl">
    {{-- Page Header --}}
    <div class="mb-8">
        <h1 class="text-2xl font-semibold tracking-tight text-[#171717]">Profil Saya</h1>
        <p class="mt-1 text-sm text-[#888888]">Kelola informasi profil Anda.</p>
    </div>

    {{-- Flash --}}
    @if (session('success'))
        <div class="mb-4 flex items-center gap-2.5 rounded-lg bg-[#d3e5ff] px-4 py-3 text-sm text-[#0761d1]">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" /></svg>
            {{ session('success') }}
        </div>
    @endif

    {{-- Form Card --}}
    <div class="rounded-xl bg-[#fafafa] shadow-[inset_0_0_0_1px_#ebebeb]">
        <div class="border-b border-[#ebebeb] px-6 py-4">
            <p class="text-sm font-semibold text-[#171717]">Informasi Pribadi</p>
            <p class="mt-0.5 text-sm text-[#888888]">Perubahan akan disimpan ke sistem setelah Anda klik Simpan.</p>
        </div>

        <form wire:submit="update" class="p-6">
            <div class="space-y-5">
                {{-- Nama & No HP --}}
                <div class="grid gap-4 sm:grid-cols-2">
                    <div>
                        <label class="mb-1.5 block text-sm font-medium text-[#171717]">Nama Lengkap</label>
                        <input type="text" wire:model="nama"
                            class="h-10 w-full rounded-md border border-[#ebebeb] bg-white px-3 text-sm text-[#171717] focus:border-[#171717] focus:outline-none focus:ring-2 focus:ring-[#171717]/10" />
                        @error('nama') <p class="mt-1.5 text-xs text-[#ee0000]">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="mb-1.5 block text-sm font-medium text-[#171717]">No. HP</label>
                        <input type="text" wire:model="noHp"
                            class="h-10 w-full rounded-md border border-[#ebebeb] bg-white px-3 text-sm text-[#171717] focus:border-[#171717] focus:outline-none focus:ring-2 focus:ring-[#171717]/10" />
                        @error('noHp') <p class="mt-1.5 text-xs text-[#ee0000]">{{ $message }}</p> @enderror
                    </div>
                </div>

                {{-- Alamat --}}
                <div>
                    <label class="mb-1.5 block text-sm font-medium text-[#171717]">Alamat</label>
                    <textarea wire:model="alamat" rows="2"
                        class="w-full rounded-md border border-[#ebebeb] bg-white px-3 py-2.5 text-sm text-[#171717] focus:border-[#171717] focus:outline-none focus:ring-2 focus:ring-[#171717]/10"></textarea>
                    @error('alamat') <p class="mt-1.5 text-xs text-[#ee0000]">{{ $message }}</p> @enderror
                </div>

                {{-- Tanggal Lahir, Jenis Kelamin, Pekerjaan --}}
                <div class="grid gap-4 sm:grid-cols-3">
                    <div>
                        <label class="mb-1.5 block text-sm font-medium text-[#171717]">Tanggal Lahir</label>
                        <input type="date" wire:model="tanggalLahir"
                            class="h-10 w-full rounded-md border border-[#ebebeb] bg-white px-3 text-sm text-[#171717] focus:border-[#171717] focus:outline-none focus:ring-2 focus:ring-[#171717]/10" />
                        @error('tanggalLahir') <p class="mt-1.5 text-xs text-[#ee0000]">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="mb-1.5 block text-sm font-medium text-[#171717]">Jenis Kelamin</label>
                        <select wire:model="jenisKelamin"
                            class="h-10 w-full rounded-md border border-[#ebebeb] bg-white px-3 text-sm text-[#171717] focus:border-[#171717] focus:outline-none focus:ring-2 focus:ring-[#171717]/10">
                            <option value="laki-laki">Laki-laki</option>
                            <option value="perempuan">Perempuan</option>
                        </select>
                    </div>
                    <div>
                        <label class="mb-1.5 block text-sm font-medium text-[#171717]">Pekerjaan</label>
                        <input type="text" wire:model="pekerjaan"
                            class="h-10 w-full rounded-md border border-[#ebebeb] bg-white px-3 text-sm text-[#171717] focus:border-[#171717] focus:outline-none focus:ring-2 focus:ring-[#171717]/10" />
                    </div>
                </div>
            </div>

            {{-- Footer Actions --}}
            <div class="mt-6 flex items-center justify-between border-t border-[#ebebeb] pt-5">
                <p class="text-xs text-[#888888]">Pastikan data Anda akurat sebelum menyimpan.</p>
                <button type="submit"
                    class="rounded-full bg-[#171717] px-5 py-2 text-sm font-medium text-white transition hover:opacity-90">
                    Simpan Perubahan
                </button>
            </div>
        </form>
    </div>
</div>

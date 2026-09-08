<div class="mx-auto max-w-3xl">
    <flux:heading size="xl">Pengaturan Sistem</flux:heading>
    <flux:subheading class="mb-6">Konfigurasi umum aplikasi yang hanya bisa diubah oleh admin.</flux:subheading>

    @if (session('status'))
        <flux:callout variant="success" class="mb-6" icon="check-circle">
            {{ session('status') }}
        </flux:callout>
    @endif

    <form wire:submit="simpanKonfigurasi" class="space-y-6">
        <flux:card class="space-y-4">
            <flux:heading size="lg">Konfigurasi Umum</flux:heading>

            <flux:input
                wire:model="namaProdukDefault"
                label="Nama Koperasi / Instansi"
                placeholder="Contoh: Koperasi Sejahtera Bersama"
            />

            <flux:input
                type="number"
                wire:model="batasToleransiHari"
                label="Batas Toleransi Keterlambatan Setoran (hari)"
                placeholder="3"
            />

            <flux:input
                wire:model="nomorWaBantuan"
                label="Nomor WhatsApp Bantuan"
                placeholder="6281234567890"
            />

            <div>
                <flux:button type="submit" variant="primary">Simpan Konfigurasi</flux:button>
            </div>
        </flux:card>
    </form>

    <flux:card class="mt-6 space-y-4">
        <flux:heading size="lg">Akun Admin</flux:heading>
        <flux:subheading>Daftar pengguna dengan peran admin di sistem ini.</flux:subheading>

        <div class="rounded-2xl shadow-[inset_0_0_0_1px_#ebebeb] dark:shadow-[inset_0_0_0_1px_#3f3f46]">
            @foreach ($daftarAdmin as $admin)
                <div class="flex items-center justify-between px-5 py-3 border-b border-[#ebebeb] dark:border-zinc-600 last:border-b-0">
                    <div>
                        <p class="text-sm font-medium text-[#171717] dark:text-white">{{ $admin->name }}</p>
                        <p class="text-xs text-[#888888] dark:text-zinc-400">{{ $admin->no_hp }}</p>
                    </div>
                    @if ($admin->id === auth()->id())
                        <flux:badge size="sm">Anda</flux:badge>
                    @endif
                </div>
            @endforeach
        </div>
    </flux:card>

    <flux:card class="mt-6 space-y-4">
        <flux:heading size="lg">Akun Saya</flux:heading>
        <div class="flex flex-wrap gap-3">
            <flux:button :href="route('profile.edit')" wire:navigate variant="ghost">Edit Profil</flux:button>
            <flux:button :href="route('security.edit')" wire:navigate variant="ghost">Ganti PIN</flux:button>
            <flux:button wire:click="logout" variant="danger">Keluar</flux:button>
        </div>
    </flux:card>
</div>

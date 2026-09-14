<div class="mx-auto max-w-2xl">
    <flux:button :href="route('admin.pengaturan.index')" wire:navigate variant="ghost" icon="arrow-left" class="mb-4">
        Kembali ke Pengaturan Sistem
    </flux:button>

    <flux:heading size="xl">Edit Profil</flux:heading>
    <flux:subheading class="mb-6">Perbarui nama dan foto profil akun admin Anda.</flux:subheading>

    <flux:card class="space-y-5">
        <div class="flex items-center gap-4">
            @if (auth()->user()->foto_profil_path)
                <img src="{{ asset('storage/'.auth()->user()->foto_profil_path) }}" alt="Foto Profil"
                     class="h-16 w-16 rounded-full object-cover">
            @else
                <div class="flex h-16 w-16 items-center justify-center rounded-full bg-zinc-800 text-lg font-semibold text-white dark:bg-zinc-700">
                    {{ auth()->user()->initials() }}
                </div>
            @endif

            <flux:button size="sm" variant="ghost" as="label">
                Ganti Foto
                <input type="file" wire:model="fotoBaru" accept="image/*" class="hidden">
            </flux:button>
        </div>

        <form wire:submit="updateProfileInformation" class="space-y-4">
            <flux:input wire:model="name" label="Nama" required />

            <flux:input :value="$no_hp" label="No. HP" disabled readonly />
            <flux:text size="sm" class="text-zinc-500">No. HP tidak dapat diubah karena digunakan untuk login.</flux:text>

            <flux:button type="submit" variant="primary">Simpan Perubahan</flux:button>
        </form>
    </flux:card>
</div>

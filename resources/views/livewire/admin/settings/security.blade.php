<div class="mx-auto max-w-2xl">
    <flux:button :href="route('admin.pengaturan.index')" wire:navigate variant="ghost" icon="arrow-left" class="mb-4">
        Kembali ke Pengaturan Sistem
    </flux:button>

    <flux:heading size="xl">Ganti PIN</flux:heading>
    <flux:subheading class="mb-6">Pastikan PIN Anda aman dan mudah diingat.</flux:subheading>

    <flux:card>
        <form wire:submit="updatePin" class="space-y-4">
            <flux:input
                type="password"
                wire:model="current_pin"
                label="PIN Saat Ini"
                maxlength="6"
                autocomplete="off"
            />

            <flux:input
                type="password"
                wire:model="pin"
                label="PIN Baru"
                maxlength="6"
                autocomplete="off"
            />

            <flux:input
                type="password"
                wire:model="pin_confirmation"
                label="Konfirmasi PIN Baru"
                maxlength="6"
                autocomplete="off"
            />

            <flux:button type="submit" variant="primary">Update PIN</flux:button>
        </form>
    </flux:card>
</div>

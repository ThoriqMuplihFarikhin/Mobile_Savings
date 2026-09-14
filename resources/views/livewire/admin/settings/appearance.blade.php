<div class="mx-auto max-w-2xl">
    <flux:button :href="route('admin.pengaturan.index')" wire:navigate variant="ghost" icon="arrow-left" class="mb-4">
        Kembali ke Pengaturan Sistem
    </flux:button>

    <flux:heading size="xl">Tampilan</flux:heading>
    <flux:subheading class="mb-6">Atur tema tampilan untuk akun admin Anda.</flux:subheading>

    <flux:card>
        <flux:radio.group x-data variant="segmented" x-model="$flux.appearance">
            <flux:radio value="light" icon="sun">Terang</flux:radio>
            <flux:radio value="dark" icon="moon">Gelap</flux:radio>
            <flux:radio value="system" icon="computer-desktop">Sistem</flux:radio>
        </flux:radio.group>
    </flux:card>
</div>

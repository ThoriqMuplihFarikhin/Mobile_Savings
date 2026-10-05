@if(auth()->user()->isAdmin())
<x-layouts::admin title="Serah Terima Paket">
    <livewire:admin.serah-terima-paket />
</x-layouts::admin>

@else
<x-layouts::mobile title="Serah Terima Paket">
    <livewire:admin.serah-terima-paket />
</x-layouts::mobile>
@endif

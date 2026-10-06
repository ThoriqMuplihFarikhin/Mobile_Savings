<?php

namespace App\Livewire\Admin\Settings;

use Flux\Flux;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;
use Livewire\WithFileUploads;

#[Layout('layouts.app')]
class Profile extends Component
{
    use WithFileUploads;

    public string $name = '';

    public string $no_hp = '';

    /** @var TemporaryUploadedFile|null */
    public $fotoBaru;

    public function mount(): void
    {
        $user = Auth::user();
        $this->name = $user->name;
        $this->no_hp = $user->no_hp ?? '';
    }

    public function updatedFotoBaru(): void
    {
        $this->validate(['fotoBaru' => 'image|max:2048']);

        $foto = $this->fotoBaru;

        if ($foto === null) {
            return;
        }

        $path = $foto->store('profil', 'public');
        Auth::user()->update(['foto_profil_path' => $path]);

        Flux::toast(variant: 'success', text: 'Foto profil berhasil diubah!');
    }

    public function updateProfileInformation(): void
    {
        $validated = $this->validate([
            'name' => ['required', 'string', 'max:255'],
        ]);

        Auth::user()->update(['name' => $validated['name']]);

        Flux::toast(variant: 'success', text: 'Profil berhasil diperbarui.');
    }

    public function render(): View
    {
        return view('livewire.admin.settings.profile');
    }
}

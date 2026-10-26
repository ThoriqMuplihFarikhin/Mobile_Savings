<?php

use App\Concerns\ProfileValidationRules;
use Flux\Flux;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithFileUploads;

new #[Title('Edit profil')] class extends Component {
    use ProfileValidationRules;
    use WithFileUploads;

    public string $name = '';
    public string $no_hp = '';
    public string $alamat = '';
    public string $tanggalLahir = '';
    public string $jenisKelamin = 'laki-laki';
    public string $pekerjaan = '';
    public $fotoBaru;
    public $bannerBaru;

    public function mount(): void
    {
        $user = Auth::user();
        $this->name = $user->name;
        $this->no_hp = $user->no_hp ?? '';

        $profil = $user->nasabahProfil;
        if ($profil) {
            $this->alamat = $profil->alamat ?? '';
            $this->tanggalLahir = $profil->tanggal_lahir ? $profil->tanggal_lahir->format('Y-m-d') : '';
            $this->jenisKelamin = $profil->jenis_kelamin ?? 'laki-laki';
            $this->pekerjaan = $profil->pekerjaan ?? '';
        }
    }

    public function updatedFotoBaru(): void
    {
        $this->validate(['fotoBaru' => 'image|max:2048']);
        $path = $this->fotoBaru->store('profil', 'public');
        Auth::user()->update(['foto_profil_path' => $path]);
        Flux::toast(variant: 'success', text: 'Foto profil berhasil diubah!');
    }

    public function updatedBannerBaru(): void
    {
        $this->validate(['bannerBaru' => 'image|max:3072']);
        $path = $this->bannerBaru->store('banner', 'public');
        Auth::user()->update(['banner_path' => $path]);
        Flux::toast(variant: 'success', text: 'Banner berhasil diubah!');
    }

    public function updateProfileInformation(): void
    {
        $user = Auth::user();

        $validated = $this->validate([
            'name' => ['required', 'string', 'max:255'],
            'alamat' => ['required', 'string', 'min:5'],
            'tanggalLahir' => ['required', 'date', 'before:today'],
            'jenisKelamin' => ['required', 'in:laki-laki,perempuan'],
            'pekerjaan' => ['nullable', 'string'],
        ]);

        $user->update(['name' => $validated['name']]);

        \App\Models\NasabahProfil::updateOrCreate(
            ['user_id' => $user->id],
            [
                'nama' => $validated['name'],
                'alamat' => $validated['alamat'],
                'tanggal_lahir' => $validated['tanggalLahir'],
                'jenis_kelamin' => $validated['jenisKelamin'],
                'pekerjaan' => $validated['pekerjaan'] ?? null,
            ]
        );

        Flux::toast(variant: 'success', text: 'Perubahan profil berhasil disimpan!');
    }

    public function render()
    {
        return view('pages.settings.⚡profile')
            ->layout('layouts.mobile');
    }
}; ?>

<section class="w-full">
    @php
        $user = Auth::user();
        $backRoute = match($user->role) {
            'nasabah' => route('nasabah.pengaturan.index'),
            'kolektor' => route('kolektor.pengaturan.index'),
            default => route('dashboard'),
        };
        $labelSejak = $user->role === 'nasabah' ? 'Nasabah sejak' : 'Bergabung sejak';
    @endphp

    {{-- Banner --}}
    <div class="relative -mx-4 -mt-4 h-[140px] overflow-hidden rounded-b-xl bg-primary-deep sm:mx-0 sm:rounded-t-xl sm:rounded-b-none">
        @if($user->banner_path)
            <img src="{{ asset('storage/'.$user->banner_path) }}" alt="Banner" class="h-full w-full object-cover">
        @endif

        {{-- Back button --}}
        <a href="{{ $backRoute }}" wire:navigate aria-label="Kembali"
           class="absolute left-3 top-3 flex h-11 w-11 items-center justify-center rounded-full bg-black/70 text-white transition hover:bg-black/90">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7" /></svg>
        </a>

        {{-- Ganti Banner --}}
        <label class="absolute right-3 top-3 flex h-11 cursor-pointer items-center gap-1.5 rounded-full bg-black/70 px-4 text-xs font-medium text-white transition hover:bg-black/90">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z" /><path stroke-linecap="round" stroke-linejoin="round" d="M15 13a3 3 0 11-6 0 3 3 0 016 0z" /></svg>
            Ganti Banner
            <input type="file" wire:model="bannerBaru" accept="image/*" class="hidden">
        </label>
    </div>

    {{-- Avatar + Info --}}
    <div class="relative z-10 -mt-12 flex flex-col items-center">
        <div class="relative">
            @if($user->foto_profil_path)
                <img src="{{ asset('storage/'.$user->foto_profil_path) }}" alt="Foto Profil"
                     class="h-24 w-24 rounded-full border-4 border-surface object-cover">
            @else
                <div class="flex h-24 w-24 items-center justify-center rounded-full border-4 border-surface bg-primary-soft text-3xl font-semibold text-on-primary-soft">
                    {{ $user->initials() }}
                </div>
            @endif

            {{-- Badge Ganti Foto --}}
            <label class="absolute bottom-0 right-0 flex h-11 w-11 cursor-pointer items-center justify-center rounded-full border-2 border-surface bg-surface text-ink shadow-[0_1px_2px_rgb(0_0_0/0.06)] transition hover:bg-inset" aria-label="Ganti foto profil">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z" /><path stroke-linecap="round" stroke-linejoin="round" d="M15 13a3 3 0 11-6 0 3 3 0 016 0z" /></svg>
                <input type="file" wire:model="fotoBaru" accept="image/*" class="hidden">
            </label>
        </div>

        <h2 class="mt-3 text-xl font-semibold text-ink">{{ $user->name }}</h2>
        <p class="mt-0.5 text-sm text-mute">{{ $labelSejak }} {{ $user->created_at->translatedFormat('d M Y') }}</p>
    </div>

    {{-- Form --}}
    <form wire:submit="updateProfileInformation" class="mt-8 space-y-5">
        {{-- Nama --}}
        <div>
            <label for="profil-nama" class="mb-1.5 block text-sm font-medium text-body">Nama Lengkap</label>
            <input type="text" id="profil-nama" wire:model="name" required
                   class="h-12 w-full rounded-xl border {{ $errors->has('name') ? 'border-danger' : 'border-hairline-strong' }} bg-white px-4 text-base text-ink focus:border-primary focus:outline-none focus:ring-2 focus:ring-primary/20 dark:bg-surface" />
            @error('name') <p class="mt-1.5 flex items-center gap-1 text-sm text-danger dark:text-dark-danger-text"><flux:icon.exclamation-circle class="size-4 shrink-0" aria-hidden="true" />{{ $message }}</p> @enderror
        </div>

        {{-- No. HP (Read Only) --}}
        <div>
            <label for="profil-no-hp" class="mb-1.5 block text-sm font-medium text-body">No. HP</label>
            <div class="relative">
                <input type="text" id="profil-no-hp" value="{{ $user->no_hp }}" readonly
                       class="h-12 w-full cursor-not-allowed rounded-xl border border-hairline-strong bg-inset px-4 pr-10 text-base text-mute" />
                <svg xmlns="http://www.w3.org/2000/svg" class="absolute right-3 top-1/2 h-4 w-4 -translate-y-1/2 text-subtle" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" /></svg>
            </div>
            <p class="mt-1 text-xs text-mute">No. HP tidak dapat diubah karena digunakan untuk login.</p>
        </div>

        {{-- Alamat --}}
        <div>
            <label for="profil-alamat" class="mb-1.5 block text-sm font-medium text-body">Alamat</label>
            <textarea id="profil-alamat" wire:model="alamat" rows="2" required
                      class="w-full rounded-xl border {{ $errors->has('alamat') ? 'border-danger' : 'border-hairline-strong' }} bg-white px-4 py-3 text-base text-ink focus:border-primary focus:outline-none focus:ring-2 focus:ring-primary/20 dark:bg-surface"></textarea>
            @error('alamat') <p class="mt-1.5 flex items-center gap-1 text-sm text-danger dark:text-dark-danger-text"><flux:icon.exclamation-circle class="size-4 shrink-0" aria-hidden="true" />{{ $message }}</p> @enderror
        </div>

        {{-- Tanggal Lahir --}}
        <div>
            <label for="profil-tanggal-lahir" class="mb-1.5 block text-sm font-medium text-body">Tanggal Lahir</label>
            <x-ui.tanggal id="profil-tanggal-lahir" wire:model="tanggalLahir" :max="now()->toDateString()"
                   class="h-12 w-full rounded-xl border {{ $errors->has('tanggalLahir') ? 'border-danger' : 'border-hairline-strong' }} bg-white px-4 text-base text-ink focus:border-primary focus:outline-none focus:ring-2 focus:ring-primary/20 dark:bg-surface" />
            @error('tanggalLahir') <p class="mt-1.5 flex items-center gap-1 text-sm text-danger dark:text-dark-danger-text"><flux:icon.exclamation-circle class="size-4 shrink-0" aria-hidden="true" />{{ $message }}</p> @enderror
        </div>

        {{-- Jenis Kelamin --}}
        <div>
            <label for="profil-jenis-kelamin" class="mb-1.5 block text-sm font-medium text-body">Jenis Kelamin</label>
            <select id="profil-jenis-kelamin" wire:model="jenisKelamin" required
                    class="h-12 w-full rounded-xl border border-hairline-strong bg-white px-4 text-base text-ink focus:border-primary focus:outline-none focus:ring-2 focus:ring-primary/20 dark:bg-surface">
                <option value="laki-laki">Laki-laki</option>
                <option value="perempuan">Perempuan</option>
            </select>
        </div>

        {{-- Pekerjaan --}}
        <div>
            <label for="profil-pekerjaan" class="mb-1.5 block text-sm font-medium text-body">Pekerjaan</label>
            <input type="text" id="profil-pekerjaan" wire:model="pekerjaan"
                   class="h-12 w-full rounded-xl border border-hairline-strong bg-white px-4 text-base text-ink focus:border-primary focus:outline-none focus:ring-2 focus:ring-primary/20 dark:bg-surface" />
        </div>

        {{-- Tombol Simpan --}}
        <button type="submit"
                class="flex h-12 w-full items-center justify-center rounded-full bg-primary text-base font-medium text-on-primary transition hover:bg-primary-hover active:scale-[0.98]">
            Simpan Perubahan
        </button>
    </form>
</section>

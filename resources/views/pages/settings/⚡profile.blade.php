<?php

use App\Concerns\ProfileValidationRules;
use Flux\Flux;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithFileUploads;

new #[Title('Profile settings')] class extends Component {
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
        $this->no_hp = $user->no_hp;

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

        Flux::toast(variant: 'success', text: __('Profile updated.'));
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
    @endphp

    {{-- Banner --}}
    <div class="relative -mx-4 -mt-4 h-[140px] overflow-hidden rounded-b-2xl sm:mx-0 sm:rounded-t-2xl sm:rounded-b-none">
        @if($user->banner_path)
            <img src="{{ asset('storage/'.$user->banner_path) }}" alt="Banner" class="h-full w-full object-cover">
        @else
            <div class="h-full w-full" style="background: linear-gradient(135deg, #6C5CE7, #3B82F6);"></div>
        @endif

        {{-- Back button --}}
        <a href="{{ $backRoute }}" wire:navigate
           class="absolute left-3 top-3 flex h-8 w-8 items-center justify-center rounded-full bg-black/30 text-white backdrop-blur-sm">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7" /></svg>
        </a>

        {{-- Ganti Banner --}}
        <label class="absolute right-3 top-3 flex h-8 cursor-pointer items-center gap-1.5 rounded-full bg-black/30 px-3 text-xs font-medium text-white backdrop-blur-sm transition hover:bg-black/50">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z" /><path stroke-linecap="round" stroke-linejoin="round" d="M15 13a3 3 0 11-6 0 3 3 0 016 0z" /></svg>
            Ganti Banner
            <input type="file" wire:model="bannerBaru" accept="image/*" class="hidden">
        </label>
    </div>

    {{-- Avatar + Info --}}
    <div class="flex flex-col items-center -mt-12 relative z-10">
        <div class="relative">
            @if($user->foto_profil_path)
                <img src="{{ asset('storage/'.$user->foto_profil_path) }}" alt="Foto Profil"
                     class="h-24 w-24 rounded-full border-4 border-white object-cover shadow-lg dark:border-zinc-800">
            @else
                <div class="flex h-24 w-24 items-center justify-center rounded-full border-4 border-white bg-[#171717] text-3xl font-bold text-white shadow-lg dark:border-zinc-800">
                    {{ $user->initials() }}
                </div>
            @endif

            {{-- Badge Ganti Foto --}}
            <label class="absolute bottom-0 right-0 flex h-8 w-8 cursor-pointer items-center justify-center rounded-full border-2 border-white bg-[#fafafa] text-[#171717] shadow-md transition hover:bg-[#ebebeb] dark:border-zinc-800 dark:bg-zinc-700 dark:text-white dark:hover:bg-zinc-600">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z" /><path stroke-linecap="round" stroke-linejoin="round" d="M15 13a3 3 0 11-6 0 3 3 0 016 0z" /></svg>
                <input type="file" wire:model="fotoBaru" accept="image/*" class="hidden">
            </label>
        </div>

        <h2 class="mt-3 text-xl font-bold text-[#171717] dark:text-white">{{ $user->name }}</h2>
        <p class="mt-0.5 text-sm text-[#888888] dark:text-zinc-400">Nasabah sejak {{ $user->created_at->translatedFormat('d M Y') }}</p>
    </div>

    {{-- Form --}}
    <form wire:submit="updateProfileInformation" class="mt-8 space-y-5">
        {{-- Nama --}}
        <div>
            <label class="mb-1.5 block text-sm font-medium text-[#171717] dark:text-zinc-200">Nama Lengkap</label>
            <input type="text" wire:model="name" required
                   class="h-11 w-full rounded-xl border border-[#ebebeb] bg-[#fafafa] px-4 text-sm text-[#171717] focus:border-[#171717] focus:outline-none focus:ring-2 focus:ring-[#171717]/10 dark:border-zinc-600 dark:bg-zinc-700 dark:text-white dark:focus:border-white dark:focus:ring-white/20" />
            @error('name') <p class="mt-1.5 text-xs text-[#ee0000]">{{ $message }}</p> @enderror
        </div>

        {{-- No. HP (Read Only) --}}
        <div>
            <label class="mb-1.5 block text-sm font-medium text-[#171717] dark:text-zinc-200">No. HP</label>
            <div class="relative">
                <input type="text" value="{{ $user->no_hp }}" readonly
                       class="h-11 w-full rounded-xl border border-[#ebebeb] bg-[#f5f5f5] px-4 pr-10 text-sm text-[#888888] cursor-not-allowed dark:border-zinc-600 dark:bg-zinc-700/50 dark:text-zinc-400" />
                <svg xmlns="http://www.w3.org/2000/svg" class="absolute right-3 top-1/2 h-4 w-4 -translate-y-1/2 text-[#a1a1a1] dark:text-zinc-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" /></svg>
            </div>
            <p class="mt-1 text-xs text-[#888888] dark:text-zinc-500">No. HP tidak dapat diubah karena digunakan untuk login.</p>
        </div>

        {{-- Alamat --}}
        <div>
            <label class="mb-1.5 block text-sm font-medium text-[#171717] dark:text-zinc-200">Alamat</label>
            <textarea wire:model="alamat" rows="2" required
                      class="w-full rounded-xl border border-[#ebebeb] bg-[#fafafa] px-4 py-3 text-sm text-[#171717] focus:border-[#171717] focus:outline-none focus:ring-2 focus:ring-[#171717]/10 dark:border-zinc-600 dark:bg-zinc-700 dark:text-white dark:focus:border-white dark:focus:ring-white/20"></textarea>
            @error('alamat') <p class="mt-1.5 text-xs text-[#ee0000]">{{ $message }}</p> @enderror
        </div>

        {{-- Tanggal Lahir --}}
        <div>
            <label class="mb-1.5 block text-sm font-medium text-[#171717] dark:text-zinc-200">Tanggal Lahir</label>
            <input type="date" wire:model="tanggalLahir" required
                   class="h-11 w-full rounded-xl border border-[#ebebeb] bg-[#fafafa] px-4 text-sm text-[#171717] focus:border-[#171717] focus:outline-none focus:ring-2 focus:ring-[#171717]/10 dark:border-zinc-600 dark:bg-zinc-700 dark:text-white dark:focus:border-white dark:focus:ring-white/20" />
            @error('tanggalLahir') <p class="mt-1.5 text-xs text-[#ee0000]">{{ $message }}</p> @enderror
        </div>

        {{-- Jenis Kelamin --}}
        <div>
            <label class="mb-1.5 block text-sm font-medium text-[#171717] dark:text-zinc-200">Jenis Kelamin</label>
            <select wire:model="jenisKelamin" required
                    class="h-11 w-full rounded-xl border border-[#ebebeb] bg-[#fafafa] px-4 text-sm text-[#171717] focus:border-[#171717] focus:outline-none focus:ring-2 focus:ring-[#171717]/10 dark:border-zinc-600 dark:bg-zinc-700 dark:text-white dark:focus:border-white dark:focus:ring-white/20">
                <option value="laki-laki">Laki-laki</option>
                <option value="perempuan">Perempuan</option>
            </select>
        </div>

        {{-- Pekerjaan --}}
        <div>
            <label class="mb-1.5 block text-sm font-medium text-[#171717] dark:text-zinc-200">Pekerjaan</label>
            <input type="text" wire:model="pekerjaan"
                   class="h-11 w-full rounded-xl border border-[#ebebeb] bg-[#fafafa] px-4 text-sm text-[#171717] focus:border-[#171717] focus:outline-none focus:ring-2 focus:ring-[#171717]/10 dark:border-zinc-600 dark:bg-zinc-700 dark:text-white dark:focus:border-white dark:focus:ring-white/20" />
        </div>

        {{-- Tombol Simpan --}}
        <button type="submit"
                class="w-full rounded-xl bg-[#171717] py-3 text-sm font-semibold text-white transition hover:opacity-90 dark:bg-white dark:text-zinc-900">
            Simpan Perubahan
        </button>
    </form>
</section>

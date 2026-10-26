<?php

namespace App\Livewire\Admin;

use App\Livewire\Concerns\AuthorizesRole;
use App\Models\User;
use Illuminate\View\View;
use Livewire\Component;

class Search extends Component
{
    use AuthorizesRole;

    protected function requiredRole(): string
    {
        return 'admin';
    }

    public string $search = '';

    /** @var array<int, array{id: int, name: string}> */
    public array $results = [];

    public bool $showResults = false;

    public function updatedSearch(): void
    {
        if (strlen($this->search) < 2) {
            $this->results = [];
            $this->showResults = false;

            return;
        }

        $this->results = User::where('role', 'nasabah')
            ->where('name', 'like', '%'.addcslashes($this->search, '%_\\').'%')
            ->limit(8)
            ->get()
            ->map(fn ($user) => [
                'id' => $user->id,
                'name' => $user->name,
            ])
            ->toArray();

        $this->showResults = count($this->results) > 0;
    }

    public function closeResults(): void
    {
        $this->showResults = false;
    }

    public function render(): View
    {
        return view('livewire.admin.search');
    }
}

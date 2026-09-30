<?php

namespace App\Livewire\Admin;

use App\Livewire\Concerns\AuthorizesRole;
use App\Models\User;
use Livewire\Component;

class Search extends Component
{
    use AuthorizesRole;

    protected function requiredRole(): string
    {
        return 'admin';
    }

    public $search = '';

    public $results = [];

    public $showResults = false;

    public function updatedSearch()
    {
        if (strlen($this->search) < 2) {
            $this->results = [];
            $this->showResults = false;

            return;
        }

        $this->results = User::where('role', 'nasabah')
            ->where('name', 'like', "%{$this->search}%")
            ->limit(8)
            ->get()
            ->map(fn ($user) => [
                'id' => $user->id,
                'name' => $user->name,
                'no_hp' => $user->no_hp,
            ])
            ->toArray();

        $this->showResults = count($this->results) > 0;
    }

    public function closeResults()
    {
        $this->showResults = false;
    }

    public function render()
    {
        return view('livewire.admin.search');
    }
}

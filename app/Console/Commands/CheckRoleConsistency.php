<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;

#[Signature('role:check-consistency')]
#[Description('Check consistency between users.role column and Spatie Permission roles')]
class CheckRoleConsistency extends Command
{
    public function handle(): int
    {
        $users = User::all();
        $inconsistent = collect();

        foreach ($users as $user) {
            $spatieRole = $user->getRoleNames()->first();
            $columnRole = $user->role;

            if ($columnRole !== $spatieRole) {
                $inconsistent->push([
                    'id' => $user->id,
                    'name' => $user->name,
                    'column_role' => $columnRole ?? '(null)',
                    'spatie_role' => $spatieRole ?? '(null)',
                ]);
            }
        }

        if ($inconsistent->isEmpty()) {
            $this->info('All users have consistent roles.');

            return self::SUCCESS;
        }

        $this->error("Found {$inconsistent->count()} inconsistent user(s):");

        $this->table(
            ['ID', 'Name', 'Column Role', 'Spatie Role'],
            $inconsistent->toArray()
        );

        return self::FAILURE;
    }
}

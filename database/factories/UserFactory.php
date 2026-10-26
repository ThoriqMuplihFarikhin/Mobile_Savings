<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

/**
 * @extends Factory<User>
 */
class UserFactory extends Factory
{
    /**
     * The current password being used by the factory.
     */
    protected static ?string $password;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => fake()->name(),
            'no_hp' => fake()->unique()->numerify('08##########'),
            'pin_hash' => Hash::make('123456'),
            'role' => 'nasabah',
            'status_akun' => 'aktif',
            'harus_ganti_pin' => false,
            'email' => fake()->unique()->safeEmail(),
            'email_verified_at' => now(),
            'password' => static::$password ??= Hash::make('password'),
            'remember_token' => Str::random(10),
        ];
    }

    /**
     * Indicate that the model's email address should be unverified.
     */
    public function unverified(): static
    {
        return $this->state(fn (array $attributes) => [
            'email_verified_at' => null,
        ]);
    }

    public function admin(): static
    {
        return $this->state(fn (array $attributes) => [
            'role' => 'admin',
        ])->afterCreating(function (User $user) {
            $user->assignRole('admin');
        });
    }

    public function kolektor(): static
    {
        return $this->state(fn (array $attributes) => [
            'role' => 'kolektor',
        ])->afterCreating(function (User $user) {
            $user->assignRole('kolektor');
        });
    }

    public function nasabah(): static
    {
        return $this->state(fn (array $attributes) => [
            'role' => 'nasabah',
        ])->afterCreating(function (User $user) {
            $user->assignRole('nasabah');
        });
    }

    /**
     * Indicate that the model has two-factor authentication configured.
     */
    public function withTwoFactor(): static
    {
        return $this;
    }
}

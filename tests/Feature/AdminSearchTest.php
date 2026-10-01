<?php

use App\Livewire\Admin\Search;
use App\Models\User;
use Livewire\Livewire;

it('search dengan wildcard tidak mengembalikan semua nasabah', function () {
    User::factory()->nasabah()->create(['name' => 'Budi Santoso']);
    User::factory()->nasabah()->create(['name' => 'Siti Aminah']);
    User::factory()->admin()->create(['name' => 'Admin Satu']);

    $this->actingAs(User::factory()->admin()->create());

    $component = Livewire::test(Search::class)->set('search', '%%');
    expect($component->viewData('results'))->toBe([]);

    $component = Livewire::test(Search::class)->set('search', '__');
    expect($component->viewData('results'))->toBe([]);
});

it('search tidak menyertakan no_hp', function () {
    User::factory()->nasabah()->create(['name' => 'Budi Santoso', 'no_hp' => '081111111111']);

    $this->actingAs(User::factory()->admin()->create());

    $component = Livewire::test(Search::class)->set('search', 'Budi');

    $results = $component->viewData('results');
    expect($results)->toHaveCount(1)
        ->and($results[0])->not->toHaveKey('no_hp');
    $component->assertDontSee('081111111111');
});

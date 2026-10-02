<?php

use App\Models\User;

test('profile page is displayed', function () {
    $this->actingAs($user = User::factory()->nasabah()->create());

    $this->get(route('profile.edit'))->assertOk();
});

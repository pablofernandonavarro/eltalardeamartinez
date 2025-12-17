<?php

use App\Models\User;

test('guests are redirected to the login page', function () {
    $this->get('/dashboard')->assertRedirect('/login');
});

test('authenticated users can visit the dashboard', function () {
    $this->actingAs($user = User::factory()->create());

    // En esta app /dashboard funciona como router hacia el panel según rol
    $this->get('/dashboard')->assertRedirect(route('resident.dashboard', absolute: false));
});

<?php

use Inertia\Testing\AssertableInertia as Assert;

test('the homepage returns the welcome portfolio payload', function () {
    $response = $this->get(route('home'));

    $response->assertOk()->assertInertia(fn (Assert $page) => $page
        ->component('Welcome')
        ->has('heroStats')
        ->has('featuredTags')
        ->has('projects.data')
        ->where('canRegister', true));
});

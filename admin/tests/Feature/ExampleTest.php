<?php

declare(strict_types=1);

use function Pest\Laravel\withoutExceptionHandling;

it('returns a successful response', function () {
    withoutExceptionHandling();

    $response = $this->get('/');

    $response->assertStatus(302);
});

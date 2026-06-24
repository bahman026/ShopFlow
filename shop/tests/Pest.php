<?php

declare(strict_types=1);

/*
|--------------------------------------------------------------------------
| Test Case
|--------------------------------------------------------------------------
*/

use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

// The admin app owns the shared database schema, so the shop never migrates it.
// Each feature test runs inside a transaction that is rolled back afterwards,
// against the pre-migrated shared Postgres test database.
uses(
    TestCase::class,
    DatabaseTransactions::class,
)->in('Feature');

/*
|--------------------------------------------------------------------------
| Expectations
|--------------------------------------------------------------------------
*/

expect()->extend('toBeOne', function () {
    return $this->toBe(1);
});

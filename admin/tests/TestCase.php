<?php

declare(strict_types=1);

namespace Tests;

use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    /**
     * Roles and permissions are seeded **once per test run**, not per test.
     *
     * `RefreshDatabase` runs `migrate:fresh` (and this seeder with it) only
     * while `RefreshDatabaseState::$migrated` is false, and it does so *before*
     * `beginDatabaseTransaction()` — so these rows are committed and outlive
     * every per-test rollback.
     *
     * This used to live in the `login()` helper, which meant re-creating three
     * roles, ~28 permissions and three `syncPermissions()` pivot rewrites
     * before *every* test in 44 files. Seeding here does the same work once.
     *
     * @var class-string<\Illuminate\Database\Seeder>
     */
    protected string $seeder = RolePermissionSeeder::class;
}

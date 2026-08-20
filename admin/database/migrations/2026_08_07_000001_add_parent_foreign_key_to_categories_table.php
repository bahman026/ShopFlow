<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * `categories.parent_id` was the only relationship in the schema with no
     * foreign key — a bare integer. Deleting a parent left its children
     * pointing at a row that no longer existed, and because the storefront
     * walks `parent_id` to collect descendants, an orphaned subtree silently
     * disappeared from category listings instead of failing loudly.
     */
    public function up(): void
    {
        // Promote any already-orphaned child to a root category. There is
        // nothing else to point it at, and it is better than refusing to add
        // the constraint at all.
        DB::table('categories')
            ->whereNotNull('parent_id')
            ->whereNotIn('parent_id', DB::table('categories')->select('id'))
            ->update(['parent_id' => null]);

        // `categories.id` is a bigint while `parent_id` was a 4-byte integer;
        // the types have to match before one can reference the other.
        Schema::table('categories', function (Blueprint $table): void {
            $table->unsignedBigInteger('parent_id')->nullable()->change();
        });

        Schema::table('categories', function (Blueprint $table): void {
            // Restrict rather than cascade: cascading would silently delete an
            // entire subtree (and, through products, a lot more). This matches
            // products.category_id, which already restricts.
            $table->foreign('parent_id')
                ->references('id')
                ->on('categories')
                ->restrictOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('categories', function (Blueprint $table): void {
            $table->dropForeign(['parent_id']);
        });

        Schema::table('categories', function (Blueprint $table): void {
            $table->unsignedInteger('parent_id')->nullable()->change();
        });
    }
};

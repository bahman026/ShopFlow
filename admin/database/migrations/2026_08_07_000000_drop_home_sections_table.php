<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Drops the admin-composed home page.
     *
     * The storefront never read this table: it always rendered a fixed layout,
     * so reordering or disabling a row here changed nothing on the site. The
     * layout stays fixed in the storefront and the indirection goes away.
     *
     * The original create migration is deleted rather than kept, so a fresh
     * database never builds the table in the first place; `dropIfExists` is
     * what removes it from databases that already ran it.
     */
    public function up(): void
    {
        Schema::dropIfExists('home_sections');
    }

    /**
     * Recreates the table as it was, so the migration is reversible. Nothing
     * reads or writes it — restoring the feature would also mean restoring the
     * model, resource and enum from git history.
     */
    public function down(): void
    {
        Schema::create('home_sections', function (Blueprint $table): void {
            $table->id();
            $table->string('type')->default('products');
            $table->string('title')->nullable();
            $table->json('config')->nullable();
            $table->unsignedInteger('order')->default(0);
            $table->boolean('status')->default(true);
            $table->timestamps();
            $table->index(['status', 'order']);
        });
    }
};

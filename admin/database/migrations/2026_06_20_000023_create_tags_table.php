<?php

declare(strict_types=1);

use App\Models\Category;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tags', function (Blueprint $table): void {
            $table->id();
            $table->text('name');
            $table->text('slug')->unique();
            // A tag is an SEO landing page for a category and/or attribute
            // filter (see docs/TAGS.md). Both are optional (at least one is
            // required, enforced in the app) so a tag can scope by category
            // only, by attribute(s) only, or both. Attributes are a
            // many-to-many via the attribute_tag pivot.
            $table->foreignIdFor(Category::class)->nullable()->constrained()->cascadeOnDelete();
            $table->text('content')->nullable();
            // SEO fields, mirroring categories/products (decision: extend the
            // schema rather than reuse name/content, since tags exist for SEO).
            $table->text('title')->nullable();
            $table->string('description')->nullable();
            $table->boolean('no_index')->default(false);
            $table->text('canonical')->nullable();
            // Home-page placement: whether the tag shows in the storefront's
            // featured-tags strip, and in what order. This is how a customer
            // discovers a tag from the home page (its image + name link to
            // /tags/{slug}). The image itself is a polymorphic `images` row.
            $table->boolean('show_on_home')->default(false);
            $table->unsignedInteger('home_order')->default(0);
            $table->timestamps();
            // Note: the original source schema had a `type` (user/seller) column;
            // dropped here — ShopFlow is single-vendor, so it has no meaning.
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tags');
    }
};

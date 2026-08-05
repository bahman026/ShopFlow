<?php

declare(strict_types=1);

use App\Enums\HomeSectionTypeEnum;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // The storefront home page is composed from this ordered list of
        // sections instead of a hardcoded layout: staff add/reorder/toggle
        // rows, and the frontend renders each `type` with its component.
        Schema::create('home_sections', function (Blueprint $table): void {
            $table->id();
            $table->string('type')->default(HomeSectionTypeEnum::PRODUCTS->value);
            $table->string('title')->nullable();
            // Type-specific settings (e.g. {"position":"home-main"} for a
            // slider, {"sort":"newest"} for a product row). Null for types
            // that need none (tags/categories/brands).
            $table->json('config')->nullable();
            $table->unsignedInteger('order')->default(0);
            $table->boolean('status')->default(true);
            $table->timestamps();

            $table->index(['status', 'order']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('home_sections');
    }
};

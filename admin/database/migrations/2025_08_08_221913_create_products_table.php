<?php

declare(strict_types=1);

use App\Enums\ProductStatusEnum;
use App\Models\AttributeGroup;
use App\Models\Brand;
use App\Models\Category;
use App\Models\Image;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->string('heading');
            $table->string('slug')->unique();
            $table->decimal('price', 10, 2);
            $table->text('content')->nullable();
            $table->string('title')->nullable();
            $table->text('description')->nullable();
            $table->boolean('no_index')->default(false);
            $table->string('canonical')->nullable();
            $table->foreignIdFor(Image::class)->nullable();
            $table->foreignIdFor(AttributeGroup::class)->nullable();
            $table->foreignIdFor(Category::class);
            $table->foreignIdFor(Brand::class)->nullable();
            $table->unsignedInteger('minimum')->default(1);
            $table->unsignedInteger('maximum')->nullable();
            $table->unsignedInteger('step')->default(1);
            $table->decimal('profit_percent', 5, 2)->default(0);
            $table->json('attributes')->nullable();
            $table->json('highlight')->nullable();
            $table->boolean('has_stock')->default(true);
            $table->unsignedInteger('variety_counts')->default(0);
            $table->decimal('weight', 10, 2)->nullable();
            $table->decimal('length', 10, 2)->nullable();
            $table->decimal('width', 10, 2)->nullable();
            $table->decimal('height', 10, 2)->nullable();
            $table->unsignedTinyInteger('status')->default(ProductStatusEnum::PUBLISHED->value);
            $table->unsignedBigInteger('seen')->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};

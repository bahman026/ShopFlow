<?php

use App\Enums\BrandStatusEnum;
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
        Schema::create('brands', function (Blueprint $table) {
            $table->id();
            $table->string('heading');
            $table->string('slug')->unique();
            $table->text('content')->nullable();
            $table->string('title')->nullable();
            $table->string('description')->nullable();
            $table->boolean('no_index')->default(false);
            $table->text('canonical')->nullable();
            $table->foreignIdFor(Image::class)->nullable();
            $table->unsignedTinyInteger('status')->default(BrandStatusEnum::ACTIVE);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('brands');
    }
};

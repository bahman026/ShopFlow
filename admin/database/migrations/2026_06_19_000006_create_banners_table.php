<?php

declare(strict_types=1);

use App\Enums\BannerStatusEnum;
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
        Schema::create('banners', function (Blueprint $table) {
            $table->id();
            $table->string('position');
            $table->string('heading');
            $table->string('url')->nullable();
            $table->unsignedInteger('sort')->nullable();
            $table->unsignedTinyInteger('status')->default(BannerStatusEnum::PUBLISHED->value);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('banners');
    }
};

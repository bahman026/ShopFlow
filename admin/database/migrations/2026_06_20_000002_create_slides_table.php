<?php

declare(strict_types=1);

use App\Models\Slider;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('slides', function (Blueprint $table) {
            $table->id();
            $table->foreignIdFor(Slider::class)->constrained()->cascadeOnDelete();
            $table->string('heading')->nullable();
            $table->string('label')->nullable();
            $table->string('url')->nullable();
            $table->unsignedInteger('order')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('slides');
    }
};

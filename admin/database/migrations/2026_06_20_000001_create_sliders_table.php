<?php

declare(strict_types=1);

use App\Enums\SliderStatusEnum;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sliders', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('position');
            $table->unsignedTinyInteger('status')->default(SliderStatusEnum::PUBLISHED->value);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sliders');
    }
};

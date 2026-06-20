<?php

declare(strict_types=1);

use App\Enums\PageStatusEnum;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pages', function (Blueprint $table) {
            $table->id();
            $table->string('heading');
            $table->string('slug')->unique();
            $table->longText('content')->nullable();
            $table->string('title')->nullable();
            $table->string('description')->nullable();
            $table->boolean('no_index')->default(false);
            $table->string('canonical')->nullable();
            $table->unsignedTinyInteger('status')->default(PageStatusEnum::PUBLISHED->value);
            $table->timestamp('published_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pages');
    }
};

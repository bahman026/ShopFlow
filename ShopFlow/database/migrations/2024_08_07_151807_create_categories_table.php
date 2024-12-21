<?php

declare(strict_types=1);

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
        Schema::create('categories', function (Blueprint $table) {
            $table->id();
            $table->text('heading');
            $table->text('slug')->unique();
            $table->string('content')->nullable();
            $table->text('title')->nullable();
            $table->string('description')->nullable();
            $table->boolean('no_index')->default(false);
            $table->text('canonical')->nullable();
            $table->unsignedInteger('parent_id')->nullable();
            $table->unsignedTinyInteger('status');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('categories');
    }
};

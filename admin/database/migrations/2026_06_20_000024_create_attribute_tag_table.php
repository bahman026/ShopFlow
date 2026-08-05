<?php

declare(strict_types=1);

use App\Models\Attribute;
use App\Models\Tag;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('attribute_tag', function (Blueprint $table): void {
            $table->foreignIdFor(Attribute::class)->constrained()->cascadeOnDelete();
            $table->foreignIdFor(Tag::class)->constrained()->cascadeOnDelete();
            $table->timestamps();

            $table->unique(['attribute_id', 'tag_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('attribute_tag');
    }
};

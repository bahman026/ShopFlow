<?php

declare(strict_types=1);

use App\Enums\ReviewStatusEnum;
use App\Models\Product;
use App\Models\User;
use App\Models\Variety;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('reviews', function (Blueprint $table): void {
            $table->id();
            $table->string('heading');
            $table->text('content');
            $table->foreignIdFor(User::class)->nullable()->constrained()->nullOnDelete();
            $table->foreignIdFor(Product::class)->constrained()->cascadeOnDelete();
            $table->foreignIdFor(Variety::class)->nullable()->constrained()->nullOnDelete();
            $table->foreignId('parent_id')->nullable()->constrained('reviews')->nullOnDelete();
            $table->unsignedTinyInteger('status')->default(ReviewStatusEnum::PENDING->value);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('reviews');
    }
};

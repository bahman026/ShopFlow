<?php

declare(strict_types=1);

use App\Enums\DiscountForEnum;
use App\Models\Variety;
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
        Schema::create('discounts', function (Blueprint $table) {
            $table->id();
            $table->foreignIdFor(Variety::class)->constrained()->cascadeOnDelete();
            $table->unsignedInteger('quantity')->default(1);
            $table->unsignedInteger('priority')->default(0);
            $table->boolean('is_percent')->default(false);
            $table->decimal('amount', 10, 2);
            $table->dateTime('started_at')->nullable();
            $table->dateTime('ended_at')->nullable();
            $table->unsignedInteger('sold')->default(0);
            $table->unsignedInteger('max_sell')->nullable();
            $table->unsignedInteger('max_sell_by_user')->nullable();
            $table->unsignedTinyInteger('is_for')->default(DiscountForEnum::EVERYONE->value);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('discounts');
    }
};

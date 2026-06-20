<?php

declare(strict_types=1);

use App\Models\Coupon;
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
        Schema::create('coupon_variety', function (Blueprint $table) {
            $table->id();
            $table->foreignIdFor(Coupon::class)->constrained()->cascadeOnDelete();
            $table->foreignIdFor(Variety::class)->constrained()->cascadeOnDelete();
            $table->timestamps();

            $table->unique(['coupon_id', 'variety_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('coupon_variety');
    }
};

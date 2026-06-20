<?php

declare(strict_types=1);

use App\Enums\CouponForEnum;
use App\Enums\CouponStatusEnum;
use App\Models\User;
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
        Schema::create('coupons', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('code')->unique();
            $table->decimal('amount', 10, 2);
            $table->decimal('min_price', 10, 2)->nullable();
            $table->decimal('max_discount', 10, 2)->nullable();
            $table->unsignedInteger('total_used')->default(0);
            $table->unsignedInteger('total_uses')->nullable();
            $table->foreignIdFor(User::class)->nullable()->constrained()->nullOnDelete();
            $table->foreignId('user_creator_id')->nullable()->constrained('users')->nullOnDelete();
            $table->unsignedTinyInteger('status')->default(CouponStatusEnum::ACTIVE->value);
            $table->boolean('is_percent')->default(false);
            $table->boolean('shipping')->default(false);
            $table->unsignedTinyInteger('is_for')->default(CouponForEnum::EVERYONE->value);
            $table->dateTime('started_at')->nullable();
            $table->dateTime('expired_at')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('coupons');
    }
};

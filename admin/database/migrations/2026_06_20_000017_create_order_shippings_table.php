<?php

declare(strict_types=1);

use App\Models\Order;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('order_shippings', function (Blueprint $table): void {
            $table->id();
            $table->foreignIdFor(Order::class)->constrained()->cascadeOnDelete();
            $table->foreignId('checker_id')->nullable()->constrained('users')->nullOnDelete();
            $table->unsignedInteger('pack_count')->nullable();
            $table->foreignId('pack_user')->nullable()->constrained('users')->nullOnDelete();
            $table->dateTime('checked_at')->nullable();
            $table->dateTime('shipped_at')->nullable();
            $table->foreignId('sender_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('courier_id')->nullable()->constrained('users')->nullOnDelete();
            $table->dateTime('courier_received_at')->nullable();
            $table->dateTime('courier_delivered_at')->nullable();
            $table->string('confirm_code')->nullable();
            $table->boolean('cheque_is_require')->default(false);
            $table->foreignId('courier2_id')->nullable()->constrained('users')->nullOnDelete();
            $table->text('description')->nullable();
            $table->unsignedTinyInteger('payment_type')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('order_shippings');
    }
};

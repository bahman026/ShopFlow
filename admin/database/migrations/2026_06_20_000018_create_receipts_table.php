<?php

declare(strict_types=1);

use App\Enums\ReceiptTypeEnum;
use App\Models\Order;
use App\Models\User;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('receipts', function (Blueprint $table): void {
            $table->id();
            $table->foreignIdFor(User::class)->nullable()->constrained()->nullOnDelete();

            // cards / saved-cards table is not built yet (Phase 5), so this
            // stays a plain nullable column with no FK constraint.
            $table->unsignedBigInteger('card_id')->nullable();

            $table->decimal('amount', 12, 2)->default(0);
            $table->foreignIdFor(Order::class)->nullable()->constrained()->nullOnDelete();
            $table->string('tracking_code')->nullable();
            $table->dateTime('paid_at')->nullable();
            $table->string('destination_name')->nullable();
            $table->string('destination_bank')->nullable();
            $table->string('end_of_card_number')->nullable();
            $table->text('description')->nullable();
            $table->boolean('is_paya')->nullable();
            $table->unsignedTinyInteger('type')->default(ReceiptTypeEnum::RECEIPT->value);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('receipts');
    }
};

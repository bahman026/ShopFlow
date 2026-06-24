<?php

declare(strict_types=1);

use App\Enums\TransactionStatusEnum;
use App\Models\Order;
use App\Models\User;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('transactions', function (Blueprint $table): void {
            $table->id();
            $table->foreignIdFor(User::class)->nullable()->constrained()->nullOnDelete();
            $table->foreignIdFor(Order::class)->nullable()->constrained()->nullOnDelete();
            $table->string('ref_id')->nullable();
            $table->decimal('amount', 12, 2)->default(0);

            // accounting table is not built yet, so this is a plain nullable
            // column with no FK constraint.
            $table->unsignedBigInteger('accounting_id')->nullable();

            $table->unsignedTinyInteger('port')->nullable();
            $table->string('transaction_id')->nullable();
            $table->unsignedTinyInteger('status')->default(TransactionStatusEnum::PENDING->value);
            $table->string('ip')->nullable();
            $table->text('description')->nullable();
            $table->dateTime('paid_at')->nullable();
            $table->string('result_code')->nullable();
            $table->text('result_message')->nullable();
            $table->timestamps();

            $table->index('status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('transactions');
    }
};

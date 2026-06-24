<?php

declare(strict_types=1);

use App\Enums\OrderSrcEnum;
use App\Enums\OrderStatusEnum;
use App\Models\Coupon;
use App\Models\ShippingLine;
use App\Models\ShippingMethod;
use App\Models\User;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('orders', function (Blueprint $table): void {
            $table->id();
            $table->foreignIdFor(User::class)->nullable()->constrained()->nullOnDelete();
            $table->foreignIdFor(Coupon::class)->nullable()->constrained()->nullOnDelete();
            $table->unsignedTinyInteger('status')->default(OrderStatusEnum::PENDING->value);

            $table->decimal('coupon_discount', 12, 2)->default(0);
            $table->decimal('discount', 12, 2)->default(0);
            $table->decimal('shipping_cost', 12, 2)->default(0);
            $table->decimal('total_products_price', 12, 2)->default(0);
            $table->decimal('tax', 12, 2)->default(0);
            $table->decimal('total_price', 12, 2)->default(0);

            $table->boolean('for_partner')->default(false);
            $table->text('content')->nullable();
            $table->unsignedTinyInteger('src')->default(OrderSrcEnum::WEB->value);
            $table->string('version')->nullable();

            // Postal receipt (CRM). Image is referenced by id only; the images
            // table is polymorphic elsewhere, so no FK constraint here.
            $table->unsignedBigInteger('bijack_image_id')->nullable();
            $table->text('bijack_description')->nullable();

            // Accounting invoice. The accounting table is not built yet, so this
            // is a plain nullable reference with no FK constraint.
            $table->unsignedBigInteger('accounting_id')->nullable();

            $table->foreignId('confirmed_id')->nullable()->constrained('users')->nullOnDelete();
            $table->dateTime('confirmed_at')->nullable();
            $table->text('confirm_description')->nullable();

            $table->foreignId('collector_id')->nullable()->constrained('users')->nullOnDelete();
            $table->dateTime('collected_at')->nullable();
            $table->dateTime('collector_reminded_at')->nullable();
            $table->text('collector_description')->nullable();

            $table->foreignId('notifier_id')->nullable()->constrained('users')->nullOnDelete();
            $table->dateTime('notified_at')->nullable();

            $table->foreignIdFor(ShippingLine::class)->nullable()->constrained()->nullOnDelete();
            $table->foreignIdFor(ShippingMethod::class)->nullable()->constrained()->nullOnDelete();
            $table->text('send_description')->nullable();
            $table->time('receive_from')->nullable();
            $table->time('receive_to')->nullable();

            $table->timestamps();

            $table->index('status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('orders');
    }
};

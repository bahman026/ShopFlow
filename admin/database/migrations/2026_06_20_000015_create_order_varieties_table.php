<?php

declare(strict_types=1);

use App\Models\Order;
use App\Models\Product;
use App\Models\Variety;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('order_varieties', function (Blueprint $table): void {
            $table->id();
            $table->foreignIdFor(Order::class)->constrained()->cascadeOnDelete();
            $table->foreignIdFor(Product::class)->nullable()->constrained()->nullOnDelete();
            $table->foreignIdFor(Variety::class)->nullable()->constrained()->nullOnDelete();

            // sub_orders is seller-centric and intentionally not implemented
            // (single-vendor), so this stays a plain nullable column with no FK.
            $table->unsignedBigInteger('sub_order_id')->nullable();

            $table->unsignedInteger('quantity')->default(1);
            $table->unsignedInteger('gather_quantity')->default(0);
            $table->unsignedInteger('invoice_quantity')->default(0);

            $table->decimal('price', 12, 2)->default(0);
            $table->decimal('discount', 12, 2)->default(0);
            $table->decimal('coupon_discount', 12, 2)->default(0);
            $table->decimal('final_price', 12, 2)->default(0);

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('order_varieties');
    }
};

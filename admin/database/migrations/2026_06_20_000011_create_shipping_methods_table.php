<?php

declare(strict_types=1);

use App\Enums\ShippingMethodForEnum;
use App\Models\ShippingLine;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('shipping_methods', function (Blueprint $table): void {
            $table->id();
            $table->foreignIdFor(ShippingLine::class)->constrained()->restrictOnDelete();
            $table->string('name');
            $table->string('type')->nullable();
            $table->unsignedInteger('min_count')->nullable();
            $table->unsignedInteger('min_amount')->nullable();
            $table->unsignedTinyInteger('for')->default(ShippingMethodForEnum::CUSTOMER->value);
            $table->dateTime('disable_from')->nullable();
            $table->dateTime('disable_to')->nullable();
            $table->boolean('status')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('shipping_methods');
    }
};

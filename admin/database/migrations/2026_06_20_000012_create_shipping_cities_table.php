<?php

declare(strict_types=1);

use App\Models\City;
use App\Models\Province;
use App\Models\ShippingMethod;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('shipping_cities', function (Blueprint $table): void {
            $table->id();
            $table->foreignIdFor(ShippingMethod::class)->constrained()->cascadeOnDelete();
            $table->foreignIdFor(Province::class)->nullable()->constrained()->nullOnDelete();
            $table->foreignIdFor(City::class)->nullable()->constrained()->nullOnDelete();
            $table->boolean('pay_on_delivery')->default(false);
            $table->unsignedInteger('amount')->nullable();
            $table->string('sending_days')->nullable();
            $table->dateTime('disable_from')->nullable();
            $table->dateTime('disable_to')->nullable();
            $table->unsignedInteger('delay')->nullable();
            $table->text('description')->nullable();
            $table->boolean('status')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('shipping_cities');
    }
};

<?php

declare(strict_types=1);

use App\Enums\GatewayForEnum;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('gateways', function (Blueprint $table): void {
            $table->id();
            $table->string('key');
            $table->string('name');
            $table->unsignedTinyInteger('for')->default(GatewayForEnum::EVERYONE->value);
            $table->string('username')->nullable();
            $table->string('password')->nullable();
            $table->string('gate_id')->nullable();
            $table->boolean('active')->default(true);
            $table->unsignedInteger('priority')->default(0);
            $table->string('coding')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('gateways');
    }
};

<?php

declare(strict_types=1);

use App\Models\AttributeGroup;
use App\Models\Category;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAttributeGroupCategoryTable extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('attribute_group_category', function (Blueprint $table) {
            $table->id();
            $table->foreignIdFor(AttributeGroup::class);
            $table->foreignIdFor(Category::class);
            $table->boolean('as_filter')->default(false);
            $table->boolean('required')->default(false);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('attribute_group_categories');
    }
}

<?php

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
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('collection_id')->nullable();
            $table->string('name')->nullable();
            $table->string('handle')->nullable();
            $table->text('description')->nullable();
            $table->string('sku')->nullable();
            $table->string('status')->nullable();
            $table->string('shopify_product_id')->nullable();
            $table->string('image_url')->nullable();
            $table->decimal('base_price', 12, 2)->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};

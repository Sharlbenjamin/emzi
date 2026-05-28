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
        Schema::create('materials', function (Blueprint $table) {
            $table->id();
            $table->string('name')->nullable();
            $table->string('sku')->nullable();
            $table->string('category')->nullable();
            $table->unsignedBigInteger('supplier_id')->nullable();
            $table->string('unit_type')->nullable();
            $table->decimal('available_quantity', 12, 3)->nullable();
            $table->decimal('minimum_quantity_alert', 12, 3)->nullable();
            $table->decimal('cost_per_unit', 12, 2)->nullable();
            $table->string('color')->nullable();
            $table->text('notes')->nullable();
            $table->boolean('is_active')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('materials');
    }
};

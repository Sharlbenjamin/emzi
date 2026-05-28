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
        Schema::create('production_batch_material_orders', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('production_batch_id')->nullable();
            $table->unsignedBigInteger('material_id')->nullable();
            $table->unsignedBigInteger('supplier_id')->nullable();
            $table->decimal('required_quantity', 12, 3)->nullable();
            $table->decimal('ordered_quantity', 12, 3)->nullable();
            $table->decimal('received_quantity', 12, 3)->nullable();
            $table->string('status')->nullable();
            $table->date('ordered_at')->nullable();
            $table->date('expected_delivery_date')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('production_batch_material_orders');
    }
};

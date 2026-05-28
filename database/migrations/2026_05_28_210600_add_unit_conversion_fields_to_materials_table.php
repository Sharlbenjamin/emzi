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
        Schema::table('materials', function (Blueprint $table) {
            $table->decimal('meters_per_roll', 12, 3)->nullable()->after('unit_type');
            $table->decimal('kg_per_roll', 12, 3)->nullable()->after('meters_per_roll');
            $table->decimal('meters_per_piece', 12, 3)->nullable()->after('kg_per_roll');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('materials', function (Blueprint $table) {
            $table->dropColumn([
                'meters_per_roll',
                'kg_per_roll',
                'meters_per_piece',
            ]);
        });
    }
};

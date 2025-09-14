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
        Schema::table('product_services', function (Blueprint $table) {
            // Add the new column here
            $table->foreignId('productlocation_id')
                ->nullable()
                ->constrained('product_locations')
                ->after('category_id'); // Optional: Places the column neatly in the table
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('product_services', function (Blueprint $table) {
            // This allows you to undo the migration later if needed
            $table->dropForeign(['productlocation_id']);
            $table->dropColumn('productlocation_id');
        });
    }
};

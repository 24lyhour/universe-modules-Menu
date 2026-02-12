<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Only rename if categories exists and menu_categories doesn't
        if (Schema::hasTable('categories') && !Schema::hasTable('menu_categories')) {
            Schema::rename('categories', 'menu_categories');
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('menu_categories') && !Schema::hasTable('categories')) {
            Schema::rename('menu_categories', 'categories');
        }
    }
};

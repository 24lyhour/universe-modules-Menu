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
        // Add soft deletes to menus table
        if (!Schema::hasColumn('menus', 'deleted_at')) {
            Schema::table('menus', function (Blueprint $table) {
                $table->softDeletes();
            });
        }

        // Add soft deletes to menu_categories table
        if (!Schema::hasColumn('menu_categories', 'deleted_at')) {
            Schema::table('menu_categories', function (Blueprint $table) {
                $table->softDeletes();
            });
        }

        // Add soft deletes to menu_types table
        if (!Schema::hasColumn('menu_types', 'deleted_at')) {
            Schema::table('menu_types', function (Blueprint $table) {
                $table->softDeletes();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('menus', function (Blueprint $table) {
            $table->dropSoftDeletes();
        });

        Schema::table('menu_categories', function (Blueprint $table) {
            $table->dropSoftDeletes();
        });

        Schema::table('menu_types', function (Blueprint $table) {
            $table->dropSoftDeletes();
        });
    }
};

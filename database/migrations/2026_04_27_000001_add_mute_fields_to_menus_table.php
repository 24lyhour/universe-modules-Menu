<?php

use App\Traits\HasMutable;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('menus', function (Blueprint $table) {
            HasMutable::addColumns($table, after: 'status');
            $table->index(['outlet_id', 'is_muted']);
        });
    }

    public function down(): void
    {
        Schema::table('menus', function (Blueprint $table) {
            $table->dropIndex(['outlet_id', 'is_muted']);
            $table->dropColumn([
                'is_muted',
                'muted_at',
                'muted_until',
                'muted_reason',
                'muted_by',
            ]);
        });
    }
};

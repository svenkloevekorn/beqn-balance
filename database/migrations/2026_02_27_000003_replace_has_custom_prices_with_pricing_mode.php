<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('customers', function (Blueprint $table) {
            $table->string('pricing_mode')->default('none')->after('discount_percent');
        });

        // Bestehende Daten migrieren
        DB::table('customers')->where('has_custom_prices', true)->update(['pricing_mode' => 'custom_prices']);
        DB::table('customers')->where('has_custom_prices', false)->where('discount_percent', '>', 0)->update(['pricing_mode' => 'percentage']);

        Schema::table('customers', function (Blueprint $table) {
            $table->dropColumn('has_custom_prices');
        });
    }

    public function down(): void
    {
        Schema::table('customers', function (Blueprint $table) {
            $table->boolean('has_custom_prices')->default(false)->after('notes');
        });

        DB::table('customers')->where('pricing_mode', 'custom_prices')->update(['has_custom_prices' => true]);

        Schema::table('customers', function (Blueprint $table) {
            $table->dropColumn('pricing_mode');
        });
    }
};

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Alte Spalten entfernen, neue hinzufuegen
        Schema::table('number_ranges', function (Blueprint $table) {
            $table->dropColumn([
                'prefix',
                'include_year',
                'next_number',
                'digits',
                'reset_yearly',
                'last_reset_year',
            ]);

            $table->string('format')->after('label');
            $table->unsignedInteger('counter_global')->default(0)->after('format');
            $table->unsignedInteger('counter_yearly')->default(0)->after('counter_global');
            $table->unsignedInteger('counter_monthly')->default(0)->after('counter_yearly');
            $table->unsignedInteger('counter_daily')->default(0)->after('counter_monthly');
            $table->unsignedSmallInteger('last_reset_year')->nullable()->after('counter_daily');
            $table->unsignedTinyInteger('last_reset_month')->nullable()->after('last_reset_year');
            $table->unsignedTinyInteger('last_reset_day')->nullable()->after('last_reset_month');
        });

        // Standard-Formate setzen
        DB::table('number_ranges')->where('type', 'invoice')->update(['format' => 'RE-{jjjj}-{jz,4}']);
        DB::table('number_ranges')->where('type', 'quote')->update(['format' => 'AN-{jjjj}-{jz,4}']);
        DB::table('number_ranges')->where('type', 'delivery_note')->update(['format' => 'LS-{jjjj}-{jz,4}']);
        DB::table('number_ranges')->where('type', 'customer')->update(['format' => 'KD-{z,4}']);
        DB::table('number_ranges')->where('type', 'article')->update(['format' => 'ART-{z,4}']);
    }

    public function down(): void
    {
        Schema::table('number_ranges', function (Blueprint $table) {
            $table->dropColumn([
                'format',
                'counter_global',
                'counter_yearly',
                'counter_monthly',
                'counter_daily',
                'last_reset_month',
                'last_reset_day',
            ]);

            // Alte Spalten wiederherstellen
            $table->string('prefix')->after('label');
            $table->boolean('include_year')->default(true)->after('prefix');
            $table->unsignedInteger('next_number')->default(1)->after('include_year');
            $table->unsignedTinyInteger('digits')->default(4)->after('next_number');
            $table->boolean('reset_yearly')->default(true)->after('digits');
        });
    }
};

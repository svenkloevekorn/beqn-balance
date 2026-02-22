<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('number_ranges', function (Blueprint $table) {
            $table->id();
            $table->string('type')->unique();
            $table->string('label');
            $table->string('prefix');
            $table->boolean('include_year')->default(true);
            $table->unsignedInteger('next_number')->default(1);
            $table->unsignedTinyInteger('digits')->default(4);
            $table->boolean('reset_yearly')->default(true);
            $table->unsignedSmallInteger('last_reset_year')->nullable();
            $table->timestamps();
        });

        // Standard-Nummernkreise anlegen
        DB::table('number_ranges')->insert([
            [
                'type' => 'invoice',
                'label' => 'Rechnungen',
                'prefix' => 'RE',
                'include_year' => true,
                'next_number' => 1,
                'digits' => 4,
                'reset_yearly' => true,
                'last_reset_year' => null,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'type' => 'quote',
                'label' => 'Angebote',
                'prefix' => 'AN',
                'include_year' => true,
                'next_number' => 1,
                'digits' => 4,
                'reset_yearly' => true,
                'last_reset_year' => null,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'type' => 'delivery_note',
                'label' => 'Lieferscheine',
                'prefix' => 'LS',
                'include_year' => true,
                'next_number' => 1,
                'digits' => 4,
                'reset_yearly' => true,
                'last_reset_year' => null,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'type' => 'customer',
                'label' => 'Kunden',
                'prefix' => 'KD',
                'include_year' => false,
                'next_number' => 1001,
                'digits' => 0,
                'reset_yearly' => false,
                'last_reset_year' => null,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'type' => 'article',
                'label' => 'Artikel',
                'prefix' => 'ART',
                'include_year' => false,
                'next_number' => 1001,
                'digits' => 0,
                'reset_yearly' => false,
                'last_reset_year' => null,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }

    public function down(): void
    {
        Schema::dropIfExists('number_ranges');
    }
};

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('company_settings', function (Blueprint $table) {
            $table->id();

            // Unternehmen
            $table->string('company_name')->nullable();
            $table->string('legal_form')->nullable();
            $table->string('managing_director')->nullable();

            // Adresse
            $table->string('street')->nullable();
            $table->string('zip')->nullable();
            $table->string('city')->nullable();
            $table->string('country')->default('DE');

            // Kontakt
            $table->string('phone')->nullable();
            $table->string('fax')->nullable();
            $table->string('email')->nullable();
            $table->string('website')->nullable();

            // Steuerdaten
            $table->string('vat_id')->nullable();
            $table->string('tax_number')->nullable();
            $table->string('trade_register')->nullable();

            // Bankverbindung
            $table->string('bank_name')->nullable();
            $table->string('iban')->nullable();
            $table->string('bic')->nullable();

            // Logo
            $table->string('logo_path')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('company_settings');
    }
};

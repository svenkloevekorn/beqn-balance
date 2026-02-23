<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('customers', function (Blueprint $table) {
            $table->string('buyer_reference')->nullable()->after('vat_id');
        });

        Schema::table('invoices', function (Blueprint $table) {
            $table->string('buyer_reference')->nullable()->after('notes');
        });
    }

    public function down(): void
    {
        Schema::table('customers', function (Blueprint $table) {
            $table->dropColumn('buyer_reference');
        });

        Schema::table('invoices', function (Blueprint $table) {
            $table->dropColumn('buyer_reference');
        });
    }
};

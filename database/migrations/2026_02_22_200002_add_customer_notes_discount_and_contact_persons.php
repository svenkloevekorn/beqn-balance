<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('customers', function (Blueprint $table) {
            $table->text('notes')->nullable()->after('payment_term_days');
            $table->decimal('discount_percent', 5, 2)->nullable()->after('payment_term_days');
        });

        Schema::create('contact_persons', function (Blueprint $table) {
            $table->id();
            $table->foreignId('customer_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->string('position')->nullable();
            $table->string('email')->nullable();
            $table->string('phone')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('contact_persons');

        Schema::table('customers', function (Blueprint $table) {
            $table->dropColumn(['notes', 'discount_percent']);
        });
    }
};

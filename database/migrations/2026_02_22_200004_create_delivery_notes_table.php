<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('delivery_notes', function (Blueprint $table) {
            $table->id();
            $table->string('delivery_note_number')->unique();
            $table->foreignId('customer_id')->constrained()->cascadeOnDelete();
            $table->date('delivery_date');
            $table->string('status')->default('draft');
            $table->text('notes')->nullable();
            $table->timestamps();
        });

        Schema::create('delivery_note_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('delivery_note_id')->constrained()->cascadeOnDelete();
            $table->foreignId('article_id')->nullable()->constrained()->nullOnDelete();
            $table->string('description');
            $table->decimal('quantity', 10, 2)->default(1);
            $table->string('unit')->default('Stück');
            $table->decimal('net_price', 10, 2)->default(0);
            $table->decimal('vat_rate', 5, 2)->default(19.00);
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('delivery_note_items');
        Schema::dropIfExists('delivery_notes');
    }
};

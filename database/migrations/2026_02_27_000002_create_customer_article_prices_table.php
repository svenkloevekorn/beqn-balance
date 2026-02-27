<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('customer_article_prices', function (Blueprint $table) {
            $table->id();
            $table->foreignId('customer_id')->constrained()->cascadeOnDelete();
            $table->foreignId('article_id')->constrained()->cascadeOnDelete();
            $table->decimal('custom_net_price', 10, 2)->nullable();
            $table->boolean('is_active')->default(false);
            $table->timestamps();

            $table->unique(['customer_id', 'article_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('customer_article_prices');
    }
};

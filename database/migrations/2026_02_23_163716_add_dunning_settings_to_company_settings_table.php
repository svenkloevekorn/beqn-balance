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
        Schema::table('company_settings', function (Blueprint $table) {
            $table->decimal('dunning_reminder_fee', 10, 2)->default(0)->after('id');
            $table->string('dunning_reminder_subject')->default('Zahlungserinnerung')->after('dunning_reminder_fee');
            $table->text('dunning_reminder_text')->nullable()->after('dunning_reminder_subject');
            $table->integer('dunning_reminder_days')->default(7)->after('dunning_reminder_text');

            $table->decimal('dunning_first_fee', 10, 2)->default(5)->after('dunning_reminder_days');
            $table->string('dunning_first_subject')->default('1. Mahnung')->after('dunning_first_fee');
            $table->text('dunning_first_text')->nullable()->after('dunning_first_subject');
            $table->integer('dunning_first_days')->default(14)->after('dunning_first_text');

            $table->decimal('dunning_second_fee', 10, 2)->default(10)->after('dunning_first_days');
            $table->string('dunning_second_subject')->default('2. Mahnung')->after('dunning_second_fee');
            $table->text('dunning_second_text')->nullable()->after('dunning_second_subject');
            $table->integer('dunning_second_days')->default(14)->after('dunning_second_text');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('company_settings', function (Blueprint $table) {
            $table->dropColumn([
                'dunning_reminder_fee', 'dunning_reminder_subject', 'dunning_reminder_text', 'dunning_reminder_days',
                'dunning_first_fee', 'dunning_first_subject', 'dunning_first_text', 'dunning_first_days',
                'dunning_second_fee', 'dunning_second_subject', 'dunning_second_text', 'dunning_second_days',
            ]);
        });
    }
};

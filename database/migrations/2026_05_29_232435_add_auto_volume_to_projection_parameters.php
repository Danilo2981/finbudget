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
        Schema::table('projection_parameters', function (Blueprint $table) {
            $table->integer('auto_credits_per_exec')->default(15)->after('operating_expenses');
            $table->integer('auto_exec_count')->default(27)->after('auto_credits_per_exec');
            $table->decimal('auto_avg_credit_value', 15, 2)->default(18000.00)->after('auto_exec_count');
            $table->integer('auto_months')->default(12)->after('auto_avg_credit_value');
        });
    }

    public function down(): void
    {
        Schema::table('projection_parameters', function (Blueprint $table) {
            $table->dropColumn(['auto_credits_per_exec', 'auto_exec_count', 'auto_avg_credit_value', 'auto_months']);
        });
    }
};

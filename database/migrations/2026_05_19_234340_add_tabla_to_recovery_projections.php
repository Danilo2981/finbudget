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
        Schema::table('recovery_projections', function (Blueprint $table) {
            // Drop the old unique index
            $table->dropUnique(['row_index', 'mes']);
            
            // Add tabla column with default value for existing rows
            $table->string('tabla')->default('capital')->after('row_index');
            
            // Add the new unique index including tabla
            $table->unique(['tabla', 'row_index', 'mes']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('recovery_projections', function (Blueprint $table) {
            $table->dropUnique(['tabla', 'row_index', 'mes']);
            $table->dropColumn('tabla');
            $table->unique(['row_index', 'mes']);
        });
    }
};

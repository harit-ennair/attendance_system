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
        Schema::table('roles', function (Blueprint $table) {
            // Drop the old name column
            $table->dropColumn('name');
            
            // Add enum column for role type
            $table->enum('type', ['admin', 'super_admin', 'student'])->unique()->after('id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('roles', function (Blueprint $table) {
            // Drop the enum column
            $table->dropColumn('type');
            
            // Restore the original name column
            $table->string('name')->unique()->after('id');
        });
    }
};

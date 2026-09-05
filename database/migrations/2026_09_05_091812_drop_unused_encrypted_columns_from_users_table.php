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
        Schema::table('users', function (Blueprint $table) {
            // Drop the unused encrypted columns
            $table->dropColumn(['encrypted_name', 'encrypted_email']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // Add them back if rolling back
            $table->text('encrypted_name')->nullable()->after('name');
            $table->text('encrypted_email')->nullable()->after('email');
        });
    }
};
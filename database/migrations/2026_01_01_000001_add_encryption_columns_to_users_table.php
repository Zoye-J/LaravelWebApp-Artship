<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('users', function (Blueprint $table) {
            // Encrypted columns (will store encrypted data)
            $table->text('encrypted_name')->nullable()->after('name');
            $table->text('encrypted_email')->nullable()->after('email');
            
            // MAC columns for integrity verification
            $table->string('name_mac')->nullable()->after('encrypted_name');
            $table->string('email_mac')->nullable()->after('encrypted_email');
            
            // Encrypted data version (for future key rotation)
            $table->string('data_version')->default('v1')->after('email_mac');
        });
    }

    public function down()
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn([
                'encrypted_name',
                'encrypted_email',
                'name_mac',
                'email_mac',
                'data_version'
            ]);
        });
    }
};
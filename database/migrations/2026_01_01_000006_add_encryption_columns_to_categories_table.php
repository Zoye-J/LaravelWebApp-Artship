<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('categories', function (Blueprint $table) {
            // Encrypted columns
            $table->text('encrypted_name')->nullable()->after('name');
            
            // MAC columns
            $table->string('name_mac')->nullable()->after('encrypted_name');
            
            // Data version
            $table->string('data_version')->default('v1')->after('name_mac');
        });
    }

    public function down()
    {
        Schema::table('categories', function (Blueprint $table) {
            $table->dropColumn([
                'encrypted_name',
                'name_mac',
                'data_version'
            ]);
        });
    }
};
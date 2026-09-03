<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('course_materials', function (Blueprint $table) {
            // Encrypted columns
            $table->text('encrypted_title')->nullable()->after('title');
            $table->text('encrypted_file_path')->nullable()->after('file_path');
            
            // MAC columns
            $table->string('title_mac')->nullable()->after('encrypted_title');
            $table->string('file_path_mac')->nullable()->after('encrypted_file_path');
            
            // Data version
            $table->string('data_version')->default('v1')->after('file_path_mac');
        });
    }

    public function down()
    {
        Schema::table('course_materials', function (Blueprint $table) {
            $table->dropColumn([
                'encrypted_title',
                'encrypted_file_path',
                'title_mac',
                'file_path_mac',
                'data_version'
            ]);
        });
    }
};
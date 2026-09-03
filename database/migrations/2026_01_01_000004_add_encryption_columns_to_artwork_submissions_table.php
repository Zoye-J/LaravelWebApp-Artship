<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('artwork_submissions', function (Blueprint $table) {
            // Encrypted columns
            $table->text('encrypted_title')->nullable()->after('title');
            $table->text('encrypted_description')->nullable()->after('description');
            $table->text('encrypted_image_path')->nullable()->after('image_path');
            
            // MAC columns
            $table->string('title_mac')->nullable()->after('encrypted_title');
            $table->string('description_mac')->nullable()->after('encrypted_description');
            $table->string('image_path_mac')->nullable()->after('encrypted_image_path');
            
            // Data version
            $table->string('data_version')->default('v1')->after('image_path_mac');
        });
    }

    public function down()
    {
        Schema::table('artwork_submissions', function (Blueprint $table) {
            $table->dropColumn([
                'encrypted_title',
                'encrypted_description',
                'encrypted_image_path',
                'title_mac',
                'description_mac',
                'image_path_mac',
                'data_version'
            ]);
        });
    }
};
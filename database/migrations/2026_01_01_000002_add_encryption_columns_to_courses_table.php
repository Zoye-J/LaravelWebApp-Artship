<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('courses', function (Blueprint $table) {
            // Encrypted columns
            $table->text('encrypted_title')->nullable()->after('title');
            $table->text('encrypted_description')->nullable()->after('description');
            $table->text('encrypted_category')->nullable()->after('category');
            
            // MAC columns
            $table->string('title_mac')->nullable()->after('encrypted_title');
            $table->string('description_mac')->nullable()->after('encrypted_description');
            $table->string('category_mac')->nullable()->after('encrypted_category');
            
            // Data version
            $table->string('data_version')->default('v1')->after('category_mac');
        });
    }

    public function down()
    {
        Schema::table('courses', function (Blueprint $table) {
            $table->dropColumn([
                'encrypted_title',
                'encrypted_description',
                'encrypted_category',
                'title_mac',
                'description_mac',
                'category_mac',
                'data_version'
            ]);
        });
    }
};
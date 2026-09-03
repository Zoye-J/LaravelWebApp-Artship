<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('course_ratings', function (Blueprint $table) {
            // Encrypted columns
            $table->text('encrypted_review')->nullable()->after('review');
            
            // MAC columns
            $table->string('review_mac')->nullable()->after('encrypted_review');
            
            // Data version
            $table->string('data_version')->default('v1')->after('review_mac');
        });
    }

    public function down()
    {
        Schema::table('course_ratings', function (Blueprint $table) {
            $table->dropColumn([
                'encrypted_review',
                'review_mac',
                'data_version'
            ]);
        });
    }
};
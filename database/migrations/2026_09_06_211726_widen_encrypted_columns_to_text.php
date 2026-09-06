<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->text('name')->change();
            $table->text('email')->change();
        });
        Schema::table('courses', function (Blueprint $table) {
            $table->text('title')->change();
            $table->text('description')->change();
            $table->text('category')->change();
        });
        Schema::table('course_materials', function (Blueprint $table) {
            $table->text('title')->change();
            $table->text('file_path')->change();
        });
        Schema::table('artwork_submissions', function (Blueprint $table) {
            $table->text('title')->change();
            $table->text('description')->change();
            $table->text('image_path')->change();
        });
        Schema::table('course_ratings', function (Blueprint $table) {
            $table->text('review')->change();
        });
        Schema::table('categories', function (Blueprint $table) {
            $table->text('name')->change();
        });
    }

    public function down(): void
    {
        // Intentionally left blank — reverting to VARCHAR(255) isn't
        // safely reversible once ciphertext has been stored.
    }
};
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('two_factor_auths', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->text('secret')->nullable(); // Can be null initially
            $table->boolean('enabled')->default(false);
            $table->text('backup_codes')->nullable();
            $table->timestamp('last_verified_at')->nullable();
            $table->timestamp('first_verified_at')->nullable(); // Track first verification
            $table->timestamps();
            
            $table->unique('user_id');
            $table->index('enabled');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('two_factor_auths');
    }
};
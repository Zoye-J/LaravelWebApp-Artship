<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('keys', function (Blueprint $table) {
            $table->id();

            $table->enum('algorithm', ['rsa', 'ecc']);
            $table->string('purpose'); 
            $table->unsignedInteger('key_size')->nullable(); 
            $table->longText('public_key');  
            $table->longText('private_key'); 

            $table->string('fingerprint', 64); 
            $table->unsignedInteger('version')->default(1);
            $table->enum('status', ['active', 'rotated', 'revoked'])->default('active');

            $table->foreignId('rotated_from_id')->nullable()->constrained('keys')->nullOnDelete();
            $table->foreignId('generated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('rotated_at')->nullable();

            $table->timestamps();

            $table->index(['algorithm', 'purpose', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('keys');
    }
};

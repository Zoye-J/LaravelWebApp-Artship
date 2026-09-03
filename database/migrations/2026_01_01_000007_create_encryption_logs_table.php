<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('encryption_logs', function (Blueprint $table) {
            $table->id();
            $table->string('model_type');
            $table->unsignedBigInteger('model_id');
            $table->string('operation'); // 'encrypt', 'decrypt', 'integrity_check'
            $table->string('status'); // 'success', 'failed', 'tampered'
            $table->text('message')->nullable();
            $table->string('ip_address')->nullable();
            $table->unsignedBigInteger('user_id')->nullable();
            $table->timestamps();
            
            $table->index(['model_type', 'model_id']);
            $table->index('status');
        });
    }

    public function down()
    {
        Schema::dropIfExists('encryption_logs');
    }
};
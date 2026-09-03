<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
   public function up()
    {
        Schema::table('course_materials', function (Blueprint $table) {
            $table->foreignId('uploaded_by')->constrained('users')->onDelete('cascade');
        });
    }

    public function down()
    {
        Schema::table('course_materials', function (Blueprint $table) {
            $table->dropForeign(['uploaded_by']);
            $table->dropColumn('uploaded_by');
        });
    }
};

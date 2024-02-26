<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('courses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            
            $table->string('image');
            $table->string('course_name');
            $table->string('teacher_name');
            $table->float('cost');
            $table->integer('total_student');
            $table->integer('curr_student')->default(0);
            $table->integer('total_hours');
            $table->string('description');

            $table->integer('super_student')->nullable();
            
            $table->integer('state')->default(0);
            
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('courses');
    }
};

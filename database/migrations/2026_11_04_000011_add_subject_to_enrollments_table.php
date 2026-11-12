<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('enrollments', function (Blueprint $table) {
            $table->index('student_id');
            $table->dropUnique(['student_id', 'course_id']);
            $table->foreignId('subject_id')->nullable()->constrained()->nullOnDelete();
            $table->unique(['student_id', 'course_id', 'subject_id']);
        });
    }

    public function down(): void
    {
        Schema::table('enrollments', function (Blueprint $table) {
            $table->dropUnique(['student_id', 'course_id', 'subject_id']);
            $table->dropConstrainedForeignId('subject_id');
            $table->dropIndex(['student_id']);
            $table->unique(['student_id', 'course_id']);
        });
    }
};

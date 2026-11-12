<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('courses', function (Blueprint $table) {
            $table->string('status')->default('active');
        });

        Schema::table('year_levels', function (Blueprint $table) {
            $table->string('status')->default('active');
        });

        Schema::table('subjects', function (Blueprint $table) {
            $table->foreignId('course_id')->nullable()->constrained()->restrictOnDelete();
            $table->foreignId('year_level_id')->nullable()->constrained()->restrictOnDelete();
            $table->string('status')->default('active');
            $table->index(['course_id', 'year_level_id', 'status']);
        });

        Schema::create('student_enrollments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('student_id')->constrained()->restrictOnDelete();
            $table->foreignId('course_id')->constrained()->restrictOnDelete();
            $table->foreignId('year_level_id')->nullable()->constrained()->restrictOnDelete();
            $table->string('school_year', 9);
            $table->date('enrollment_date');
            $table->string('status')->default('pending');
            $table->foreignId('approved_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('approved_at')->nullable();
            $table->timestamps();
            $table->unique(
                ['student_id', 'course_id', 'year_level_id', 'school_year'],
                'student_enrollments_period_unique'
            );
            $table->index(['school_year', 'status']);
        });

        Schema::create('subject_enrollments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('student_enrollment_id')->constrained()->restrictOnDelete();
            $table->foreignId('subject_id')->constrained()->restrictOnDelete();
            $table->string('status')->default('pending');
            $table->timestamps();
            $table->unique(['student_enrollment_id', 'subject_id']);
            $table->index(['subject_id', 'status']);
        });

        DB::table('enrollments')
            ->orderBy('id')
            ->chunkById(100, function ($legacyEnrollments): void {
                foreach ($legacyEnrollments as $legacyEnrollment) {
                    $yearLevelId = $legacyEnrollment->year_level_id
                        ?? DB::table('students')
                            ->where('id', $legacyEnrollment->student_id)
                            ->value('year_level_id');
                    $schoolYear = Carbon::parse($legacyEnrollment->enrolled_at)->format('Y');

                    $studentEnrollment = DB::table('student_enrollments')
                        ->where('student_id', $legacyEnrollment->student_id)
                        ->where('course_id', $legacyEnrollment->course_id)
                        ->where('school_year', $schoolYear)
                        ->when(
                            $yearLevelId === null,
                            fn ($query) => $query->whereNull('year_level_id'),
                            fn ($query) => $query->where('year_level_id', $yearLevelId)
                        )
                        ->first();

                    if ($studentEnrollment === null) {
                        $studentEnrollmentId = DB::table('student_enrollments')->insertGetId([
                            'student_id' => $legacyEnrollment->student_id,
                            'course_id' => $legacyEnrollment->course_id,
                            'year_level_id' => $yearLevelId,
                            'school_year' => $schoolYear,
                            'enrollment_date' => $legacyEnrollment->enrolled_at,
                            'status' => $legacyEnrollment->status,
                            'approved_by' => $legacyEnrollment->approved_by,
                            'approved_at' => $legacyEnrollment->approved_at,
                            'created_at' => $legacyEnrollment->created_at,
                            'updated_at' => $legacyEnrollment->updated_at,
                        ]);
                    } else {
                        $studentEnrollmentId = $studentEnrollment->id;
                    }

                    if ($legacyEnrollment->subject_id !== null) {
                        DB::table('subject_enrollments')->insertOrIgnore([
                            'student_enrollment_id' => $studentEnrollmentId,
                            'subject_id' => $legacyEnrollment->subject_id,
                            'status' => $legacyEnrollment->status,
                            'created_at' => $legacyEnrollment->created_at,
                            'updated_at' => $legacyEnrollment->updated_at,
                        ]);
                    }
                }
            });
    }

    public function down(): void
    {
        Schema::dropIfExists('subject_enrollments');
        Schema::dropIfExists('student_enrollments');

        Schema::table('subjects', function (Blueprint $table) {
            $table->dropIndex(['course_id', 'year_level_id', 'status']);
            $table->dropConstrainedForeignId('course_id');
            $table->dropConstrainedForeignId('year_level_id');
            $table->dropColumn('status');
        });

        Schema::table('year_levels', function (Blueprint $table) {
            $table->dropColumn('status');
        });

        Schema::table('courses', function (Blueprint $table) {
            $table->dropColumn('status');
        });
    }
};

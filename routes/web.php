<?php

use App\Http\Controllers\CourseController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\EnrollmentController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\StudentController;
use App\Http\Controllers\StudentPortalController;
use App\Http\Controllers\StudentProfileController;
use App\Http\Controllers\SubjectController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\YearLevelController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    if (auth()->check()) {
        return redirect()->route(auth()->user()->isStudent() ? 'student.dashboard' : 'dashboard');
    }

    return redirect()->route('login');
});

Route::get('/dashboard', [DashboardController::class, 'index'])
    ->middleware(['auth', 'verified', 'role:admin,staff'])
    ->name('dashboard');

Route::get('/student/dashboard', [StudentPortalController::class, 'index'])
    ->middleware(['auth', 'role:student'])
    ->name('student.dashboard');

Route::middleware(['auth', 'role:student'])->group(function () {
    Route::get('/student/profile', [StudentProfileController::class, 'edit'])->name('student.profile.edit');
    Route::post('/student/profile', [StudentProfileController::class, 'store'])->name('student.profile.store');
    Route::patch('/student/profile', [StudentProfileController::class, 'update'])->name('student.profile.update');
    Route::get('/student/enrollment-subjects', [StudentPortalController::class, 'availableSubjects'])->name('student.enrollment-subjects');
    Route::post('/student/enrollments', [StudentPortalController::class, 'storeEnrollment'])->name('student.enrollments.store');
    Route::delete('/student/enrollments/{enrollment}', [StudentPortalController::class, 'destroyEnrollment'])->name('student.enrollments.destroy');
});

Route::middleware(['auth', 'role:admin,staff'])->group(function () {
    Route::resource('courses', CourseController::class)->except(['show']);
    Route::resource('enrollments', EnrollmentController::class);
    Route::patch('/enrollments/{enrollment}/approve', [EnrollmentController::class, 'approve'])->name('enrollments.approve');
    Route::resource('students', StudentController::class)->except(['show']);
    Route::resource('subjects', SubjectController::class);
    Route::resource('year-levels', YearLevelController::class)->except(['show']);

    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

Route::middleware(['auth', 'role:admin'])->group(function () {
    Route::resource('users', UserController::class)->only(['index', 'update', 'destroy']);
});

require __DIR__.'/auth.php';

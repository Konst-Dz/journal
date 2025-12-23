<?php

use App\Http\Controllers\AnalyticsController;
use App\Http\Controllers\AttendanceController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\CourseController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\EmployeeProfileController;
use App\Http\Controllers\GradeController;
use App\Http\Controllers\LessonController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;

// Guest routes
Route::middleware('guest')->group(function () {
    Route::get('login', [LoginController::class, 'create'])->name('login');
    Route::post('login', [LoginController::class, 'store']);
    Route::get('register', [RegisterController::class, 'create'])->name('register');
    Route::post('register', [RegisterController::class, 'store']);
});

// Auth routes
Route::middleware('auth')->group(function () {
    Route::post('logout', [LoginController::class, 'destroy'])->name('logout');
    Route::get('/', fn () => redirect('/dashboard'));
    Route::get('dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // Employee Profile
    Route::get('profile', [EmployeeProfileController::class, 'index'])->name('profile.index');

    // Courses
    Route::resource('courses', CourseController::class);
    Route::post('courses/{course}/enroll', [CourseController::class, 'enroll'])->name('courses.enroll');
    Route::delete('courses/{course}/students/{user}', [CourseController::class, 'unenroll'])->name('courses.unenroll');

    // Lessons
    Route::get('courses/{course}/lessons', [LessonController::class, 'index'])->name('courses.lessons.index');
    Route::get('courses/{course}/lessons/create', [LessonController::class, 'create'])->name('courses.lessons.create');
    Route::post('courses/{course}/lessons', [LessonController::class, 'store'])->name('courses.lessons.store');
    Route::get('courses/{course}/lessons/{lesson}/edit', [LessonController::class, 'edit'])->name('courses.lessons.edit');
    Route::put('courses/{course}/lessons/{lesson}', [LessonController::class, 'update'])->name('courses.lessons.update');
    Route::delete('courses/{course}/lessons/{lesson}', [LessonController::class, 'destroy'])->name('courses.lessons.destroy');

    // Attendance
    Route::get('courses/{course}/lessons/{lesson}/attendance', [AttendanceController::class, 'mark'])->name('attendance.mark');
    Route::post('courses/{course}/lessons/{lesson}/attendance', [AttendanceController::class, 'store'])->name('attendance.store');
    Route::get('courses/{course}/attendance/report', [AttendanceController::class, 'report'])->name('attendance.report');

    // Grades
    Route::get('courses/{course}/grades', [GradeController::class, 'index'])->name('grades.index');
    Route::post('courses/{course}/grades', [GradeController::class, 'store'])->name('grades.store');
    Route::put('courses/{course}/grades/{grade}', [GradeController::class, 'update'])->name('grades.update');
    Route::delete('courses/{course}/grades/{grade}', [GradeController::class, 'destroy'])->name('grades.destroy');

    // Analytics
    Route::get('analytics', [AnalyticsController::class, 'index'])->name('analytics.index');
    Route::get('courses/{course}/analytics', [AnalyticsController::class, 'courseReport'])->name('analytics.course');
    Route::get('courses/{course}/analytics/export', [AnalyticsController::class, 'exportCourse'])->name('analytics.course.export');
    Route::get('employees/{employee}/analytics', [AnalyticsController::class, 'employeeReport'])->name('analytics.employee');

    // Admin only
    Route::middleware('role:admin')->group(function () {
        Route::resource('users', UserController::class)->except(['show']);
    });
});

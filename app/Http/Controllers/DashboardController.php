<?php

namespace App\Http\Controllers;

use App\Models\Attendance;
use App\Models\Course;
use App\Models\Lesson;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class DashboardController extends Controller
{
    public function index()
    {
        $user = auth()->user();
        $now = Carbon::now();
        $weekStart = $now->copy()->startOfWeek();
        $weekEnd = $now->copy()->endOfWeek();

        if ($user->isAdmin()) {
            $activeCourses = Course::where('status', 'active')->count();
            $lessonsThisWeek = Lesson::whereBetween('scheduled_at', [$weekStart, $weekEnd])->count();
            $studentsInTraining = User::where('role', 'employee')
                ->whereHas('courses', fn ($q) => $q->where('status', 'active'))
                ->count();
        } elseif ($user->isTeacher()) {
            $activeCourses = Course::where('teacher_id', $user->id)
                ->where('status', 'active')
                ->count();
            $lessonsThisWeek = Lesson::whereHas('course', fn ($q) => $q->where('teacher_id', $user->id))
                ->whereBetween('scheduled_at', [$weekStart, $weekEnd])
                ->count();
            $studentsInTraining = User::where('role', 'employee')
                ->whereHas('courses', fn ($q) => $q->where('teacher_id', $user->id)->where('status', 'active'))
                ->count();
        } else {
            $activeCourses = Course::whereHas('students', fn ($q) => $q->where('users.id', $user->id))
                ->where('status', 'active')
                ->count();
            $lessonsThisWeek = Lesson::whereHas('course.students', fn ($q) => $q->where('users.id', $user->id))
                ->whereBetween('scheduled_at', [$weekStart, $weekEnd])
                ->count();
            $studentsInTraining = 0;
        }

        // Ближайшие занятия
        $upcomingLessonsQuery = Lesson::with(['course.teacher'])
            ->where('scheduled_at', '>=', $now)
            ->orderBy('scheduled_at');

        if ($user->isAdmin()) {
            $upcomingLessons = $upcomingLessonsQuery->take(5)->get();
        } elseif ($user->isTeacher()) {
            $upcomingLessons = $upcomingLessonsQuery
                ->whereHas('course', fn ($q) => $q->where('teacher_id', $user->id))
                ->take(5)
                ->get();
        } else {
            $upcomingLessons = $upcomingLessonsQuery
                ->whereHas('course.students', fn ($q) => $q->where('users.id', $user->id))
                ->take(5)
                ->get();
        }

        // Статистика посещаемости
        if ($user->isAdmin()) {
            $totalAttendances = Attendance::count();
            $presentAttendances = Attendance::where('status', 'present')->count();
        } elseif ($user->isTeacher()) {
            $totalAttendances = Attendance::whereHas('lesson.course', fn ($q) => $q->where('teacher_id', $user->id))->count();
            $presentAttendances = Attendance::whereHas('lesson.course', fn ($q) => $q->where('teacher_id', $user->id))
                ->where('status', 'present')
                ->count();
        } else {
            $totalAttendances = Attendance::where('user_id', $user->id)->count();
            $presentAttendances = Attendance::where('user_id', $user->id)
                ->where('status', 'present')
                ->count();
        }

        $attendanceStats = [
            'total' => $totalAttendances,
            'present' => $presentAttendances,
            'percentage' => $totalAttendances > 0 ? round(($presentAttendances / $totalAttendances) * 100, 1) : 0,
        ];

        // Данные календаря (текущий месяц)
        $monthStart = $now->copy()->startOfMonth();
        $monthEnd = $now->copy()->endOfMonth();

        $calendarLessonsQuery = Lesson::with(['course'])
            ->whereBetween('scheduled_at', [$monthStart, $monthEnd]);

        if ($user->isAdmin()) {
            $calendarLessons = $calendarLessonsQuery->get();
        } elseif ($user->isTeacher()) {
            $calendarLessons = $calendarLessonsQuery
                ->whereHas('course', fn ($q) => $q->where('teacher_id', $user->id))
                ->get();
        } else {
            $calendarLessons = $calendarLessonsQuery
                ->whereHas('course.students', fn ($q) => $q->where('users.id', $user->id))
                ->get();
        }

        $calendarData = [];
        foreach ($calendarLessons as $lesson) {
            $date = $lesson->scheduled_at->format('Y-m-d');
            if (!isset($calendarData[$date])) {
                $calendarData[$date] = [
                    'count' => 0,
                    'lessons' => [],
                ];
            }
            $calendarData[$date]['count']++;
            $calendarData[$date]['lessons'][] = [
                'id' => $lesson->id,
                'title' => $lesson->title,
                'time' => $lesson->scheduled_at->format('H:i'),
                'course_name' => $lesson->course->name,
                'type' => $lesson->type,
            ];
        }

        // Топ-5 списки
        $topLists = [];

        if ($user->isAdmin() || $user->isTeacher()) {
            // Самые активные сотрудники (по посещаемости)
            $topStudentsQuery = User::where('role', 'employee')
                ->withCount([
                    'courses as total_attendances' => function ($q) use ($user) {
                        $q->join('lessons', 'courses.id', '=', 'lessons.course_id')
                            ->join('attendances', 'lessons.id', '=', 'attendances.lesson_id')
                            ->where('attendances.user_id', '=', DB::raw('users.id'));
                        
                        if ($user->isTeacher()) {
                            $q->where('courses.teacher_id', $user->id);
                        }
                    },
                    'courses as present_attendances' => function ($q) use ($user) {
                        $q->join('lessons', 'courses.id', '=', 'lessons.course_id')
                            ->join('attendances', 'lessons.id', '=', 'attendances.lesson_id')
                            ->where('attendances.user_id', '=', DB::raw('users.id'))
                            ->where('attendances.status', 'present');
                        
                        if ($user->isTeacher()) {
                            $q->where('courses.teacher_id', $user->id);
                        }
                    },
                ])
                ->having('total_attendances', '>', 0)
                ->orderBy('present_attendances', 'desc')
                ->take(5)
                ->get()
                ->map(function ($student) {
                    $percentage = $student->total_attendances > 0 
                        ? round(($student->present_attendances / $student->total_attendances) * 100, 1) 
                        : 0;
                    
                    return [
                        'id' => $student->id,
                        'name' => $student->name,
                        'attendance_count' => $student->present_attendances,
                        'attendance_percentage' => $percentage,
                    ];
                });

            // Самые популярные курсы (по количеству студентов)
            $topCoursesQuery = Course::withCount('students');
            
            if ($user->isTeacher()) {
                $topCoursesQuery->where('teacher_id', $user->id);
            }
            
            $topCourses = $topCoursesQuery
                ->orderBy('students_count', 'desc')
                ->take(5)
                ->get()
                ->map(function ($course) {
                    return [
                        'id' => $course->id,
                        'name' => $course->name,
                        'students_count' => $course->students_count,
                        'status' => $course->status,
                    ];
                });

            $topLists['top_students'] = $topStudentsQuery;
            $topLists['top_courses'] = $topCourses;

            // Преподаватели с наибольшей нагрузкой (только для админа)
            if ($user->isAdmin()) {
                $topTeachers = User::where('role', 'teacher')
                    ->withCount([
                        'taughtCourses as courses_count',
                        'taughtCourses as students_count' => function ($q) {
                            $q->join('course_user', 'courses.id', '=', 'course_user.course_id')
                                ->select(DB::raw('COUNT(DISTINCT course_user.user_id)'));
                        },
                    ])
                    ->having('courses_count', '>', 0)
                    ->orderBy('students_count', 'desc')
                    ->take(5)
                    ->get()
                    ->map(function ($teacher) {
                        return [
                            'id' => $teacher->id,
                            'name' => $teacher->name,
                            'courses_count' => $teacher->courses_count,
                            'students_count' => $teacher->students_count,
                        ];
                    });

                $topLists['top_teachers'] = $topTeachers;
            }
        }

        return Inertia::render('Dashboard', [
            'metrics' => [
                'active_courses' => $activeCourses,
                'lessons_this_week' => $lessonsThisWeek,
                'students_in_training' => $studentsInTraining,
            ],
            'upcoming_lessons' => $upcomingLessons,
            'attendance_stats' => $attendanceStats,
            'calendar_data' => $calendarData,
            'top_lists' => $topLists,
        ]);
    }
}



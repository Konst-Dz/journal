<?php

namespace App\Http\Controllers;

use App\Models\Attendance;
use App\Models\Course;
use App\Models\Grade;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Response;
use Inertia\Inertia;

class AnalyticsController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();

        if ($user->role === 'admin') {
            $courses = Course::with('teacher')->get();
            $students = User::where('role', 'employee')->get();
        } else {
            $courses = Course::where('teacher_id', $user->id)->with('teacher')->get();
            $studentIds = $courses->pluck('id')->flatMap(function ($courseId) {
                return Course::find($courseId)->students->pluck('id');
            })->unique();
            $students = User::whereIn('id', $studentIds)->get();
        }

        // Статистика посещений
        $attendanceStats = [
            'total' => Attendance::count(),
            'present' => Attendance::where('status', 'present')->count(),
            'absent' => Attendance::where('status', 'absent')->count(),
            'late' => Attendance::where('status', 'late')->count(),
            'excused' => Attendance::where('status', 'excused')->count(),
        ];

        // Статистика оценок
        $gradesStats = [
            'total' => Grade::count(),
            'passed' => Grade::where('passed', true)->count(),
            'failed' => Grade::where('passed', false)->count(),
        ];

        // Рейтинг пользователей (по посещаемости)
        $userRatings = $students->map(function ($student) {
            $attendances = Attendance::where('user_id', $student->id)->get();
            $total = $attendances->count();
            $present = $attendances->whereIn('status', ['present', 'excused'])->count();
            $rate = $total > 0 ? round($present / $total * 100, 1) : 0;

            return [
                'id' => $student->id,
                'name' => $student->name,
                'email' => $student->email,
                'attendance_rate' => $rate,
                'total_lessons' => $total,
            ];
        })->sortByDesc('attendance_rate')->values();

        // Рейтинг преподавателей
        $teachers = User::where('role', 'teacher')->get();
        $teacherRatings = $teachers->map(function ($teacher) {
            $courses = Course::where('teacher_id', $teacher->id)->get();
            $totalStudents = $courses->sum(fn ($c) => $c->students->count());
            $avgAttendance = 0;

            if ($totalStudents > 0) {
                $totalRate = 0;
                $count = 0;
                foreach ($courses as $course) {
                    foreach ($course->students as $student) {
                        $attendances = Attendance::where('user_id', $student->id)
                            ->whereIn('lesson_id', $course->lessons->pluck('id'))
                            ->get();
                        $total = $attendances->count();
                        if ($total > 0) {
                            $present = $attendances->whereIn('status', ['present', 'excused'])->count();
                            $totalRate += ($present / $total * 100);
                            $count++;
                        }
                    }
                }
                $avgAttendance = $count > 0 ? round($totalRate / $count, 1) : 0;
            }

            return [
                'id' => $teacher->id,
                'name' => $teacher->name,
                'email' => $teacher->email,
                'courses_count' => $courses->count(),
                'students_count' => $totalStudents,
                'avg_attendance' => $avgAttendance,
            ];
        })->sortByDesc('avg_attendance')->values();

        // Рейтинг курсов
        $courseRatings = $courses->map(function ($course) {
            $students = $course->students;
            $lessons = $course->lessons;
            $totalAttendances = 0;
            $presentCount = 0;

            foreach ($students as $student) {
                $attendances = Attendance::where('user_id', $student->id)
                    ->whereIn('lesson_id', $lessons->pluck('id'))
                    ->get();
                $totalAttendances += $attendances->count();
                $presentCount += $attendances->whereIn('status', ['present', 'excused'])->count();
            }

            $attendanceRate = $totalAttendances > 0 ? round($presentCount / $totalAttendances * 100, 1) : 0;

            return [
                'id' => $course->id,
                'name' => $course->name,
                'teacher' => $course->teacher->name,
                'students_count' => $students->count(),
                'lessons_count' => $lessons->count(),
                'attendance_rate' => $attendanceRate,
            ];
        })->sortByDesc('attendance_rate')->values();

        // Рейтинг занятий
        $lessonRatings = collect();
        foreach ($courses as $course) {
            foreach ($course->lessons as $lesson) {
                $attendances = Attendance::where('lesson_id', $lesson->id)->get();
                $total = $attendances->count();
                $present = $attendances->whereIn('status', ['present', 'excused'])->count();
                $rate = $total > 0 ? round($present / $total * 100, 1) : 0;

                $lessonRatings->push([
                    'id' => $lesson->id,
                    'title' => $lesson->title,
                    'course' => $course->name,
                    'scheduled_at' => $lesson->scheduled_at->format('d.m.Y H:i'),
                    'attendance_rate' => $rate,
                    'students_count' => $total,
                ]);
            }
        }
        $lessonRatings = $lessonRatings->sortByDesc('attendance_rate')->values();

        return Inertia::render('Analytics/Index', [
            'attendanceStats' => $attendanceStats,
            'gradesStats' => $gradesStats,
            'userRatings' => $userRatings,
            'teacherRatings' => $user->role === 'admin' ? $teacherRatings : [],
            'courseRatings' => $courseRatings,
            'lessonRatings' => $lessonRatings,
            'isAdmin' => $user->role === 'admin',
        ]);
    }

    public function courseReport(Course $course)
    {
        $this->authorize('view', $course);

        $students = $course->students;
        $lessons = $course->lessons()->orderBy('scheduled_at')->get();

        $report = $students->map(function ($student) use ($lessons, $course) {
            $attendances = Attendance::whereIn('lesson_id', $lessons->pluck('id'))
                ->where('user_id', $student->id)
                ->get()
                ->keyBy('lesson_id');

            $stats = [
                'present' => 0,
                'absent' => 0,
                'late' => 0,
                'excused' => 0,
            ];

            $lessons->each(function ($lesson) use ($attendances, &$stats) {
                $att = $attendances->get($lesson->id);
                if ($att) {
                    $stats[$att->status]++;
                } else {
                    $stats['absent']++;
                }
            });

            $totalLessons = $lessons->count();
            $attendanceRate = $totalLessons > 0
                ? round(($stats['present'] + $stats['excused']) / $totalLessons * 100, 1)
                : 0;

            $grades = Grade::where('course_id', $course->id)
                ->where('user_id', $student->id)
                ->whereNotNull('lesson_id')
                ->get();

            $passedCount = $grades->where('passed', true)->count();
            $totalGrades = $grades->count();
            $averageGrade = $totalGrades > 0
                ? round($passedCount / $totalGrades * 100, 1)
                : 0;

            $finalGrade = Grade::where('course_id', $course->id)
                ->where('user_id', $student->id)
                ->whereNull('lesson_id')
                ->first();

            return [
                'id' => $student->id,
                'name' => $student->name,
                'email' => $student->email,
                'attendance' => $stats,
                'attendance_rate' => $attendanceRate,
                'grades' => [
                    'passed' => $passedCount,
                    'total' => $totalGrades,
                    'rate' => $averageGrade,
                ],
                'final_grade' => $finalGrade ? ($finalGrade->passed ? 'Зачёт' : 'Незачёт') : null,
            ];
        });

        return Inertia::render('Analytics/CourseReport', [
            'course' => $course->only('id', 'name', 'start_date', 'end_date'),
            'lessons' => $lessons->map(fn ($l) => $l->only('id', 'title', 'scheduled_at')),
            'report' => $report,
        ]);
    }

    public function employeeReport(User $employee)
    {
        $courses = Course::whereHas('students', fn ($q) => $q->where('users.id', $employee->id))
            ->with(['teacher', 'lessons'])
            ->get();

        $report = $courses->map(function ($course) use ($employee) {
            $lessons = $course->lessons;
            $attendances = Attendance::whereIn('lesson_id', $lessons->pluck('id'))
                ->where('user_id', $employee->id)
                ->get()
                ->keyBy('lesson_id');

            $stats = [
                'present' => 0,
                'absent' => 0,
                'late' => 0,
                'excused' => 0,
            ];

            $lessons->each(function ($lesson) use ($attendances, &$stats) {
                $att = $attendances->get($lesson->id);
                if ($att) {
                    $stats[$att->status]++;
                } else {
                    $stats['absent']++;
                }
            });

            $totalLessons = $lessons->count();
            $attendanceRate = $totalLessons > 0
                ? round(($stats['present'] + $stats['excused']) / $totalLessons * 100, 1)
                : 0;

            $grades = Grade::where('course_id', $course->id)
                ->where('user_id', $employee->id)
                ->whereNotNull('lesson_id')
                ->get();

            $passedCount = $grades->where('passed', true)->count();
            $totalGrades = $grades->count();

            $finalGrade = Grade::where('course_id', $course->id)
                ->where('user_id', $employee->id)
                ->whereNull('lesson_id')
                ->first();

            return [
                'id' => $course->id,
                'name' => $course->name,
                'teacher' => $course->teacher->name,
                'status' => $course->status,
                'start_date' => $course->start_date->format('Y-m-d'),
                'end_date' => $course->end_date?->format('Y-m-d'),
                'attendance' => $stats,
                'attendance_rate' => $attendanceRate,
                'grades' => [
                    'passed' => $passedCount,
                    'total' => $totalGrades,
                ],
                'final_grade' => $finalGrade ? ($finalGrade->passed ? 'Зачёт' : 'Незачёт') : null,
            ];
        });

        return Inertia::render('Analytics/EmployeeReport', [
            'employee' => $employee->only('id', 'name', 'email'),
            'report' => $report,
        ]);
    }

    public function exportCourse(Course $course, Request $request)
    {
        $this->authorize('view', $course);

        $format = $request->get('format', 'csv');

        $students = $course->students;
        $lessons = $course->lessons()->orderBy('scheduled_at')->get();

        $headers = ['Студент', 'Email'];
        foreach ($lessons as $lesson) {
            $headers[] = $lesson->title . ' (' . $lesson->scheduled_at->format('d.m.Y') . ')';
        }
        $headers[] = 'Посещаемость %';
        $headers[] = 'Зачётов';
        $headers[] = 'Итоговая оценка';

        $rows = [];
        foreach ($students as $student) {
            $row = [$student->name, $student->email];

            $attendances = Attendance::whereIn('lesson_id', $lessons->pluck('id'))
                ->where('user_id', $student->id)
                ->get()
                ->keyBy('lesson_id');

            $presentCount = 0;
            foreach ($lessons as $lesson) {
                $att = $attendances->get($lesson->id);
                if ($att) {
                    $status = match ($att->status) {
                        'present' => '✓',
                        'late' => '~',
                        'excused' => 'О',
                        default => '✗',
                    };
                    $row[] = $status;
                    if ($att->status === 'present' || $att->status === 'excused') {
                        $presentCount++;
                    }
                } else {
                    $row[] = '✗';
                }
            }

            $attendanceRate = $lessons->count() > 0
                ? round($presentCount / $lessons->count() * 100, 1)
                : 0;
            $row[] = $attendanceRate . '%';

            $grades = Grade::where('course_id', $course->id)
                ->where('user_id', $student->id)
                ->whereNotNull('lesson_id')
                ->where('passed', true)
                ->count();
            $row[] = $grades;

            $finalGrade = Grade::where('course_id', $course->id)
                ->where('user_id', $student->id)
                ->whereNull('lesson_id')
                ->first();
            $row[] = $finalGrade ? ($finalGrade->passed ? 'Зачёт' : 'Незачёт') : '-';

            $rows[] = $row;
        }

        $filename = 'course_' . $course->id . '_' . date('Y-m-d') . '.' . $format;

        if ($format === 'csv') {
            $output = fopen('php://temp', 'r+');
            fputcsv($output, $headers, ';');
            foreach ($rows as $row) {
                fputcsv($output, $row, ';');
            }
            rewind($output);
            $csv = stream_get_contents($output);
            fclose($output);

            return Response::make($csv, 200, [
                'Content-Type' => 'text/csv; charset=UTF-8',
                'Content-Disposition' => 'attachment; filename="' . $filename . '"',
            ]);
        }

        return back()->with('error', 'Неподдерживаемый формат.');
    }
}

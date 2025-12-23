<?php

namespace App\Http\Controllers;

use App\Models\Attendance;
use App\Models\Grade;
use App\Models\Lesson;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class EmployeeProfileController extends Controller
{
    public function index(Request $request): Response
    {
        $user = $request->user();

        $schedule = Lesson::whereHas('course.students', function ($query) use ($user) {
            $query->where('user_id', $user->id);
        })
            ->with(['course.teacher'])
            ->orderBy('scheduled_at', 'asc')
            ->where('scheduled_at', '>=', now())
            ->limit(20)
            ->get();

        $attendances = Attendance::where('user_id', $user->id)
            ->with(['lesson.course'])
            ->latest()
            ->limit(50)
            ->get();

        $attendanceStats = [
            'total' => Attendance::where('user_id', $user->id)->count(),
            'present' => Attendance::where('user_id', $user->id)->where('status', 'present')->count(),
            'absent' => Attendance::where('user_id', $user->id)->where('status', 'absent')->count(),
            'late' => Attendance::where('user_id', $user->id)->where('status', 'late')->count(),
        ];

        $attendanceStats['percentage'] = $attendanceStats['total'] > 0
            ? round(($attendanceStats['present'] / $attendanceStats['total']) * 100, 1)
            : 0;

        $grades = Grade::where('user_id', $user->id)
            ->with(['lesson.course'])
            ->latest()
            ->limit(50)
            ->get();

        $gradeStats = [
            'total' => Grade::where('user_id', $user->id)->count(),
            'passed' => Grade::where('user_id', $user->id)->where('passed', true)->count(),
            'failed' => Grade::where('user_id', $user->id)->where('passed', false)->count(),
        ];

        $gradeStats['percentage'] = $gradeStats['total'] > 0
            ? round(($gradeStats['passed'] / $gradeStats['total']) * 100, 1)
            : 0;

        return Inertia::render('Profile/Index', [
            'schedule' => $schedule,
            'attendances' => $attendances,
            'attendanceStats' => $attendanceStats,
            'grades' => $grades,
            'gradeStats' => $gradeStats,
        ]);
    }
}



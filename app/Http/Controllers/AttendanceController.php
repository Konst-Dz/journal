<?php

namespace App\Http\Controllers;

use App\Models\Attendance;
use App\Models\Course;
use App\Models\Lesson;
use Illuminate\Http\Request;
use Inertia\Inertia;

class AttendanceController extends Controller
{
    public function mark(Course $course, Lesson $lesson)
    {
        $this->authorize('update', $course);

        $lesson->load('course.students');
        $attendances = Attendance::where('lesson_id', $lesson->id)
            ->get()
            ->keyBy('user_id');

        return Inertia::render('Attendance/Mark', [
            'course' => $course->only('id', 'name'),
            'lesson' => $lesson->only('id', 'title', 'scheduled_at'),
            'students' => $course->students->map(fn ($s) => [
                'id' => $s->id,
                'name' => $s->name,
                'attendance' => $attendances->get($s->id)?->only('id', 'status', 'note'),
            ]),
        ]);
    }

    public function store(Request $request, Course $course, Lesson $lesson)
    {
        $this->authorize('update', $course);

        $request->validate([
            'attendances' => ['required', 'array'],
            'attendances.*.user_id' => ['required', 'exists:users,id'],
            'attendances.*.status' => ['required', 'in:present,absent,late,excused'],
            'attendances.*.note' => ['nullable', 'string'],
        ]);

        foreach ($request->attendances as $att) {
            Attendance::updateOrCreate(
                ['lesson_id' => $lesson->id, 'user_id' => $att['user_id']],
                ['status' => $att['status'], 'note' => $att['note'] ?? null]
            );
        }

        return back()->with('success', 'Посещаемость отмечена.');
    }

    public function report(Course $course)
    {
        $this->authorize('view', $course);

        $lessons = $course->lessons()->orderBy('scheduled_at')->get();
        $students = $course->students;

        $report = $students->map(function ($student) use ($lessons) {
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

            return [
                'id' => $student->id,
                'name' => $student->name,
                'stats' => $stats,
                'total' => $lessons->count(),
                'attendance_rate' => $lessons->count() > 0
                    ? round(($stats['present'] + $stats['excused']) / $lessons->count() * 100, 1)
                    : 0,
            ];
        });

        return Inertia::render('Attendance/Report', [
            'course' => $course->only('id', 'name'),
            'lessons' => $lessons->map(fn ($l) => $l->only('id', 'title', 'scheduled_at')),
            'report' => $report,
        ]);
    }
}



<?php

namespace App\Http\Controllers;

use App\Models\Course;
use App\Models\Grade;
use App\Models\Lesson;
use Illuminate\Http\Request;
use Inertia\Inertia;

class GradeController extends Controller
{
    public function index(Course $course)
    {
        $this->authorize('view', $course);

        $lessons = $course->lessons()->orderBy('scheduled_at')->get();
        $students = $course->students;

        $gradebook = $students->map(function ($student) use ($lessons, $course) {
            $grades = Grade::where('course_id', $course->id)
                ->where('user_id', $student->id)
                ->get()
                ->keyBy('lesson_id');

            $lessonGrades = $lessons->map(function ($lesson) use ($grades) {
                $grade = $grades->get($lesson->id);
                return [
                    'lesson_id' => $lesson->id,
                    'lesson_title' => $lesson->title,
                    'grade' => $grade ? [
                        'id' => $grade->id,
                        'passed' => $grade->passed,
                        'comment' => $grade->comment,
                    ] : null,
                ];
            });

            $finalGrade = Grade::where('course_id', $course->id)
                ->where('user_id', $student->id)
                ->whereNull('lesson_id')
                ->first();

            return [
                'id' => $student->id,
                'name' => $student->name,
                'lessons' => $lessonGrades,
                'final' => $finalGrade ? [
                    'id' => $finalGrade->id,
                    'passed' => $finalGrade->passed,
                    'comment' => $finalGrade->comment,
                ] : null,
            ];
        });

        return Inertia::render('Grades/Index', [
            'course' => $course->only('id', 'name'),
            'lessons' => $lessons->map(fn ($l) => $l->only('id', 'title', 'scheduled_at')),
            'gradebook' => $gradebook,
        ]);
    }

    public function store(Request $request, Course $course)
    {
        $this->authorize('update', $course);

        $request->validate([
            'user_id' => ['required', 'exists:users,id'],
            'lesson_id' => ['nullable', 'exists:lessons,id'],
            'passed' => ['required', 'boolean'],
            'comment' => ['nullable', 'string'],
        ]);

        // Проверка что lesson принадлежит курсу
        if ($request->lesson_id) {
            $lesson = Lesson::findOrFail($request->lesson_id);
            if ($lesson->course_id !== $course->id) {
                abort(403);
            }
        }

        Grade::updateOrCreate(
            [
                'course_id' => $course->id,
                'user_id' => $request->user_id,
                'lesson_id' => $request->lesson_id,
            ],
            [
                'passed' => $request->passed,
                'comment' => $request->comment,
            ]
        );

        return back()->with('success', 'Оценка сохранена.');
    }

    public function update(Request $request, Course $course, Grade $grade)
    {
        $this->authorize('update', $course);

        $request->validate([
            'passed' => ['required', 'boolean'],
            'comment' => ['nullable', 'string'],
        ]);

        $grade->update($request->only('passed', 'comment'));

        return back()->with('success', 'Оценка обновлена.');
    }

    public function destroy(Course $course, Grade $grade)
    {
        $this->authorize('update', $course);

        $grade->delete();

        return back()->with('success', 'Оценка удалена.');
    }
}

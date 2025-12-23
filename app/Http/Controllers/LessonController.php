<?php

namespace App\Http\Controllers;

use App\Models\Course;
use App\Models\Lesson;
use Illuminate\Http\Request;
use Inertia\Inertia;

class LessonController extends Controller
{
    public function index(Request $request, Course $course)
    {
        $this->authorize('view', $course);

        $lessons = $course->lessons()
            ->when($request->type, fn ($q, $type) => $q->where('type', $type))
            ->orderBy('scheduled_at')
            ->paginate(20)
            ->withQueryString();

        return Inertia::render('Lessons/Index', [
            'course' => $course->only('id', 'name'),
            'lessons' => $lessons,
            'filters' => $request->only(['type']),
        ]);
    }

    public function create(Course $course)
    {
        $this->authorize('update', $course);

        return Inertia::render('Lessons/Create', [
            'course' => $course->only('id', 'name'),
        ]);
    }

    public function store(Request $request, Course $course)
    {
        $this->authorize('update', $course);

        $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'scheduled_at' => ['required', 'date'],
            'duration_minutes' => ['required', 'integer', 'min:1'],
            'type' => ['required', 'in:lecture,practice,coaching'],
            'status' => ['required', 'in:pending,completed,cancelled'],
        ]);

        $course->lessons()->create($request->only('title', 'description', 'scheduled_at', 'duration_minutes', 'type', 'status'));

        return redirect()->route('courses.lessons.index', $course)->with('success', 'Занятие создано.');
    }

    public function edit(Course $course, Lesson $lesson)
    {
        $this->authorize('update', $course);

        return Inertia::render('Lessons/Edit', [
            'course' => $course->only('id', 'name'),
            'lesson' => $lesson->only('id', 'title', 'description', 'scheduled_at', 'duration_minutes', 'type', 'status'),
        ]);
    }

    public function update(Request $request, Course $course, Lesson $lesson)
    {
        $this->authorize('update', $course);

        $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'scheduled_at' => ['required', 'date'],
            'duration_minutes' => ['required', 'integer', 'min:1'],
            'type' => ['required', 'in:lecture,practice,coaching'],
            'status' => ['required', 'in:pending,completed,cancelled'],
        ]);

        $lesson->update($request->only('title', 'description', 'scheduled_at', 'duration_minutes', 'type', 'status'));

        return redirect()->route('courses.lessons.index', $course)->with('success', 'Занятие обновлено.');
    }

    public function destroy(Course $course, Lesson $lesson)
    {
        $this->authorize('update', $course);

        $lesson->delete();

        return redirect()->route('courses.lessons.index', $course)->with('success', 'Занятие удалено.');
    }
}



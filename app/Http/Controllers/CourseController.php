<?php

namespace App\Http\Controllers;

use App\Models\Course;
use App\Models\User;
use Illuminate\Http\Request;
use Inertia\Inertia;

class CourseController extends Controller
{
    public function index(Request $request)
    {
        $query = Course::with('teacher')
            ->when($request->status, fn ($q, $status) => $q->where('status', $status))
            ->when($request->search, fn ($q, $s) => $q->where('name', 'like', "%{$s}%"));

        if (auth()->user()->isTeacher()) {
            $query->where('teacher_id', auth()->id());
        }

        $courses = $query->orderBy('start_date', 'desc')
            ->paginate(15)
            ->withQueryString();

        return Inertia::render('Courses/Index', [
            'courses' => $courses,
            'filters' => $request->only(['status', 'search']),
        ]);
    }

    public function create()
    {
        return Inertia::render('Courses/Create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'status' => ['required', 'in:draft,active,completed'],
            'start_date' => ['required', 'date'],
            'end_date' => ['nullable', 'date', 'after:start_date'],
        ]);

        Course::create([
            ...$request->only('name', 'description', 'status', 'start_date', 'end_date'),
            'teacher_id' => auth()->id(),
        ]);

        return redirect()->route('courses.index')->with('success', 'Курс создан.');
    }

    public function show(Course $course)
    {
        $course->load(['teacher', 'students', 'lessons' => fn ($q) => $q->orderBy('scheduled_at')]);

        $enrolledIds = $course->students->pluck('id')->toArray();
        $availableStudents = User::where('role', 'employee')
            ->whereNotIn('id', $enrolledIds)
            ->get(['id', 'name', 'email']);

        return Inertia::render('Courses/Show', [
            'course' => $course,
            'availableStudents' => $availableStudents,
        ]);
    }

    public function edit(Course $course)
    {
        $this->authorize('update', $course);

        return Inertia::render('Courses/Edit', [
            'course' => $course->only('id', 'name', 'description', 'status', 'start_date', 'end_date'),
        ]);
    }

    public function update(Request $request, Course $course)
    {
        $this->authorize('update', $course);

        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'status' => ['required', 'in:draft,active,completed'],
            'start_date' => ['required', 'date'],
            'end_date' => ['nullable', 'date', 'after:start_date'],
        ]);

        $course->update($request->only('name', 'description', 'status', 'start_date', 'end_date'));

        return redirect()->route('courses.show', $course)->with('success', 'Курс обновлён.');
    }

    public function destroy(Course $course)
    {
        $this->authorize('delete', $course);

        $course->delete();

        return redirect()->route('courses.index')->with('success', 'Курс удалён.');
    }

    public function enroll(Request $request, Course $course)
    {
        $this->authorize('update', $course);

        $request->validate([
            'user_ids' => ['required', 'array'],
            'user_ids.*' => ['exists:users,id'],
        ]);

        $course->students()->syncWithoutDetaching($request->user_ids);

        return back()->with('success', 'Сотрудники добавлены на курс.');
    }

    public function unenroll(Course $course, User $user)
    {
        $this->authorize('update', $course);

        $course->students()->detach($user->id);

        return back()->with('success', 'Сотрудник удалён с курса.');
    }
}



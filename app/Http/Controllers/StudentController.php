<?php

namespace App\Http\Controllers;

use App\Models\Student;
use App\Models\User;
use App\Models\Role;
use App\Models\Department;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class StudentController extends Controller
{
    public function index(Request $request)
    {
        $user = Auth::user();
        if ($user->isStudent()) abort(403);

        $query = Student::with(['user', 'department']);

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('student_num', 'like', "%{$search}%")
                  ->orWhereHas('user', fn($u) => $u->where('first_name', 'like', "%{$search}%")
                      ->orWhere('last_name', 'like', "%{$search}%")
                      ->orWhere('email', 'like', "%{$search}%"));
            });
        }

        if ($request->filled('year_level')) {
            $query->where('year_level', $request->year_level);
        }
        if ($request->filled('department_id')) {
            $query->where('department_id', $request->department_id);
        }

        $students = $query->orderBy('created_at', 'desc')->paginate(15)->withQueryString();
        $departments = Department::all();

        return view('students.index', compact('students', 'departments'));
    }

    public function create()
    {
        $this->authorize('create', Student::class);
        $departments = Department::all();
        return view('students.create', compact('departments'));
    }

    public function store(Request $request)
    {
        $this->authorize('create', Student::class);

        $validated = $request->validate([
            'first_name'      => ['required', 'string', 'max:50'],
            'last_name'       => ['required', 'string', 'max:50'],
            'email'           => ['required', 'email', 'unique:users,email'],
            'username'        => ['required', 'string', 'max:50', 'unique:users,username'],
            'password'        => ['required', 'string', 'min:8', 'confirmed'],
            'student_num'     => ['required', 'string', 'max:30', 'unique:students,student_num'],
            'course'          => ['required', 'string', 'max:100'],
            'year_level'      => ['required', 'integer', 'min:1', 'max:6'],
            'section'         => ['nullable', 'string', 'max:20'],
            'department_id'   => ['nullable', 'exists:departments,department_id'],
            'birthdate'       => ['nullable', 'date'],
            'gender'          => ['nullable', 'in:male,female,prefer_not_to_say'],
            'phone'           => ['nullable', 'string', 'max:20'],
            'guardian_name'   => ['nullable', 'string', 'max:100'],
            'guardian_contact'=> ['nullable', 'string', 'max:20'],
        ]);

        DB::transaction(function () use ($validated) {
            $role = Role::where('role_name', 'Student')->firstOrFail();

            $user = User::create([
                'first_name' => $validated['first_name'],
                'last_name'  => $validated['last_name'],
                'email'      => $validated['email'],
                'username'   => $validated['username'],
                'password'   => Hash::make($validated['password']),
                'phone'      => $validated['phone'] ?? null,
                'role_id'    => $role->role_id,
                'is_active'  => true,
            ]);

            Student::create([
                'user_id'         => $user->user_id,
                'student_num'     => $validated['student_num'],
                'course'          => $validated['course'],
                'year_level'      => $validated['year_level'],
                'section'         => $validated['section'] ?? null,
                'department_id'   => $validated['department_id'] ?? null,
                'birthdate'       => $validated['birthdate'] ?? null,
                'gender'          => $validated['gender'] ?? null,
                'guardian_name'   => $validated['guardian_name'] ?? null,
                'guardian_contact'=> $validated['guardian_contact'] ?? null,
            ]);
        });

        return redirect()->route('students.index')->with('success', 'Student registered successfully.');
    }

    public function show(Student $student)
    {
        $user = Auth::user();
        if ($user->isStudent() && $user->student?->student_id !== $student->student_id) abort(403);

        $student->load(['user', 'department', 'appointments.counselor.user', 'sessions.counselor.user']);

        $stats = [
            'total_sessions'   => $student->sessions()->where('session_status', 'completed')->count(),
            'total_appts'      => $student->appointments()->count(),
            'pending_appts'    => $student->appointments()->where('appointment_status', 'pending')->count(),
        ];

        return view('students.show', compact('student', 'stats'));
    }

    public function edit(Student $student)
    {
        $user = Auth::user();
        if ($user->isStudent() && $user->student?->student_id !== $student->student_id) abort(403);
        if ($user->isCounselor()) abort(403);

        $departments = Department::all();
        return view('students.edit', compact('student', 'departments'));
    }

    public function update(Request $request, Student $student)
    {
        $user = Auth::user();
        if ($user->isStudent() && $user->student?->student_id !== $student->student_id) abort(403);
        if ($user->isCounselor()) abort(403);

        $validated = $request->validate([
            'first_name'      => ['required', 'string', 'max:50'],
            'last_name'       => ['required', 'string', 'max:50'],
            'phone'           => ['nullable', 'string', 'max:20'],
            'course'          => ['required', 'string', 'max:100'],
            'year_level'      => ['required', 'integer', 'min:1', 'max:6'],
            'section'         => ['nullable', 'string', 'max:20'],
            'department_id'   => ['nullable', 'exists:departments,department_id'],
            'birthdate'       => ['nullable', 'date'],
            'gender'          => ['nullable', 'in:male,female,prefer_not_to_say'],
            'guardian_name'   => ['nullable', 'string', 'max:100'],
            'guardian_contact'=> ['nullable', 'string', 'max:20'],
        ]);

        DB::transaction(function () use ($validated, $student) {
            $student->user->update([
                'first_name' => $validated['first_name'],
                'last_name'  => $validated['last_name'],
                'phone'      => $validated['phone'] ?? null,
            ]);

            $student->update([
                'course'          => $validated['course'],
                'year_level'      => $validated['year_level'],
                'section'         => $validated['section'] ?? null,
                'department_id'   => $validated['department_id'] ?? null,
                'birthdate'       => $validated['birthdate'] ?? null,
                'gender'          => $validated['gender'] ?? null,
                'guardian_name'   => $validated['guardian_name'] ?? null,
                'guardian_contact'=> $validated['guardian_contact'] ?? null,
            ]);
        });

        return redirect()->route('students.show', $student)->with('success', 'Student profile updated successfully.');
    }

        
    public function search(Request $request)
    {
        $q    = $request->input('q', '');
        $byId = $request->boolean('by_id');
    
        $query = \App\Models\Student::with(['user', 'department'])
            ->whereHas('user', fn ($u) => $u->where('is_active', true));
    
        if ($byId) {
            // Restore a specific student by student_id (used to re-hydrate old() value)
            $query->where('student_id', (int) $q);
        } else {
            $query->where(function ($q2) use ($q) {
                $q2->where('student_num', 'like', "%{$q}%")
                ->orWhereHas('user', fn ($u) =>
                        $u->where(DB::raw("CONCAT(first_name, ' ', last_name)"), 'like', "%{$q}%")
                        ->orWhere('first_name', 'like', "%{$q}%")
                        ->orWhere('last_name',  'like', "%{$q}%")
                );
            });
        }
    
        $students = $query->limit(10)->get()->map(fn ($s) => [
            'student_id'  => $s->student_id,
            'student_num' => $s->student_num,
            'first_name'  => $s->user->first_name,
            'last_name'   => $s->user->last_name,
            'full_name'   => $s->user->full_name,
            'initials'    => $s->user->initials,
            'department'  => $s->department?->dept_name ?? '',
        ]);
    
        return response()->json(['students' => $students]);
    }
 
}

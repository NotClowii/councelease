<?php

namespace App\Http\Controllers;

use App\Models\Counselor;
use App\Models\User;
use App\Models\Role;
use App\Models\Department;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class CounselorController extends Controller
{
    public function index(Request $request)
    {
        $user = Auth::user();
        if ($user->isStudent()) abort(403);

        $query = Counselor::with(['user', 'department']);

        if ($request->filled('search')) {
            $search = $request->search;
            $query->whereHas('user', fn($q) => $q
                ->where('first_name', 'like', "%{$search}%")
                ->orWhere('last_name', 'like', "%{$search}%")
                ->orWhere('email', 'like', "%{$search}%")
            );
        }

        if ($request->filled('department_id')) {
            $query->where('department_id', $request->department_id);
        }

        $counselors  = $query->orderBy('created_at', 'desc')->paginate(15)->withQueryString();
        $departments = Department::all();

        return view('counselors.index', compact('counselors', 'departments'));
    }

    public function create()
    {
        $user = Auth::user();
        if (!$user->isOfficeStaff() && !$user->isSystemAdmin()) abort(403);
        $departments = Department::all();
        return view('counselors.create', compact('departments'));
    }

    public function store(Request $request)
    {
        $user = Auth::user();
        if (!$user->isOfficeStaff() && !$user->isSystemAdmin()) abort(403);

        $validated = $request->validate([
            'first_name'               => ['required', 'string', 'max:50'],
            'last_name'                => ['required', 'string', 'max:50'],
            'email'                    => ['required', 'email', 'unique:users,email'],
            'username'                 => ['required', 'string', 'max:50', 'unique:users,username'],
            'password'                 => ['required', 'string', 'min:8', 'confirmed'],
            'phone'                    => ['nullable', 'string', 'max:20'],
            'department_id'            => ['nullable', 'exists:departments,department_id'],
            'specialization'           => ['nullable', 'string', 'max:100'],
            'license_number'           => ['nullable', 'string', 'max:50'],
            'max_appointments_per_day' => ['nullable', 'integer', 'min:1', 'max:20'],
            'available_from'           => ['nullable', 'string'],
            'available_until'          => ['nullable', 'string'],
        ]);

        DB::transaction(function () use ($validated) {
            $role = Role::where('role_name', 'Counselor')->firstOrFail();

            $newUser = User::create([
                'first_name' => $validated['first_name'],
                'last_name'  => $validated['last_name'],
                'email'      => $validated['email'],
                'username'   => $validated['username'],
                'password'   => Hash::make($validated['password']),
                'phone'      => $validated['phone'] ?? null,
                'role_id'    => $role->role_id,
                'is_active'  => true,
            ]);

            Counselor::create([
                'user_id'                  => $newUser->user_id,
                'department_id'            => $validated['department_id'] ?? null,
                'specialization'           => $validated['specialization'] ?? null,
                'license_number'           => $validated['license_number'] ?? null,
                'max_appointments_per_day' => $validated['max_appointments_per_day'] ?? 8,
                'available_days'           => [1, 2, 3, 4, 5],
                'available_from'           => $validated['available_from'] ?? '08:00:00',
                'available_until'          => $validated['available_until'] ?? '17:00:00',
            ]);
        });

        return redirect()->route('counselors.index')->with('success', 'Counselor registered successfully.');
    }

    public function show(Counselor $counselor)
    {
        if (Auth::user()->isStudent()) abort(403);
        $counselor->load(['user', 'department', 'appointments.student.user', 'sessions.student.user']);
        $stats = [
            'total_sessions'       => $counselor->sessions()->where('session_status', 'completed')->count(),
            'total_appointments'   => $counselor->appointments()->count(),
            'pending_appointments' => $counselor->appointments()->where('appointment_status', 'pending')->count(),
            'students_served'      => $counselor->sessions()->distinct('student_id')->count('student_id'),
        ];
        return view('counselors.show', compact('counselor', 'stats'));
    }

    public function edit(Counselor $counselor)
    {
        $user = Auth::user();
        if (!$user->isOfficeStaff() && !$user->isSystemAdmin()) abort(403);
        $departments = Department::all();
        return view('counselors.edit', compact('counselor', 'departments'));
    }

    public function update(Request $request, Counselor $counselor)
    {
        $user = Auth::user();
        if (!$user->isOfficeStaff() && !$user->isSystemAdmin()) abort(403);

        $validated = $request->validate([
            'first_name'               => ['required', 'string', 'max:50'],
            'last_name'                => ['required', 'string', 'max:50'],
            'phone'                    => ['nullable', 'string', 'max:20'],
            'department_id'            => ['nullable', 'exists:departments,department_id'],
            'specialization'           => ['nullable', 'string', 'max:100'],
            'license_number'           => ['nullable', 'string', 'max:50'],
            'max_appointments_per_day' => ['nullable', 'integer', 'min:1', 'max:20'],
            'available_from'           => ['nullable', 'string'],
            'available_until'          => ['nullable', 'string'],
        ]);

        DB::transaction(function () use ($validated, $counselor) {
            $counselor->user->update([
                'first_name' => $validated['first_name'],
                'last_name'  => $validated['last_name'],
                'phone'      => $validated['phone'] ?? null,
            ]);
            $counselor->update([
                'department_id'            => $validated['department_id'] ?? null,
                'specialization'           => $validated['specialization'] ?? null,
                'license_number'           => $validated['license_number'] ?? null,
                'max_appointments_per_day' => $validated['max_appointments_per_day'] ?? 8,
                'available_from'           => $validated['available_from'] ?? '08:00:00',
                'available_until'          => $validated['available_until'] ?? '17:00:00',
            ]);
        });

        return redirect()->route('counselors.show', $counselor)->with('success', 'Counselor updated successfully.');
    }
}
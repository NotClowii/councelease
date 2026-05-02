@extends('layouts.app')

@section('title', 'Students')
@section('page-title', 'Students')

@section('content')
<div class="page-header">
    <div>
        <h1>Students</h1>
        <p>Manage student profiles and records</p>
    </div>
    @if(auth()->user()->isOfficeStaff() || auth()->user()->isSystemAdmin())
    <a href="{{ route('students.create') }}" class="btn btn-primary">
        <i class="ph-bold ph-plus"></i> Add Student
    </a>
    @endif
</div>

<div class="card">
    <form method="GET" action="{{ route('students.index') }}">
        <div class="filter-bar">
            <input type="text" name="search" class="form-control" style="width:240px;" placeholder="Search by name, ID number, email..."
                value="{{ request('search') }}">

            <select name="year_level" class="form-control" style="width:140px;" onchange="this.form.submit()">
                <option value="">All Year Levels</option>
                @foreach([1=>'1st Year',2=>'2nd Year',3=>'3rd Year',4=>'4th Year',5=>'5th Year'] as $val=>$label)
                <option value="{{ $val }}" {{ request('year_level') == $val ? 'selected' : '' }}>{{ $label }}</option>
                @endforeach
            </select>

            <select name="department_id" class="form-control" style="width:220px;" onchange="this.form.submit()">
                <option value="">All Departments</option>
                @foreach($departments as $dept)
                <option value="{{ $dept->department_id }}" {{ request('department_id') == $dept->department_id ? 'selected' : '' }}>
                    {{ $dept->department_name }}
                </option>
                @endforeach
            </select>

            <button type="submit" class="btn btn-primary btn-sm"><i class="ph-bold ph-magnifying-glass"></i> Search</button>
            @if(request()->hasAny(['search','year_level','department_id']))
            <a href="{{ route('students.index') }}" class="btn btn-outline btn-sm"><i class="ph-bold ph-x"></i> Clear</a>
            @endif
        </div>
    </form>

    <div class="table-wrap">
        @if($students->isEmpty())
        <div class="empty-state">
            <i class="ph ph-student"></i>
            <h3>No students found</h3>
            <p>Try adjusting your search or add a new student.</p>
        </div>
        @else
        <table>
            <thead>
                <tr>
                    <th>Student</th>
                    <th>ID Number</th>
                    <th>Course & Year</th>
                    <th>Department</th>
                    <th>Email</th>
                    <th>Sessions</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                @foreach($students as $student)
                <tr>
                    <td>
                        <div class="flex items-center gap-2">
                            <div class="avatar">{{ $student->user->initials }}</div>
                            <div>
                                <div class="font-semibold">{{ $student->user->full_name }}</div>
                                <div class="text-sm text-muted">
                                    @if($student->user->is_active)
                                    <span style="color:var(--success);">● Active</span>
                                    @else
                                    <span style="color:var(--text-muted);">● Inactive</span>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </td>
                    <td class="font-semibold">{{ $student->student_num }}</td>
                    <td>
                        <div>{{ Str::limit($student->course, 30) }}</div>
                        <div class="text-sm text-muted">{{ $student->year_level_label }} {{ $student->section ? '· Sec. ' . $student->section : '' }}</div>
                    </td>
                    <td class="text-sm">{{ $student->department?->department_code ?? '—' }}</td>
                    <td class="text-sm text-muted">{{ $student->user->email }}</td>
                    <td>
                        @php $sessionCount = $student->sessions()->where('session_status','completed')->count(); @endphp
                        @if($sessionCount > 0)
                        <span class="badge badge-info">{{ $sessionCount }}</span>
                        @else
                        <span class="text-muted text-sm">None</span>
                        @endif
                    </td>
                    <td>
                        <div class="flex gap-2">
                            <a href="{{ route('students.show', $student) }}" class="btn btn-outline btn-sm btn-icon">
                                <i class="ph-bold ph-eye"></i>
                            </a>
                            @if(auth()->user()->isOfficeStaff() || auth()->user()->isSystemAdmin())
                            <a href="{{ route('students.edit', $student) }}" class="btn btn-outline btn-sm btn-icon">
                                <i class="ph-bold ph-pencil"></i>
                            </a>
                            @endif
                        </div>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
        @endif
    </div>

    @if($students->hasPages())
    <div style="padding:16px 20px;border-top:1px solid var(--border-light);display:flex;align-items:center;justify-content:space-between;">
        <div class="text-sm text-muted">Showing {{ $students->firstItem() }}–{{ $students->lastItem() }} of {{ $students->total() }} students</div>
        {{ $students->links() }}
    </div>
    @endif
</div>
@endsection

@extends('layouts.app')
@section('title', 'Counselors')
@section('page-title', 'Counselors')
@section('content')
<div class="page-header">
    <div><h1>Counselors</h1><p>Manage guidance counselor profiles</p></div>
    @if(auth()->user()->isOfficeStaff() || auth()->user()->isSystemAdmin())
    <a href="{{ route('counselors.create') }}" class="btn btn-primary"><i class="ph-bold ph-plus"></i> Add Counselor</a>
    @endif
</div>
<div class="card">
    <form method="GET" action="{{ route('counselors.index') }}">
        <div class="filter-bar">
            <input type="text" name="search" class="form-control" style="width:240px;" placeholder="Search by name or email..." value="{{ request('search') }}">
            <select name="department_id" class="form-control" style="width:220px;" onchange="this.form.submit()">
                <option value="">All Departments</option>
                @foreach($departments as $dept)
                <option value="{{ $dept->department_id }}" {{ request('department_id') == $dept->department_id ? 'selected' : '' }}>{{ $dept->department_name }}</option>
                @endforeach
            </select>
            <button type="submit" class="btn btn-primary btn-sm"><i class="ph-bold ph-magnifying-glass"></i> Search</button>
            @if(request()->hasAny(['search','department_id']))
            <a href="{{ route('counselors.index') }}" class="btn btn-outline btn-sm"><i class="ph-bold ph-x"></i> Clear</a>
            @endif
        </div>
    </form>
    <div class="table-wrap">
        @if($counselors->isEmpty())
        <div class="empty-state">
            <i class="ph ph-users-three"></i>
            <h3>No counselors found</h3>
            <p>Add a counselor to get started.</p>
        </div>
        @else
        <table>
            <thead>
                <tr>
                    <th>Counselor</th>
                    <th>Department</th>
                    <th>Specialization</th>
                    <th>Available Hours</th>
                    <th>Sessions</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                @foreach($counselors as $counselor)
                <tr>
                    <td>
                        <div class="flex items-center gap-2">
                            <div class="avatar" style="background:var(--accent);">{{ $counselor->user->initials }}</div>
                            <div>
                                <div class="font-semibold">{{ $counselor->user->full_name }}</div>
                                <div class="text-sm text-muted">{{ $counselor->user->email }}</div>
                            </div>
                        </div>
                    </td>
                    <td>{{ $counselor->department?->department_code ?? '—' }}</td>
                    <td>{{ $counselor->specialization ?? '—' }}</td>
                    <td class="text-sm">
                        {{ \Carbon\Carbon::parse($counselor->available_from)->format('h:i A') }} –
                        {{ \Carbon\Carbon::parse($counselor->available_until)->format('h:i A') }}
                    </td>
                    <td>
                        @php $count = $counselor->sessions()->where('session_status','completed')->count(); @endphp
                        <span class="badge badge-info">{{ $count }}</span>
                    </td>
                    <td>
                        <div class="flex gap-2">
                            <a href="{{ route('counselors.show', $counselor) }}" class="btn btn-outline btn-sm btn-icon"><i class="ph-bold ph-eye"></i></a>
                            @if(auth()->user()->isOfficeStaff() || auth()->user()->isSystemAdmin())
                            <a href="{{ route('counselors.edit', $counselor) }}" class="btn btn-outline btn-sm btn-icon"><i class="ph-bold ph-pencil"></i></a>
                            @endif
                        </div>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
        @endif
    </div>
    @if($counselors->hasPages())
    <div style="padding:16px 20px;border-top:1px solid var(--border-light);display:flex;justify-content:space-between;align-items:center;">
        <div class="text-sm text-muted">Showing {{ $counselors->firstItem() }}–{{ $counselors->lastItem() }} of {{ $counselors->total() }}</div>
        {{ $counselors->links() }}
    </div>
    @endif
</div>
@endsection
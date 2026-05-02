@extends('layouts.app')

@section('title', $student->user->full_name)
@section('page-title', 'Student Profile')
@section('breadcrumb', 'Students / ' . $student->user->full_name)

@section('content')
<div class="page-header">
    <div style="display:flex;align-items:center;gap:12px;">
        <a href="{{ route('students.index') }}" class="btn btn-outline btn-sm btn-icon">
            <i class="ph-bold ph-arrow-left"></i>
        </a>
        <h1>Student Profile</h1>
    </div>
    @if(auth()->user()->isOfficeStaff() || auth()->user()->isSystemAdmin() || (auth()->user()->isStudent() && auth()->user()->student?->student_id === $student->student_id))
    <a href="{{ route('students.edit', $student) }}" class="btn btn-outline">
        <i class="ph-bold ph-pencil"></i> Edit Profile
    </a>
    @endif
</div>

<div style="display:grid;grid-template-columns:280px 1fr;gap:20px;align-items:start;">

    {{-- Profile Card --}}
    <div style="display:flex;flex-direction:column;gap:16px;">
        <div class="card">
            <div class="card-body" style="text-align:center;padding:28px 20px;">
                <div class="avatar lg" style="width:72px;height:72px;font-size:24px;margin:0 auto 14px;">{{ $student->user->initials }}</div>
                <h2 style="font-size:17px;font-weight:700;">{{ $student->user->full_name }}</h2>
                <div style="font-size:13px;color:var(--text-muted);margin-top:4px;">{{ $student->student_num }}</div>
                <div style="margin-top:10px;">
                    <span class="badge {{ $student->user->is_active ? 'badge-success' : 'badge-danger' }}">
                        {{ $student->user->is_active ? 'Active' : 'Inactive' }}
                    </span>
                </div>
            </div>
            <div style="padding:0 20px 20px;">
                <hr class="divider" style="margin-top:0;">
                <dl class="detail-list">
                    <dt>Email</dt>
                    <dd>{{ $student->user->email }}</dd>
                    <dt>Phone</dt>
                    <dd>{{ $student->user->phone ?? '—' }}</dd>
                    <dt>Gender</dt>
                    <dd style="text-transform:capitalize;">{{ $student->gender ? str_replace('_', ' ', $student->gender) : '—' }}</dd>
                    <dt>Birthdate</dt>
                    <dd>{{ $student->birthdate ? $student->birthdate->format('M d, Y') : '—' }}</dd>
                </dl>
            </div>
        </div>

        <div class="card">
            <div class="card-header"><span class="card-title">Statistics</span></div>
            <div class="card-body">
                <div style="display:flex;flex-direction:column;gap:12px;">
                    @foreach([
                        ['label'=>'Total Sessions', 'value'=>$stats['total_sessions'], 'icon'=>'chats-circle', 'color'=>'green'],
                        ['label'=>'Total Appointments', 'value'=>$stats['total_appts'], 'icon'=>'calendar', 'color'=>'blue'],
                        ['label'=>'Pending Appointments', 'value'=>$stats['pending_appts'], 'icon'=>'hourglass', 'color'=>'amber'],
                    ] as $s)
                    <div style="display:flex;align-items:center;gap:10px;padding:10px;background:var(--surface);border-radius:8px;">
                        <div class="stat-icon {{ $s['color'] }}" style="width:34px;height:34px;font-size:16px;">
                            <i class="ph-bold ph-{{ $s['icon'] }}"></i>
                        </div>
                        <div>
                            <div style="font-size:18px;font-weight:700;">{{ $s['value'] }}</div>
                            <div style="font-size:11px;color:var(--text-muted);">{{ $s['label'] }}</div>
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>

    {{-- Main Content --}}
    <div style="display:flex;flex-direction:column;gap:20px;">

        <div class="card">
            <div class="card-header"><span class="card-title">Academic Information</span></div>
            <div class="card-body">
                <div class="grid-2">
                    <dl class="detail-list">
                        <dt>Course / Program</dt>
                        <dd>{{ $student->course }}</dd>
                        <dt>Year Level</dt>
                        <dd>{{ $student->year_level_label }}</dd>
                        <dt>Section</dt>
                        <dd>{{ $student->section ?? '—' }}</dd>
                    </dl>
                    <dl class="detail-list">
                        <dt>Department</dt>
                        <dd>{{ $student->department?->department_name ?? '—' }}</dd>
                        <dt>Guardian Name</dt>
                        <dd>{{ $student->guardian_name ?? '—' }}</dd>
                        <dt>Guardian Contact</dt>
                        <dd>{{ $student->guardian_contact ?? '—' }}</dd>
                    </dl>
                </div>
                @if($student->address)
                <dl class="detail-list">
                    <dt>Address</dt>
                    <dd>{{ $student->address }}</dd>
                </dl>
                @endif
            </div>
        </div>

        {{-- Recent Appointments --}}
        <div class="card">
            <div class="card-header">
                <span class="card-title">Recent Appointments</span>
                <a href="{{ route('appointments.index') }}" class="btn btn-outline btn-sm">View All</a>
            </div>
            @if($student->appointments->isEmpty())
            <div class="empty-state" style="padding:28px;">
                <h3>No appointments yet</h3>
            </div>
            @else
            <div class="table-wrap">
                <table>
                    <thead><tr><th>Date</th><th>Counselor</th><th>Concern</th><th>Status</th></tr></thead>
                    <tbody>
                        @foreach($student->appointments->take(5) as $appt)
                        @php $statusMap=['pending'=>'warning','approved'=>'success','completed'=>'info','cancelled'=>'danger','no_show'=>'secondary','rescheduled'=>'primary']; @endphp
                        <tr>
                            <td>{{ $appt->appointment_datetime->format('M d, Y') }}</td>
                            <td>{{ $appt->counselor->user->full_name }}</td>
                            <td>{{ $appt->concern_type ?? '—' }}</td>
                            <td>
                                <span class="badge badge-{{ $statusMap[$appt->appointment_status] ?? 'secondary' }}">
                                    {{ ucfirst(str_replace('_',' ',$appt->appointment_status)) }}
                                </span>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            @endif
        </div>

        {{-- Recent Sessions --}}
        <div class="card">
            <div class="card-header">
                <span class="card-title">Counseling Session History</span>
                <a href="{{ route('sessions.index') }}" class="btn btn-outline btn-sm">View All</a>
            </div>
            @if($student->sessions->isEmpty())
            <div class="empty-state" style="padding:28px;">
                <h3>No sessions recorded</h3>
            </div>
            @else
            <div class="table-wrap">
                <table>
                    <thead><tr><th>Date</th><th>Counselor</th><th>Duration</th><th>Status</th></tr></thead>
                    <tbody>
                        @foreach($student->sessions->take(5) as $session)
                        <tr>
                            <td>{{ $session->session_datetime->format('M d, Y') }}</td>
                            <td>{{ $session->counselor->user->full_name }}</td>
                            <td>{{ $session->actual_duration_minutes ? $session->actual_duration_minutes . ' min' : '—' }}</td>
                            <td>
                                <span class="badge badge-{{ $session->session_status === 'completed' ? 'success' : 'info' }}">
                                    {{ ucfirst($session->session_status) }}
                                </span>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            @endif
        </div>
    </div>
</div>
@endsection

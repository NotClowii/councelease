@extends('layouts.app')

@section('title', 'Dashboard')
@section('page-title', 'Dashboard')

@section('content')
<div class="page-header">
    <div>
        <h1>Good {{ now()->hour < 12 ? 'morning' : (now()->hour < 17 ? 'afternoon' : 'evening') }}, {{ auth()->user()->first_name }} 👋</h1>
        <p>{{ now()->format('l, F j, Y') }} · {{ auth()->user()->role?->role_name }}</p>
    </div>
    @if(auth()->user()->isStudent())
    <a href="{{ route('appointments.create') }}" class="btn btn-primary">
        <i class="ph-bold ph-plus"></i>
        Book Appointment
    </a>
    @elseif(auth()->user()->isCounselor() || auth()->user()->isOfficeStaff() || auth()->user()->isSystemAdmin())
    <a href="{{ route('appointments.index') }}?status=pending" class="btn btn-primary">
        <i class="ph-bold ph-calendar-check"></i>
        View Pending
    </a>
    @endif
</div>

{{-- ─── STUDENT DASHBOARD ─── --}}
@if(auth()->user()->isStudent())
<div class="stats-grid">
    <div class="stat-card">
        <div class="stat-icon green"><i class="ph-bold ph-chats-circle"></i></div>
        <div class="stat-info">
            <div class="stat-value">{{ $data['total_sessions'] }}</div>
            <div class="stat-label">Total Sessions Completed</div>
        </div>
    </div>
    <div class="stat-card">
        <div class="stat-icon amber"><i class="ph-bold ph-clock"></i></div>
        <div class="stat-info">
            <div class="stat-value">{{ $data['pending_count'] }}</div>
            <div class="stat-label">Pending Appointments</div>
        </div>
    </div>
    <div class="stat-card">
        <div class="stat-icon blue"><i class="ph-bold ph-calendar"></i></div>
        <div class="stat-info">
            <div class="stat-value">{{ $data['upcoming_appointments']->count() }}</div>
            <div class="stat-label">Upcoming Appointments</div>
        </div>
    </div>
</div>

<div class="grid-2" style="gap:20px;">
    {{-- Upcoming Appointments --}}
    <div class="card">
        <div class="card-header">
            <span class="card-title">Upcoming Appointments</span>
            <a href="{{ route('appointments.index') }}" class="btn btn-outline btn-sm">View All</a>
        </div>
        @if($data['upcoming_appointments']->isEmpty())
        <div class="empty-state">
            <i class="ph ph-calendar-blank"></i>
            <h3>No upcoming appointments</h3>
            <p>Book a session with a counselor to get started.</p>
            <a href="{{ route('appointments.create') }}" class="btn btn-primary btn-sm" style="margin-top:14px;">Book Now</a>
        </div>
        @else
        <div class="table-wrap">
            <table>
                <thead>
                    <tr>
                        <th>Counselor</th>
                        <th>Date & Time</th>
                        <th>Status</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($data['upcoming_appointments'] as $appt)
                    <tr>
                        <td>
                            <div class="flex items-center gap-2">
                                <div class="avatar">{{ $appt->counselor->user->initials }}</div>
                                <div>
                                    <div class="font-semibold">{{ $appt->counselor->user->full_name }}</div>
                                    <div class="text-sm text-muted">{{ $appt->concern_type }}</div>
                                </div>
                            </div>
                        </td>
                        <td>
                            <div class="font-semibold">{{ $appt->appointment_datetime->format('M d, Y') }}</div>
                            <div class="text-sm text-muted">{{ $appt->appointment_datetime->format('h:i A') }}</div>
                        </td>
                        <td>
                            <span class="badge badge-{{ $appt->appointment_status === 'approved' ? 'success' : 'warning' }}">
                                {{ ucfirst($appt->appointment_status) }}
                            </span>
                        </td>
                        <td>
                            <a href="{{ route('appointments.show', $appt) }}" class="btn btn-outline btn-sm btn-icon">
                                <i class="ph-bold ph-arrow-right"></i>
                            </a>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        @endif
    </div>

    {{-- Past Sessions --}}
    <div class="card">
        <div class="card-header">
            <span class="card-title">Recent Sessions</span>
            <a href="{{ route('sessions.index') }}" class="btn btn-outline btn-sm">View All</a>
        </div>
        @if($data['past_sessions']->isEmpty())
        <div class="empty-state">
            <i class="ph ph-chats"></i>
            <h3>No sessions yet</h3>
            <p>Your completed counseling sessions will appear here.</p>
        </div>
        @else
        <div class="table-wrap">
            <table>
                <thead><tr><th>Counselor</th><th>Date</th><th>Status</th></tr></thead>
                <tbody>
                    @foreach($data['past_sessions'] as $session)
                    <tr>
                        <td>
                            <div class="flex items-center gap-2">
                                <div class="avatar">{{ $session->counselor->user->initials }}</div>
                                <span>{{ $session->counselor->user->full_name }}</span>
                            </div>
                        </td>
                        <td>{{ $session->session_datetime->format('M d, Y') }}</td>
                        <td><span class="badge badge-success">Completed</span></td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        @endif
    </div>
</div>

{{-- ─── COUNSELOR DASHBOARD ─── --}}
@elseif(auth()->user()->isCounselor())
<div class="stats-grid">
    <div class="stat-card">
        <div class="stat-icon amber"><i class="ph-bold ph-hourglass"></i></div>
        <div class="stat-info">
            <div class="stat-value">{{ $data['pending_approvals'] }}</div>
            <div class="stat-label">Pending Approvals</div>
        </div>
    </div>
    <div class="stat-card">
        <div class="stat-icon blue"><i class="ph-bold ph-calendar-check"></i></div>
        <div class="stat-info">
            <div class="stat-value">{{ $data['todays_appointments']->count() }}</div>
            <div class="stat-label">Today's Appointments</div>
        </div>
    </div>
    <div class="stat-card">
        <div class="stat-icon green"><i class="ph-bold ph-chats-circle"></i></div>
        <div class="stat-info">
            <div class="stat-value">{{ $data['total_sessions_this_month'] }}</div>
            <div class="stat-label">Sessions This Month</div>
        </div>
    </div>
    <div class="stat-card">
        <div class="stat-icon teal"><i class="ph-bold ph-users"></i></div>
        <div class="stat-info">
            <div class="stat-value">{{ $data['total_students_served'] }}</div>
            <div class="stat-label">Students Served</div>
        </div>
    </div>
</div>

<div class="grid-2" style="gap:20px;">
    <div class="card">
        <div class="card-header">
            <span class="card-title">Today's Schedule</span>
            <span class="badge badge-info">{{ now()->format('M d') }}</span>
        </div>
        @if($data['todays_appointments']->isEmpty())
        <div class="empty-state">
            <i class="ph ph-sun"></i>
            <h3>No appointments today</h3>
            <p>Enjoy a free day!</p>
        </div>
        @else
        <div class="table-wrap">
            <table>
                <thead><tr><th>Student</th><th>Time</th><th>Concern</th><th>Status</th><th></th></tr></thead>
                <tbody>
                    @foreach($data['todays_appointments'] as $appt)
                    <tr>
                        <td>
                            <div class="flex items-center gap-2">
                                <div class="avatar">{{ $appt->student->user->initials }}</div>
                                <div>
                                    <div class="font-semibold">{{ $appt->student->user->full_name }}</div>
                                    <div class="text-sm text-muted">{{ $appt->student->student_num }}</div>
                                </div>
                            </div>
                        </td>
                        <td>{{ $appt->appointment_datetime->format('h:i A') }}</td>
                        <td>{{ $appt->concern_type ?? '—' }}</td>
                        <td>
                            <span class="badge badge-{{ $appt->appointment_status === 'approved' ? 'success' : 'warning' }}">
                                {{ ucfirst($appt->appointment_status) }}
                            </span>
                        </td>
                        <td>
                            <a href="{{ route('appointments.show', $appt) }}" class="btn btn-outline btn-sm btn-icon">
                                <i class="ph-bold ph-arrow-right"></i>
                            </a>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        @endif
    </div>

    <div class="card">
        <div class="card-header">
            <span class="card-title">Recent Sessions</span>
            <a href="{{ route('sessions.index') }}" class="btn btn-outline btn-sm">View All</a>
        </div>
        @if($data['recent_sessions']->isEmpty())
        <div class="empty-state">
            <i class="ph ph-chats"></i>
            <h3>No sessions yet</h3>
        </div>
        @else
        <div class="table-wrap">
            <table>
                <thead><tr><th>Student</th><th>Date</th><th>Status</th></tr></thead>
                <tbody>
                    @foreach($data['recent_sessions'] as $session)
                    <tr>
                        <td>
                            <div class="flex items-center gap-2">
                                <div class="avatar">{{ $session->student->user->initials }}</div>
                                <span>{{ $session->student->user->full_name }}</span>
                            </div>
                        </td>
                        <td>{{ $session->session_datetime->format('M d, Y') }}</td>
                        <td><span class="badge badge-{{ $session->session_status === 'completed' ? 'success' : 'info' }}">{{ ucfirst($session->session_status) }}</span></td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        @endif
    </div>
</div>

{{-- ─── ADMIN / STAFF DASHBOARD ─── --}}
@else
<div class="stats-grid">
    <div class="stat-card">
        <div class="stat-icon teal"><i class="ph-bold ph-student"></i></div>
        <div class="stat-info">
            <div class="stat-value">{{ $data['total_students'] }}</div>
            <div class="stat-label">Total Students</div>
        </div>
    </div>
    <div class="stat-card">
        <div class="stat-icon green"><i class="ph-bold ph-users-three"></i></div>
        <div class="stat-info">
            <div class="stat-value">{{ $data['total_counselors'] }}</div>
            <div class="stat-label">Active Counselors</div>
        </div>
    </div>
    <div class="stat-card">
        <div class="stat-icon blue"><i class="ph-bold ph-calendar"></i></div>
        <div class="stat-info">
            <div class="stat-value">{{ $data['todays_appointments'] }}</div>
            <div class="stat-label">Today's Appointments</div>
        </div>
    </div>
    <div class="stat-card">
        <div class="stat-icon amber"><i class="ph-bold ph-hourglass"></i></div>
        <div class="stat-info">
            <div class="stat-value">{{ $data['pending_appointments'] }}</div>
            <div class="stat-label">Pending Approvals</div>
        </div>
    </div>
    <div class="stat-card">
        <div class="stat-icon green"><i class="ph-bold ph-chats-circle"></i></div>
        <div class="stat-info">
            <div class="stat-value">{{ $data['sessions_this_month'] }}</div>
            <div class="stat-label">Sessions This Month</div>
        </div>
    </div>
</div>

<div class="grid-2" style="gap:20px;">
    <div class="card" style="grid-column:span 2;">
        <div class="card-header">
            <span class="card-title">Recent Appointments</span>
            <a href="{{ route('appointments.index') }}" class="btn btn-outline btn-sm">View All</a>
        </div>
        @if($data['recent_appointments']->isEmpty())
        <div class="empty-state">
            <i class="ph ph-calendar-blank"></i>
            <h3>No appointments yet</h3>
        </div>
        @else
        <div class="table-wrap">
            <table>
                <thead>
                    <tr>
                        <th>Student</th>
                        <th>Counselor</th>
                        <th>Appointment Date</th>
                        <th>Concern</th>
                        <th>Status</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($data['recent_appointments'] as $appt)
                    <tr>
                        <td>
                            <div class="flex items-center gap-2">
                                <div class="avatar">{{ $appt->student->user->initials }}</div>
                                <div>
                                    <div class="font-semibold">{{ $appt->student->user->full_name }}</div>
                                    <div class="text-sm text-muted">{{ $appt->student->student_num }}</div>
                                </div>
                            </div>
                        </td>
                        <td>{{ $appt->counselor->user->full_name }}</td>
                        <td>
                            <div>{{ $appt->appointment_datetime->format('M d, Y') }}</div>
                            <div class="text-sm text-muted">{{ $appt->appointment_datetime->format('h:i A') }}</div>
                        </td>
                        <td>{{ $appt->concern_type ?? '—' }}</td>
                        <td>
                            @php
                                $statusMap = ['pending'=>'warning','approved'=>'success','completed'=>'info','cancelled'=>'danger','no_show'=>'secondary','rescheduled'=>'primary'];
                            @endphp
                            <span class="badge badge-{{ $statusMap[$appt->appointment_status] ?? 'secondary' }}">
                                {{ ucfirst(str_replace('_', ' ', $appt->appointment_status)) }}
                            </span>
                        </td>
                        <td>
                            <div class="flex gap-2">
                                <a href="{{ route('appointments.show', $appt) }}" class="btn btn-outline btn-sm btn-icon">
                                    <i class="ph-bold ph-eye"></i>
                                </a>
                                @if($appt->isPending() && (auth()->user()->isOfficeStaff() || auth()->user()->isSystemAdmin()))
                                <form method="POST" action="{{ route('appointments.approve', $appt) }}" style="display:inline;">
                                    @csrf
                                    <button type="submit" class="btn btn-success btn-sm btn-icon" title="Approve">
                                        <i class="ph-bold ph-check"></i>
                                    </button>
                                </form>
                                @endif
                            </div>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        @endif
    </div>
</div>

<div class="card" style="margin-top:20px;">
    <div class="card-header"><span class="card-title">Appointment Status Overview</span></div>
    <div class="card-body">
        <div class="stats-grid" style="grid-template-columns:repeat(4,1fr);">
            @foreach([
                ['label'=>'Pending', 'key'=>'pending', 'badge'=>'warning', 'icon'=>'hourglass'],
                ['label'=>'Approved', 'key'=>'approved', 'badge'=>'success', 'icon'=>'check-circle'],
                ['label'=>'Completed', 'key'=>'completed', 'badge'=>'info', 'icon'=>'chats-circle'],
                ['label'=>'Cancelled', 'key'=>'cancelled', 'badge'=>'danger', 'icon'=>'x-circle'],
            ] as $stat)
            <div style="text-align:center;padding:16px;background:var(--surface);border-radius:12px;border:1px solid var(--border-light);">
                <div style="font-size:28px;font-weight:700;color:var(--text-primary);">{{ $data['appointment_stats'][$stat['key']] ?? 0 }}</div>
                <div style="font-size:12px;color:var(--text-muted);margin-top:4px;">{{ $stat['label'] }}</div>
            </div>
            @endforeach
        </div>
    </div>
</div>
@endif

@endsection

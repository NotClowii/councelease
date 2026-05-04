@extends('layouts.app')
@section('title', $counselor->user->full_name)
@section('page-title', 'Counselor Profile')
@section('content')
<div class="page-header">
    <div style="display:flex;align-items:center;gap:12px;">
        <a href="{{ route('counselors.index') }}" class="btn btn-outline btn-sm btn-icon"><i class="ph-bold ph-arrow-left"></i></a>
        <h1>Counselor Profile</h1>
    </div>
    @if(auth()->user()->isOfficeStaff() || auth()->user()->isSystemAdmin())
    <a href="{{ route('counselors.edit', $counselor) }}" class="btn btn-outline"><i class="ph-bold ph-pencil"></i> Edit</a>
    @endif
</div>
<div style="display:grid;grid-template-columns:280px 1fr;gap:20px;align-items:start;">
    <div style="display:flex;flex-direction:column;gap:16px;">
        <div class="card">
            <div class="card-body" style="text-align:center;padding:28px 20px;">
                <div class="avatar lg" style="width:72px;height:72px;font-size:24px;margin:0 auto 14px;background:var(--accent);">{{ $counselor->user->initials }}</div>
                <h2 style="font-size:17px;font-weight:700;">{{ $counselor->user->full_name }}</h2>
                <div style="font-size:13px;color:var(--text-muted);margin-top:4px;">{{ $counselor->specialization ?? 'Guidance Counselor' }}</div>
                <div style="margin-top:10px;"><span class="badge badge-success">Active</span></div>
            </div>
            <div style="padding:0 20px 20px;">
                <hr class="divider" style="margin-top:0;">
                <dl class="detail-list">
                    <dt>Email</dt><dd>{{ $counselor->user->email }}</dd>
                    <dt>Phone</dt><dd>{{ $counselor->user->phone ?? '—' }}</dd>
                    <dt>License No.</dt><dd>{{ $counselor->license_number ?? '—' }}</dd>
                    <dt>Department</dt><dd>{{ $counselor->department?->department_name ?? '—' }}</dd>
                    <dt>Available Hours</dt>
                    <dd>{{ \Carbon\Carbon::parse($counselor->available_from)->format('h:i A') }} – {{ \Carbon\Carbon::parse($counselor->available_until)->format('h:i A') }}</dd>
                    <dt>Max Appointments/Day</dt><dd>{{ $counselor->max_appointments_per_day }}</dd>
                </dl>
            </div>
        </div>
        <div class="card">
            <div class="card-header"><span class="card-title">Statistics</span></div>
            <div class="card-body">
                <div style="display:flex;flex-direction:column;gap:12px;">
                    @foreach([
                        ['label'=>'Total Sessions','value'=>$stats['total_sessions'],'icon'=>'chats-circle','color'=>'green'],
                        ['label'=>'Students Served','value'=>$stats['students_served'],'icon'=>'users','color'=>'blue'],
                        ['label'=>'Total Appointments','value'=>$stats['total_appointments'],'icon'=>'calendar','color'=>'teal'],
                        ['label'=>'Pending Approvals','value'=>$stats['pending_appointments'],'icon'=>'hourglass','color'=>'amber'],
                    ] as $s)
                    <div style="display:flex;align-items:center;gap:10px;padding:10px;background:var(--surface);border-radius:8px;">
                        <div class="stat-icon {{ $s['color'] }}" style="width:34px;height:34px;font-size:16px;"><i class="ph-bold ph-{{ $s['icon'] }}"></i></div>
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
    <div style="display:flex;flex-direction:column;gap:20px;">
        <div class="card">
            <div class="card-header">
                <span class="card-title">Recent Appointments</span>
                <a href="{{ route('appointments.index') }}" class="btn btn-outline btn-sm">View All</a>
            </div>
            @if($counselor->appointments->isEmpty())
            <div class="empty-state" style="padding:28px;"><h3>No appointments yet</h3></div>
            @else
            <div class="table-wrap">
                <table>
                    <thead><tr><th>Student</th><th>Date</th><th>Concern</th><th>Status</th></tr></thead>
                    <tbody>
                        @foreach($counselor->appointments->take(5) as $appt)
                        @php $sm=['pending'=>'warning','approved'=>'success','completed'=>'info','cancelled'=>'danger','no_show'=>'secondary','rescheduled'=>'primary']; @endphp
                        <tr>
                            <td>{{ $appt->student->user->full_name }}</td>
                            <td>{{ $appt->appointment_datetime->format('M d, Y') }}</td>
                            <td>{{ $appt->concern_type ?? '—' }}</td>
                            <td><span class="badge badge-{{ $sm[$appt->appointment_status] ?? 'secondary' }}">{{ ucfirst(str_replace('_',' ',$appt->appointment_status)) }}</span></td>
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
            @if($counselor->sessions->isEmpty())
            <div class="empty-state" style="padding:28px;"><h3>No sessions yet</h3></div>
            @else
            <div class="table-wrap">
                <table>
                    <thead><tr><th>Student</th><th>Date</th><th>Duration</th><th>Status</th></tr></thead>
                    <tbody>
                        @foreach($counselor->sessions->take(5) as $session)
                        <tr>
                            <td>{{ $session->student->user->full_name }}</td>
                            <td>{{ $session->session_datetime->format('M d, Y') }}</td>
                            <td>{{ $session->actual_duration_minutes ? $session->actual_duration_minutes . ' min' : '—' }}</td>
                            <td><span class="badge badge-{{ $session->session_status === 'completed' ? 'success' : 'info' }}">{{ ucfirst($session->session_status) }}</span></td>
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
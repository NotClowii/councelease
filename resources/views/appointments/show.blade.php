@extends('layouts.app')

@section('title', 'Appointment #' . $appointment->appointment_id)
@section('page-title', 'Appointment Details')
@section('breadcrumb', 'Appointments / #' . $appointment->appointment_id)

@section('content')
@php
    $statusMap  = ['pending'=>'warning','approved'=>'success','completed'=>'info','cancelled'=>'danger','no_show'=>'secondary','rescheduled'=>'primary'];
    $badgeClass = $statusMap[$appointment->appointment_status] ?? 'secondary';
    $user       = auth()->user();
@endphp

<div class="page-header">
    <div style="display:flex;align-items:center;gap:12px;">
        <a href="{{ route('appointments.index') }}" class="btn btn-outline btn-sm btn-icon">
            <i class="ph-bold ph-arrow-left"></i>
        </a>
        <div>
            <div style="display:flex;align-items:center;gap:10px;">
                <h1>Appointment #{{ $appointment->appointment_id }}</h1>
                <span class="badge badge-{{ $badgeClass }}" style="font-size:12px;padding:4px 12px;">
                    {{ ucfirst(str_replace('_', ' ', $appointment->appointment_status)) }}
                </span>
            </div>
            <p>Booked on {{ $appointment->created_at->format('F j, Y \a\t h:i A') }}</p>
        </div>
    </div>

    <div class="flex gap-2">
        @if($appointment->isPending() && ($user->isCounselor() || $user->isOfficeStaff() || $user->isSystemAdmin()))
        <form method="POST" action="{{ route('appointments.approve', $appointment) }}">
            @csrf
            <button type="submit" class="btn btn-success">
                <i class="ph-bold ph-check"></i> Approve
            </button>
        </form>
        @endif

        @if($appointment->isApproved() && $user->isCounselor())
        <form method="POST" action="{{ route('appointments.start-session', $appointment) }}">
            @csrf
            <button type="submit" class="btn btn-primary">
                <i class="ph-bold ph-play"></i> Start Session
            </button>
        </form>
        @endif

        @if($appointment->canBeCancelled())
        <button class="btn btn-danger" onclick="document.getElementById('cancelModal').style.display='flex'">
            <i class="ph-bold ph-x"></i> Cancel
        </button>
        @endif
    </div>
</div>

<div style="display:grid;grid-template-columns:1fr 320px;gap:20px;align-items:start;">

    {{-- Main Info --}}
    <div style="display:flex;flex-direction:column;gap:20px;">

        {{-- Appointment Info --}}
        <div class="card">
            <div class="card-header"><span class="card-title">Appointment Information</span></div>
            <div class="card-body">
                <div class="grid-2">
                    <dl class="detail-list">
                        <dt>Date & Time</dt>
                        <dd>
                            <div style="font-size:16px;font-weight:700;">{{ $appointment->appointment_datetime->format('F j, Y') }}</div>
                            <div style="color:var(--text-muted);">{{ $appointment->appointment_datetime->format('h:i A') }} ({{ $appointment->duration_minutes }} min)</div>
                        </dd>

                        <dt>Concern Type</dt>
                        <dd>{{ $appointment->concern_type ?? '—' }}</dd>

                        <dt>Status</dt>
                        <dd>
                            <span class="badge badge-{{ $badgeClass }}">
                                {{ ucfirst(str_replace('_', ' ', $appointment->appointment_status)) }}
                            </span>
                        </dd>
                    </dl>

                    <dl class="detail-list">
                        @if($appointment->approved_at)
                        <dt>Approved By</dt>
                        <dd>{{ $appointment->approvedByUser?->full_name ?? '—' }} <span class="text-muted text-sm">on {{ $appointment->approved_at->format('M d, Y') }}</span></dd>
                        @endif

                        @if($appointment->cancelled_at)
                        <dt>Cancelled At</dt>
                        <dd>{{ $appointment->cancelled_at->format('M d, Y h:i A') }}</dd>
                        <dt>Cancellation Reason</dt>
                        <dd>{{ $appointment->cancellation_reason }}</dd>
                        @endif

                        <dt>Time Until / Elapsed</dt>
                        <dd>{{ $appointment->appointment_datetime->diffForHumans() }}</dd>
                    </dl>
                </div>

                @if($appointment->concern_description)
                <hr class="divider">
                <dl class="detail-list">
                    <dt>Concern Description</dt>
                    <dd style="background:var(--surface);padding:12px;border-radius:8px;border:1px solid var(--border-light);font-size:13.5px;line-height:1.7;">
                        {{ $appointment->concern_description }}
                    </dd>
                </dl>
                @endif
            </div>
        </div>

        {{-- Session Info (if exists) --}}
        @if($appointment->session)
        <div class="card">
            <div class="card-header">
                <span class="card-title">Linked Session</span>
                <a href="{{ route('sessions.show', $appointment->session) }}" class="btn btn-outline btn-sm">
                    <i class="ph-bold ph-arrow-right"></i> View Session
                </a>
            </div>
            <div class="card-body">
                <div class="grid-2">
                    <dl class="detail-list">
                        <dt>Session Date</dt>
                        <dd>{{ $appointment->session->session_datetime->format('F j, Y h:i A') }}</dd>
                        <dt>Session Status</dt>
                        <dd>
                            <span class="badge badge-{{ $appointment->session->session_status === 'completed' ? 'success' : 'info' }}">
                                {{ ucfirst($appointment->session->session_status) }}
                            </span>
                        </dd>
                    </dl>
                    <dl class="detail-list">
                        @if($appointment->session->actual_duration_minutes)
                        <dt>Actual Duration</dt>
                        <dd>{{ $appointment->session->actual_duration_minutes }} minutes</dd>
                        @endif
                        <dt>Case Notes</dt>
                        <dd>{{ $appointment->session->caseNotes->count() }} note(s)</dd>
                    </dl>
                </div>
                @if($appointment->session->session_summary)
                <dl class="detail-list">
                    <dt>Session Summary</dt>
                    <dd style="background:var(--surface);padding:12px;border-radius:8px;border:1px solid var(--border-light);">
                        {{ $appointment->session->session_summary }}
                    </dd>
                </dl>
                @endif
            </div>
        </div>
        @endif

    </div>

    {{-- Sidebar --}}
    <div style="display:flex;flex-direction:column;gap:16px;">

        {{-- Student Card --}}
        <div class="card">
            <div class="card-header"><span class="card-title">Student</span></div>
            <div class="card-body" style="text-align:center;padding-top:24px;padding-bottom:24px;">
                <div class="avatar lg" style="margin:0 auto 12px;">{{ $appointment->student->user->initials }}</div>
                <div style="font-size:15px;font-weight:700;">{{ $appointment->student->user->full_name }}</div>
                <div style="font-size:12px;color:var(--text-muted);margin-top:3px;">{{ $appointment->student->student_num }}</div>
                <div style="font-size:12px;color:var(--text-muted);">{{ $appointment->student->course }} · {{ $appointment->student->year_level_label }}</div>
                <a href="{{ route('students.show', $appointment->student) }}" class="btn btn-outline btn-sm" style="margin-top:14px;">
                    View Profile
                </a>
            </div>
        </div>

        {{-- Counselor Card --}}
        <div class="card">
            <div class="card-header"><span class="card-title">Counselor</span></div>
            <div class="card-body" style="text-align:center;padding-top:24px;padding-bottom:24px;">
                <div class="avatar lg" style="margin:0 auto 12px;background:var(--accent);">{{ $appointment->counselor->user->initials }}</div>
                <div style="font-size:15px;font-weight:700;">{{ $appointment->counselor->user->full_name }}</div>
                <div style="font-size:12px;color:var(--text-muted);margin-top:3px;">{{ $appointment->counselor->specialization ?? 'Guidance Counselor' }}</div>
                <div style="font-size:12px;color:var(--text-muted);">{{ $appointment->counselor->department?->department_name }}</div>
            </div>
        </div>
    </div>
</div>

{{-- Cancel Modal --}}
<div id="cancelModal" style="display:none;position:fixed;inset:0;z-index:300;background:rgba(0,0,0,.4);align-items:center;justify-content:center;">
    <div style="background:white;border-radius:16px;padding:28px;width:100%;max-width:440px;margin:20px;box-shadow:0 20px 60px rgba(0,0,0,.2);">
        <div style="display:flex;align-items:center;gap:10px;margin-bottom:16px;">
            <div style="width:40px;height:40px;background:#FFF5F5;border-radius:10px;display:flex;align-items:center;justify-content:center;">
                <i class="ph-bold ph-warning" style="font-size:20px;color:var(--danger);"></i>
            </div>
            <h3 style="font-size:16px;font-weight:700;">Cancel Appointment</h3>
        </div>
        <p style="font-size:13.5px;color:var(--text-muted);margin-bottom:18px;">Are you sure you want to cancel this appointment? Please provide a reason.</p>
        <form method="POST" action="{{ route('appointments.cancel', $appointment) }}">
            @csrf
            <div class="form-group">
                <label>Cancellation Reason <span style="color:var(--danger)">*</span></label>
                <textarea name="cancellation_reason" class="form-control" rows="3" required placeholder="Enter reason..."></textarea>
            </div>
            <div class="flex gap-2" style="justify-content:flex-end;margin-top:8px;">
                <button type="button" class="btn btn-outline" onclick="document.getElementById('cancelModal').style.display='none'">Keep It</button>
                <button type="submit" class="btn btn-danger">Confirm Cancel</button>
            </div>
        </form>
    </div>
</div>

@endsection

@extends('layouts.app')

@section('title', 'Session #' . $session->session_id)
@section('page-title', 'Session Details')
@section('breadcrumb', 'Sessions / #' . $session->session_id)

@section('content')
@php $user = auth()->user(); @endphp

<div class="page-header">
    <div style="display:flex;align-items:center;gap:12px;">
        <a href="{{ route('sessions.index') }}" class="btn btn-outline btn-sm btn-icon">
            <i class="ph-bold ph-arrow-left"></i>
        </a>
        <div>
            <div style="display:flex;align-items:center;gap:10px;">
                <h1>Session #{{ $session->session_id }}</h1>
                <span class="badge badge-{{ $session->session_status === 'completed' ? 'success' : ($session->session_status === 'ongoing' ? 'info' : 'danger') }}">
                    {{ ucfirst($session->session_status) }}
                </span>
            </div>
            <p>{{ $session->session_datetime->format('F j, Y \a\t h:i A') }}</p>
        </div>
    </div>
    <div class="flex gap-2">
        @if($session->isOngoing() && $user->isCounselor())
        <button class="btn btn-success" onclick="document.getElementById('completeModal').style.display='flex'">
            <i class="ph-bold ph-check"></i> Complete Session
        </button>
        @endif
        @if($session->isCompleted() && $user->isCounselor())
        <a href="{{ route('case-notes.create') }}?session={{ $session->session_id }}" class="btn btn-primary">
            <i class="ph-bold ph-plus"></i> Add Case Note
        </a>
        @endif
    </div>
</div>

<div style="display:grid;grid-template-columns:1fr 300px;gap:20px;align-items:start;">

    <div style="display:flex;flex-direction:column;gap:20px;">
        <div class="card">
            <div class="card-header"><span class="card-title">Session Information</span></div>
            <div class="card-body">
                <div class="grid-2">
                    <dl class="detail-list">
                        <dt>Session Date</dt>
                        <dd>{{ $session->session_datetime->format('F j, Y h:i A') }}</dd>
                        <dt>Session Type</dt>
                        <dd style="text-transform:capitalize;">{{ str_replace('_', ' ', $session->session_type) }}</dd>
                        <dt>Status</dt>
                        <dd>
                            <span class="badge badge-{{ $session->session_status === 'completed' ? 'success' : 'info' }}">
                                {{ ucfirst($session->session_status) }}
                            </span>
                        </dd>
                    </dl>
                    <dl class="detail-list">
                        @if($session->actual_duration_minutes)
                        <dt>Actual Duration</dt>
                        <dd>{{ $session->actual_duration_minutes }} minutes</dd>
                        @endif
                        @if($session->ended_at)
                        <dt>Ended At</dt>
                        <dd>{{ $session->ended_at->format('h:i A') }}</dd>
                        @endif
                        <dt>Linked Appointment</dt>
                        <dd>
                            <a href="{{ route('appointments.show', $session->appointment) }}" style="color:var(--primary);font-weight:600;">
                                #{{ $session->appointment_id }}
                                <i class="ph-bold ph-arrow-square-out" style="font-size:12px;"></i>
                            </a>
                        </dd>
                    </dl>
                </div>

                @if($session->session_summary)
                <hr class="divider">
                <dl class="detail-list">
                    <dt>Session Summary</dt>
                    <dd style="background:var(--surface);padding:14px;border-radius:10px;border:1px solid var(--border-light);line-height:1.7;">
                        {{ $session->session_summary }}
                    </dd>
                </dl>
                @endif
            </div>
        </div>

        {{-- Case Notes (Counselor/Admin only) --}}
        @if(!$user->isStudent())
        <div class="card">
            <div class="card-header">
                <span class="card-title">
                    Case Notes
                    <span class="badge badge-secondary" style="margin-left:6px;">{{ $caseNotes->count() }}</span>
                </span>
                @if($session->isCompleted() && $user->isCounselor())
                <a href="{{ route('case-notes.create') }}?session={{ $session->session_id }}" class="btn btn-outline btn-sm">
                    <i class="ph-bold ph-plus"></i> Add Note
                </a>
                @endif
            </div>

            @if($caseNotes->isEmpty())
            <div class="empty-state">
                <i class="ph ph-notebook"></i>
                <h3>No case notes yet</h3>
                @if($user->isCounselor() && $session->isCompleted())
                <p>Add a case note to document this session.</p>
                @elseif($session->isOngoing())
                <p>Case notes can be added after the session is completed.</p>
                @endif
            </div>
            @else
            <div style="padding:16px;display:flex;flex-direction:column;gap:14px;">
                @foreach($caseNotes as $note)
                <div style="background:var(--surface);border:1px solid var(--border-light);border-radius:12px;padding:16px;">
                    <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:10px;">
                        <div style="display:flex;align-items:center;gap:8px;">
                            <span class="badge badge-secondary" style="text-transform:capitalize;">{{ $note->note_type }}</span>
                            @if($note->is_confidential)
                            <span class="badge" style="background:#FFF5F5;color:var(--danger);border:1px solid #FED7D7;">
                                <i class="ph-bold ph-lock-key"></i> Confidential
                            </span>
                            @endif
                        </div>
                        <div style="display:flex;align-items:center;gap:8px;">
                            <span class="text-sm text-muted">{{ $note->created_at->format('M d, Y h:i A') }}</span>
                            @if($user->isCounselor() && $note->counselor_id === $user->counselor?->counselor_id)
                            <a href="{{ route('case-notes.edit', $note) }}" class="btn btn-outline btn-sm btn-icon">
                                <i class="ph-bold ph-pencil"></i>
                            </a>
                            @endif
                        </div>
                    </div>

                    <p style="font-size:13.5px;line-height:1.7;color:var(--text-primary);">{{ $note->notes_content }}</p>

                    @if($note->interventions_used)
                    <div style="margin-top:10px;">
                        <div style="font-size:11px;font-weight:600;color:var(--text-muted);text-transform:uppercase;letter-spacing:.4px;margin-bottom:6px;">Interventions Used</div>
                        <div class="flex gap-2" style="flex-wrap:wrap;">
                            @foreach($note->interventions_used as $intervention)
                            <span class="badge badge-secondary">{{ $intervention }}</span>
                            @endforeach
                        </div>
                    </div>
                    @endif

                    @if($note->follow_up_actions)
                    <div style="margin-top:10px;padding:10px 12px;background:white;border-radius:8px;border:1px solid var(--border-light);">
                        <div style="font-size:11px;font-weight:600;color:var(--text-muted);text-transform:uppercase;letter-spacing:.4px;margin-bottom:4px;">Follow-up Actions</div>
                        <p style="font-size:13px;">{{ $note->follow_up_actions }}</p>
                    </div>
                    @endif

                    @if($note->next_session_recommended)
                    <div style="margin-top:10px;padding:8px 12px;background:#F0FFF4;border-radius:8px;border:1px solid #9AE6B4;font-size:13px;color:#276749;">
                        <i class="ph-bold ph-calendar"></i>
                        Next session recommended: <strong>{{ $note->next_session_recommended->format('F j, Y') }}</strong>
                    </div>
                    @endif

                    <div style="margin-top:10px;font-size:12px;color:var(--text-muted);">
                        Written by: {{ $note->counselor->user->full_name }}
                    </div>
                </div>
                @endforeach
            </div>
            @endif
        </div>
        @else
        {{-- Student sees a note about confidentiality --}}
        <div class="card">
            <div class="card-body" style="text-align:center;padding:32px;">
                <i class="ph ph-lock-key" style="font-size:40px;color:var(--border);display:block;margin-bottom:10px;"></i>
                <h3 style="font-size:15px;font-weight:600;color:var(--text-secondary);">Case Notes are Confidential</h3>
                <p style="font-size:13px;color:var(--text-muted);margin-top:6px;">Case notes written by your counselor are private and not visible to students.</p>
            </div>
        </div>
        @endif
    </div>

    {{-- Sidebar --}}
    <div style="display:flex;flex-direction:column;gap:16px;">
        <div class="card">
            <div class="card-header"><span class="card-title">Student</span></div>
            <div class="card-body" style="text-align:center;padding:20px;">
                <div class="avatar lg" style="margin:0 auto 10px;">{{ $session->student->user->initials }}</div>
                <div style="font-size:14px;font-weight:700;">{{ $session->student->user->full_name }}</div>
                <div style="font-size:12px;color:var(--text-muted);">{{ $session->student->student_num }}</div>
                <div style="font-size:12px;color:var(--text-muted);">{{ $session->student->course }}</div>
                <a href="{{ route('students.show', $session->student) }}" class="btn btn-outline btn-sm" style="margin-top:12px;">View Profile</a>
            </div>
        </div>

        <div class="card">
            <div class="card-header"><span class="card-title">Counselor</span></div>
            <div class="card-body" style="text-align:center;padding:20px;">
                <div class="avatar lg" style="margin:0 auto 10px;background:var(--accent);">{{ $session->counselor->user->initials }}</div>
                <div style="font-size:14px;font-weight:700;">{{ $session->counselor->user->full_name }}</div>
                <div style="font-size:12px;color:var(--text-muted);">{{ $session->counselor->specialization ?? 'Guidance Counselor' }}</div>
            </div>
        </div>
    </div>
</div>

{{-- Complete Modal --}}
<div id="completeModal" style="display:none;position:fixed;inset:0;z-index:300;background:rgba(0,0,0,.4);align-items:center;justify-content:center;">
    <div style="background:white;border-radius:16px;padding:28px;width:100%;max-width:480px;margin:20px;box-shadow:0 20px 60px rgba(0,0,0,.2);">
        <h3 style="font-size:16px;font-weight:700;margin-bottom:18px;">Complete Session</h3>
        <form method="POST" action="{{ route('sessions.complete', $session) }}">
            @csrf
            <div class="form-group">
                <label>Actual Duration (minutes) *</label>
                <input type="number" name="actual_duration_minutes" class="form-control" min="1" max="480" value="60" required>
            </div>
            <div class="form-group">
                <label>Session Summary</label>
                <textarea name="session_summary" class="form-control" rows="3" placeholder="Brief overview..."></textarea>
            </div>
            <div class="flex gap-2" style="justify-content:flex-end;">
                <button type="button" class="btn btn-outline" onclick="document.getElementById('completeModal').style.display='none'">Cancel</button>
                <button type="submit" class="btn btn-success"><i class="ph-bold ph-check"></i> Mark Complete</button>
            </div>
        </form>
    </div>
</div>

@endsection

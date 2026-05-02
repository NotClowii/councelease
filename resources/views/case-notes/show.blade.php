@extends('layouts.app')

@section('title', 'Case Note #' . $caseNote->note_id)
@section('page-title', 'Case Note Detail')

@section('content')
<div class="page-header">
    <div style="display:flex;align-items:center;gap:12px;">
        <a href="{{ route('case-notes.index') }}" class="btn btn-outline btn-sm btn-icon">
            <i class="ph-bold ph-arrow-left"></i>
        </a>
        <div>
            <div style="display:flex;align-items:center;gap:10px;">
                <h1>Case Note #{{ $caseNote->note_id }}</h1>
                <span class="badge" style="background:#FFF5F5;color:var(--danger);border:1px solid #FED7D7;">
                    <i class="ph-bold ph-lock-key"></i> Confidential
                </span>
            </div>
            <p>Created {{ $caseNote->created_at->format('F j, Y \a\t h:i A') }}</p>
        </div>
    </div>
    @if(auth()->user()->isCounselor() && $caseNote->counselor_id === auth()->user()->counselor?->counselor_id)
    <a href="{{ route('case-notes.edit', $caseNote) }}" class="btn btn-outline">
        <i class="ph-bold ph-pencil"></i> Edit
    </a>
    @endif
</div>

<div style="display:grid;grid-template-columns:1fr 280px;gap:20px;">
    <div class="card">
        <div class="card-header">
            <span class="card-title" style="text-transform:capitalize;">{{ $caseNote->note_type }} Note</span>
        </div>
        <div class="card-body">
            <div style="background:var(--surface);border:1px solid var(--border-light);border-radius:10px;padding:18px;line-height:1.8;font-size:14px;white-space:pre-wrap;">{{ $caseNote->notes_content }}</div>

            @if($caseNote->interventions_used)
            <div style="margin-top:18px;">
                <div style="font-size:11px;font-weight:700;letter-spacing:.5px;text-transform:uppercase;color:var(--text-muted);margin-bottom:8px;">Interventions Used</div>
                <div class="flex gap-2" style="flex-wrap:wrap;">
                    @foreach($caseNote->interventions_used as $i)
                    <span class="badge badge-secondary">{{ $i }}</span>
                    @endforeach
                </div>
            </div>
            @endif

            @if($caseNote->follow_up_actions)
            <div style="margin-top:18px;padding:14px;background:var(--info-light);border-radius:10px;border:1px solid #90CDF4;">
                <div style="font-size:11px;font-weight:700;letter-spacing:.5px;text-transform:uppercase;color:var(--info);margin-bottom:6px;">Follow-up Actions</div>
                <p style="font-size:13.5px;color:#2A4365;">{{ $caseNote->follow_up_actions }}</p>
            </div>
            @endif

            @if($caseNote->next_session_recommended)
            <div style="margin-top:14px;padding:12px;background:var(--success-light);border-radius:10px;border:1px solid #9AE6B4;font-size:13px;color:#276749;">
                <i class="ph-bold ph-calendar"></i>
                Next session recommended: <strong>{{ $caseNote->next_session_recommended->format('F j, Y') }}</strong>
            </div>
            @endif
        </div>
    </div>

    <div style="display:flex;flex-direction:column;gap:16px;">
        <div class="card">
            <div class="card-header"><span class="card-title">Session Info</span></div>
            <div class="card-body">
                <dl class="detail-list">
                    <dt>Student</dt>
                    <dd>{{ $caseNote->session->student->user->full_name }}</dd>
                    <dt>Session Date</dt>
                    <dd>{{ $caseNote->session->session_datetime->format('M d, Y') }}</dd>
                    <dt>Counselor</dt>
                    <dd>{{ $caseNote->counselor->user->full_name }}</dd>
                    <dt>Session</dt>
                    <dd><a href="{{ route('sessions.show', $caseNote->session) }}" style="color:var(--primary);font-weight:600;">#{{ $caseNote->session_id }}</a></dd>
                </dl>
            </div>
        </div>
    </div>
</div>
@endsection

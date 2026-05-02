@extends('layouts.app')

@section('title', 'Add Case Note')
@section('page-title', 'Add Case Note')
@section('breadcrumb', 'Case Notes / New')

@section('content')
<div class="page-header">
    <div>
        <h1>Add Case Note</h1>
        <p>Document your counseling session — this note is strictly confidential</p>
    </div>
    <a href="{{ route('sessions.show', $session) }}" class="btn btn-outline">
        <i class="ph-bold ph-arrow-left"></i> Back to Session
    </a>
</div>

<div style="display:grid;grid-template-columns:1fr 280px;gap:24px;align-items:start;">
    <div class="card">
        <div class="card-header">
            <span class="card-title">Case Note Details</span>
            <span class="badge" style="background:#FFF5F5;color:var(--danger);border:1px solid #FED7D7;">
                <i class="ph-bold ph-lock-key"></i> Confidential
            </span>
        </div>
        <div class="card-body">
            <form method="POST" action="{{ route('case-notes.store') }}">
                @csrf
                <input type="hidden" name="session_id" value="{{ $session->session_id }}">

                <div class="form-group">
                    <label>Note Type <span style="color:var(--danger)">*</span></label>
                    <select name="note_type" class="form-control" required>
                        <option value="initial"     {{ old('note_type') === 'initial'     ? 'selected' : '' }}>Initial Assessment</option>
                        <option value="progress"    {{ old('note_type') === 'progress'    ? 'selected' : '' }}>Progress Note</option>
                        <option value="termination" {{ old('note_type') === 'termination' ? 'selected' : '' }}>Termination/Closure</option>
                        <option value="referral"    {{ old('note_type') === 'referral'    ? 'selected' : '' }}>Referral Note</option>
                    </select>
                </div>

                <div class="form-group">
                    <label>Session Notes <span style="color:var(--danger)">*</span></label>
                    <textarea name="notes_content" class="form-control" rows="8" required
                        placeholder="Document the session: presenting concern, student's affect, observations, what was discussed, progress made, etc.">{{ old('notes_content') }}</textarea>
                    <div class="form-hint">Write clear, objective, and professional notes. Avoid subjective language.</div>
                    @error('notes_content') <div class="field-error">{{ $message }}</div> @enderror
                </div>

                <div class="form-group">
                    <label>Interventions / Techniques Used</label>
                    <div style="display:flex;flex-wrap:wrap;gap:8px;padding:12px;border:1.5px solid var(--border);border-radius:8px;">
                        @foreach(['Active Listening','CBT','Motivational Interviewing','Psychoeducation','Problem Solving','Crisis Intervention','Referral','Relaxation Techniques','Behavior Modification','Career Counseling'] as $intervention)
                        <label style="display:flex;align-items:center;gap:5px;font-weight:400;font-size:13px;cursor:pointer;margin-bottom:0;">
                            <input type="checkbox" name="interventions_used[]" value="{{ $intervention }}"
                                {{ in_array($intervention, old('interventions_used', [])) ? 'checked' : '' }}>
                            {{ $intervention }}
                        </label>
                        @endforeach
                    </div>
                </div>

                <div class="grid-2">
                    <div class="form-group">
                        <label>Follow-up Actions</label>
                        <textarea name="follow_up_actions" class="form-control" rows="3"
                            placeholder="Actions to be taken before the next session...">{{ old('follow_up_actions') }}</textarea>
                    </div>

                    <div class="form-group">
                        <label>Recommended Next Session Date</label>
                        <input type="date" name="next_session_recommended" class="form-control"
                            value="{{ old('next_session_recommended') }}"
                            min="{{ now()->addDay()->format('Y-m-d') }}">
                        <div class="form-hint">Leave blank if no follow-up needed</div>
                    </div>
                </div>

                <div class="flex gap-2" style="margin-top:8px;">
                    <button type="submit" class="btn btn-primary">
                        <i class="ph-bold ph-floppy-disk"></i> Save Case Note
                    </button>
                    <a href="{{ route('sessions.show', $session) }}" class="btn btn-outline">Cancel</a>
                </div>
            </form>
        </div>
    </div>

    {{-- Sidebar --}}
    <div style="display:flex;flex-direction:column;gap:16px;">
        <div class="card">
            <div class="card-header"><span class="card-title">Session Info</span></div>
            <div class="card-body" style="font-size:13px;">
                <dl class="detail-list">
                    <dt>Student</dt>
                    <dd style="display:flex;align-items:center;gap:8px;">
                        <div class="avatar" style="width:28px;height:28px;font-size:10px;">{{ $session->student->user->initials }}</div>
                        {{ $session->student->user->full_name }}
                    </dd>
                    <dt>Date</dt>
                    <dd>{{ $session->session_datetime->format('M d, Y h:i A') }}</dd>
                    <dt>Duration</dt>
                    <dd>{{ $session->actual_duration_minutes ? $session->actual_duration_minutes . ' min' : 'N/A' }}</dd>
                    <dt>Concern</dt>
                    <dd>{{ $session->appointment?->concern_type ?? '—' }}</dd>
                </dl>
            </div>
        </div>

        <div class="card" style="border-left:3px solid var(--primary-light);">
            <div class="card-body" style="font-size:13px;color:var(--text-secondary);">
                <div style="font-weight:700;color:var(--text-primary);margin-bottom:8px;">
                    <i class="ph-bold ph-shield-check" style="color:var(--primary-light);"></i>
                    Confidentiality Reminder
                </div>
                <p style="line-height:1.7;">These case notes are strictly confidential and can only be viewed by you and the System Administrator. Never include identifying information that is not relevant to the case.</p>
            </div>
        </div>
    </div>
</div>

@endsection

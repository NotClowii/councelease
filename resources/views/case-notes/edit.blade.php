@extends('layouts.app')

@section('title', 'Edit Case Note #' . $caseNote->note_id)
@section('page-title', 'Edit Case Note')

@section('content')
<div class="page-header">
    <div>
        <h1>Edit Case Note #{{ $caseNote->note_id }}</h1>
        <p>Update the counseling session documentation</p>
    </div>
    <a href="{{ route('case-notes.show', $caseNote) }}" class="btn btn-outline">
        <i class="ph-bold ph-arrow-left"></i> Back
    </a>
</div>

<div class="card" style="max-width:800px;">
    <div class="card-header">
        <span class="card-title">Note Details</span>
        <span class="badge" style="background:#FFF5F5;color:var(--danger);border:1px solid #FED7D7;">
            <i class="ph-bold ph-lock-key"></i> Confidential
        </span>
    </div>
    <div class="card-body">
        <form method="POST" action="{{ route('case-notes.update', $caseNote) }}">
            @csrf
            @method('PUT')

            <div class="form-group">
                <label>Note Type *</label>
                <select name="note_type" class="form-control" required>
                    @foreach(['initial'=>'Initial Assessment','progress'=>'Progress Note','termination'=>'Termination/Closure','referral'=>'Referral Note'] as $val=>$label)
                    <option value="{{ $val }}" {{ old('note_type', $caseNote->note_type) === $val ? 'selected' : '' }}>{{ $label }}</option>
                    @endforeach
                </select>
            </div>

            <div class="form-group">
                <label>Session Notes *</label>
                <textarea name="notes_content" class="form-control" rows="8" required>{{ old('notes_content', $caseNote->notes_content) }}</textarea>
            </div>

            <div class="form-group">
                <label>Interventions Used</label>
                <div style="display:flex;flex-wrap:wrap;gap:8px;padding:12px;border:1.5px solid var(--border);border-radius:8px;">
                    @foreach(['Active Listening','CBT','Motivational Interviewing','Psychoeducation','Problem Solving','Crisis Intervention','Referral','Relaxation Techniques','Behavior Modification','Career Counseling'] as $intervention)
                    <label style="display:flex;align-items:center;gap:5px;font-weight:400;font-size:13px;cursor:pointer;margin-bottom:0;">
                        <input type="checkbox" name="interventions_used[]" value="{{ $intervention }}"
                            {{ in_array($intervention, old('interventions_used', $caseNote->interventions_used ?? [])) ? 'checked' : '' }}>
                        {{ $intervention }}
                    </label>
                    @endforeach
                </div>
            </div>

            <div class="grid-2">
                <div class="form-group">
                    <label>Follow-up Actions</label>
                    <textarea name="follow_up_actions" class="form-control" rows="3">{{ old('follow_up_actions', $caseNote->follow_up_actions) }}</textarea>
                </div>
                <div class="form-group">
                    <label>Recommended Next Session</label>
                    <input type="date" name="next_session_recommended" class="form-control"
                        value="{{ old('next_session_recommended', $caseNote->next_session_recommended?->format('Y-m-d')) }}">
                </div>
            </div>

            <div class="flex gap-2" style="margin-top:8px;">
                <button type="submit" class="btn btn-primary">
                    <i class="ph-bold ph-floppy-disk"></i> Update Note
                </button>
                <a href="{{ route('case-notes.show', $caseNote) }}" class="btn btn-outline">Cancel</a>
            </div>
        </form>
    </div>
</div>
@endsection

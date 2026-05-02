@extends('layouts.app')

@section('title', 'Book Appointment')
@section('page-title', 'Book Appointment')
@section('breadcrumb', 'Appointments / New')

@section('content')
<div class="page-header">
    <div>
        <h1>Book an Appointment</h1>
        <p>Request a counseling session with one of our guidance counselors</p>
    </div>
    <a href="{{ route('appointments.index') }}" class="btn btn-outline">
        <i class="ph-bold ph-arrow-left"></i> Back
    </a>
</div>

<div style="display:grid;grid-template-columns:1fr 340px;gap:24px;align-items:start;">

    <div class="card">
        <div class="card-header">
            <span class="card-title">Appointment Details</span>
        </div>
        <div class="card-body">
            <form method="POST" action="{{ route('appointments.store') }}">
                @csrf

                <div class="form-group">
                    <label for="counselor_id">Select Counselor <span style="color:var(--danger)">*</span></label>
                    <select name="counselor_id" id="counselor_id" class="form-control {{ $errors->has('counselor_id') ? 'is-invalid' : '' }}" required onchange="updateCounselorInfo(this)">
                        <option value="">— Choose a counselor —</option>
                        @foreach($counselors as $c)
                        <option value="{{ $c->counselor_id }}"
                            data-dept="{{ $c->department?->department_name ?? 'N/A' }}"
                            data-spec="{{ $c->specialization ?? 'General Counseling' }}"
                            data-avail="{{ implode(', ', array_map(fn($d) => ['Sun','Mon','Tue','Wed','Thu','Fri','Sat'][$d] ?? '', $c->available_days ?? [1,2,3,4,5])) }}"
                            data-from="{{ \Carbon\Carbon::parse($c->available_from)->format('h:i A') }}"
                            data-until="{{ \Carbon\Carbon::parse($c->available_until)->format('h:i A') }}"
                            {{ old('counselor_id') == $c->counselor_id ? 'selected' : '' }}>
                            {{ $c->user->full_name }}
                        </option>
                        @endforeach
                    </select>
                    @error('counselor_id') <div class="field-error"><i class="ph-bold ph-warning-circle"></i>{{ $message }}</div> @enderror
                </div>

                {{-- Counselor Info Panel --}}
                <div id="counselorInfo" style="display:none;background:var(--surface-2);border:1px solid var(--border-light);border-radius:10px;padding:14px;margin-bottom:18px;">
                    <div style="display:grid;grid-template-columns:1fr 1fr;gap:10px;font-size:13px;">
                        <div>
                            <div style="font-size:11px;font-weight:600;color:var(--text-muted);text-transform:uppercase;letter-spacing:.4px;margin-bottom:3px;">Department</div>
                            <div id="ci-dept" style="font-weight:500;"></div>
                        </div>
                        <div>
                            <div style="font-size:11px;font-weight:600;color:var(--text-muted);text-transform:uppercase;letter-spacing:.4px;margin-bottom:3px;">Specialization</div>
                            <div id="ci-spec" style="font-weight:500;"></div>
                        </div>
                        <div>
                            <div style="font-size:11px;font-weight:600;color:var(--text-muted);text-transform:uppercase;letter-spacing:.4px;margin-bottom:3px;">Available Days</div>
                            <div id="ci-avail" style="font-weight:500;"></div>
                        </div>
                        <div>
                            <div style="font-size:11px;font-weight:600;color:var(--text-muted);text-transform:uppercase;letter-spacing:.4px;margin-bottom:3px;">Hours</div>
                            <div id="ci-hours" style="font-weight:500;"></div>
                        </div>
                    </div>
                </div>

                <div class="form-group">
                    <label for="appointment_datetime">Preferred Date & Time <span style="color:var(--danger)">*</span></label>
                    <input type="datetime-local"
                        id="appointment_datetime"
                        name="appointment_datetime"
                        class="form-control {{ $errors->has('appointment_datetime') ? 'is-invalid' : '' }}"
                        value="{{ old('appointment_datetime') }}"
                        min="{{ now()->addHours(1)->format('Y-m-d\TH:i') }}"
                        required>
                    <div class="form-hint">Select a date and time within the counselor's available schedule</div>
                    @error('appointment_datetime') <div class="field-error"><i class="ph-bold ph-warning-circle"></i>{{ $message }}</div> @enderror
                </div>

                <div class="form-group">
                    <label for="concern_type">Concern Type <span style="color:var(--danger)">*</span></label>
                    <select name="concern_type" id="concern_type" class="form-control {{ $errors->has('concern_type') ? 'is-invalid' : '' }}" required>
                        <option value="">— Select concern type —</option>
                        @foreach(['Academic','Personal/Social','Career & Future Planning','Behavioral','Family Issues','Mental Health & Wellness','Financial Concerns','Peer Relationships','Stress Management','Other'] as $ct)
                        <option value="{{ $ct }}" {{ old('concern_type') === $ct ? 'selected' : '' }}>{{ $ct }}</option>
                        @endforeach
                    </select>
                    @error('concern_type') <div class="field-error"><i class="ph-bold ph-warning-circle"></i>{{ $message }}</div> @enderror
                </div>

                <div class="form-group">
                    <label for="concern_description">Brief Description <span style="color:var(--text-muted);font-weight:400;">(optional)</span></label>
                    <textarea
                        name="concern_description"
                        id="concern_description"
                        class="form-control"
                        rows="4"
                        placeholder="Briefly describe what you'd like to discuss. This helps your counselor prepare for the session."
                        maxlength="1000">{{ old('concern_description') }}</textarea>
                    <div class="form-hint">Maximum 1000 characters. This is visible only to your assigned counselor.</div>
                </div>

                <div style="padding:14px;background:#FFFFF0;border:1px solid #FAF089;border-radius:10px;margin-bottom:20px;">
                    <div style="display:flex;gap:8px;font-size:13px;color:#744210;">
                        <i class="ph-bold ph-info" style="font-size:16px;flex-shrink:0;margin-top:1px;"></i>
                        <div>
                            <strong>Important:</strong> Your appointment request will be reviewed and approved by the guidance office or your assigned counselor. You will be notified once it's approved.
                        </div>
                    </div>
                </div>

                <div class="flex gap-2">
                    <button type="submit" class="btn btn-primary">
                        <i class="ph-bold ph-calendar-plus"></i>
                        Submit Request
                    </button>
                    <a href="{{ route('appointments.index') }}" class="btn btn-outline">Cancel</a>
                </div>
            </form>
        </div>
    </div>

    {{-- Sidebar Info --}}
    <div style="display:flex;flex-direction:column;gap:16px;">
        <div class="card">
            <div class="card-header"><span class="card-title">How It Works</span></div>
            <div class="card-body">
                <div class="timeline">
                    <div class="timeline-item">
                        <div class="timeline-dot"></div>
                        <div class="timeline-content">
                            <div class="timeline-title">Submit Request</div>
                            <div class="timeline-time">Fill out this form and choose your preferred schedule</div>
                        </div>
                    </div>
                    <div class="timeline-item">
                        <div class="timeline-dot"></div>
                        <div class="timeline-content">
                            <div class="timeline-title">Await Approval</div>
                            <div class="timeline-time">The counselor or guidance office will review your request</div>
                        </div>
                    </div>
                    <div class="timeline-item">
                        <div class="timeline-dot"></div>
                        <div class="timeline-content">
                            <div class="timeline-title">Get Notified</div>
                            <div class="timeline-time">You'll receive a notification once your appointment is approved</div>
                        </div>
                    </div>
                    <div class="timeline-item">
                        <div class="timeline-dot" style="background:var(--success);box-shadow:0 0 0 2px var(--success);"></div>
                        <div class="timeline-content">
                            <div class="timeline-title">Attend Session</div>
                            <div class="timeline-time">Show up on time for your counseling session</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="card">
            <div class="card-header"><span class="card-title">Reminders</span></div>
            <div class="card-body" style="font-size:13px;color:var(--text-secondary);">
                <ul style="padding-left:16px;line-height:2;">
                    <li>You may cancel up to 1 hour before your appointment</li>
                    <li>Sessions are typically 60 minutes long</li>
                    <li>Your information is kept strictly confidential</li>
                    <li>For urgent concerns, visit the guidance office directly</li>
                </ul>
            </div>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script>
function updateCounselorInfo(select) {
    const opt = select.options[select.selectedIndex];
    const panel = document.getElementById('counselorInfo');

    if (!select.value) {
        panel.style.display = 'none';
        return;
    }

    document.getElementById('ci-dept').textContent  = opt.dataset.dept  || 'N/A';
    document.getElementById('ci-spec').textContent  = opt.dataset.spec  || 'General';
    document.getElementById('ci-avail').textContent = opt.dataset.avail || 'Mon–Fri';
    document.getElementById('ci-hours').textContent = `${opt.dataset.from} – ${opt.dataset.until}`;
    panel.style.display = 'block';
}

// Trigger on page load if value is already selected (e.g. validation fail)
document.addEventListener('DOMContentLoaded', () => {
    const sel = document.getElementById('counselor_id');
    if (sel.value) updateCounselorInfo(sel);
});
</script>
@endpush

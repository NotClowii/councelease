@extends('layouts.app')

@section('title', 'Book Appointment')
@section('page-title', 'Book Appointment')
@section('breadcrumb', 'Appointments / New')

@section('content')
<div class="page-header">
    <div>
        <h1>Book an Appointment</h1>
        <p>
            @if(auth()->user()->isStudent())
                Request a counseling session with one of our guidance counselors
            @else
                Schedule a counseling session on behalf of a student
            @endif
        </p>
    </div>
    <a href="{{ route('appointments.index') }}" class="btn btn-outline">
        <i class="ph-bold ph-arrow-left"></i> Back
    </a>
</div>

{{-- ── Time slot styles ── --}}
<style>
/* Student search */
.student-search-wrap { position: relative; }
.student-search-input {
    width: 100%;
    padding: 10px 16px 10px 40px;
    border: 1px solid var(--border-light);
    border-radius: 8px;
    font-size: 14px;
    background: var(--bg, #fff);
    color: var(--text-primary);
    outline: none;
    transition: border-color .2s;
    box-sizing: border-box;
    font-family: inherit;
}
.student-search-input:focus { border-color: var(--primary); }
.student-search-icon {
    position: absolute; left: 13px; top: 50%;
    transform: translateY(-50%);
    color: var(--text-muted); font-size: 15px; pointer-events: none;
}
.student-dropdown {
    position: absolute; top: calc(100% + 5px); left: 0; right: 0;
    background: var(--surface, #fff);
    border: 1px solid var(--border-light);
    border-radius: 10px;
    box-shadow: 0 8px 24px rgba(0,0,0,.12);
    max-height: 220px; overflow-y: auto;
    z-index: 200; display: none;
}
.student-dropdown.open { display: block; }
.student-option {
    display: flex; align-items: center; gap: 12px;
    padding: 10px 14px; cursor: pointer;
    border-bottom: 1px solid var(--border-light);
    transition: background .15s;
}
.student-option:last-child { border-bottom: none; }
.student-option:hover { background: var(--surface-2, #f9f9f9); }
.s-avatar {
    width: 32px; height: 32px; border-radius: 50%;
    background: var(--primary); color: #fff;
    display: flex; align-items: center; justify-content: center;
    font-size: 11px; font-weight: 700; flex-shrink: 0;
}
.s-name { font-size: 13px; font-weight: 600; color: var(--text-primary); }
.s-meta { font-size: 11px; color: var(--text-muted); }
.student-pill {
    display: flex; align-items: center; gap: 10px;
    padding: 9px 14px; margin-top: 8px;
    background: color-mix(in srgb, var(--primary) 10%, transparent);
    border: 1px solid color-mix(in srgb, var(--primary) 30%, transparent);
    border-radius: 8px;
}
.student-pill .pill-name { font-weight: 600; font-size: 13px; flex: 1; color: var(--text-primary); }
.student-pill .pill-clear {
    background: none; border: none; cursor: pointer;
    color: var(--text-muted); font-size: 15px; line-height: 1; padding: 0;
}
.student-pill .pill-clear:hover { color: var(--danger, #e53e3e); }
.no-results { padding: 14px; text-align: center; color: var(--text-muted); font-size: 13px; }

/* Time slots */
.timeslot-grid {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 10px;
    margin-top: 10px;
}
.timeslot-btn {
    padding: 11px 8px;
    border: 1.5px solid var(--border-light);
    border-radius: 10px;
    background: var(--bg, #fff);
    color: var(--text-primary);
    font-size: 13px; font-weight: 500;
    cursor: pointer; text-align: center;
    transition: border-color .15s, background .15s, color .15s;
    font-family: inherit; line-height: 1.2;
    user-select: none; white-space: nowrap;
}
.timeslot-btn:hover:not(:disabled) {
    border-color: var(--primary);
    background: color-mix(in srgb, var(--primary) 8%, transparent);
}
.timeslot-btn.selected {
    border-color: var(--primary) !important;
    background: var(--primary) !important;
    color: #fff !important;
}
/* Booked: greyed background, lock icon + strikethrough text */
.timeslot-btn.booked {
    background: var(--surface-2, #f3f4f6) !important;
    border-color: var(--border-light) !important;
    color: var(--text-muted) !important;
    cursor: not-allowed;
    opacity: 1;
}
.timeslot-btn.booked .slot-time {
    text-decoration: line-through;
    text-decoration-color: var(--text-muted);
    opacity: .6;
}
.timeslot-btn.booked .slot-lock {
    display: inline-block;
    margin-right: 4px;
    font-style: normal;
}
/* Past slots */
.timeslot-btn.past {
    opacity: .3; cursor: not-allowed;
}
.timeslot-loading { font-size: 13px; color: var(--text-muted); padding: 12px 0; }
.slot-legend {
    display: flex; gap: 14px; margin-top: 10px; flex-wrap: wrap;
}
.slot-legend span {
    display: flex; align-items: center; gap: 5px;
    font-size: 11px; color: var(--text-muted);
}
.l-dot {
    width: 12px; height: 12px; border-radius: 3px; flex-shrink: 0;
}
.l-avail { background: var(--bg, #fff); border: 1.5px solid var(--border-light); }
.l-sel   { background: var(--primary); }
.l-book  { background: var(--surface-2, #f3f4f6); border: 1.5px solid var(--border-light); }
</style>

<div style="display:grid;grid-template-columns:1fr 340px;gap:24px;align-items:start;">

    <div class="card">
        <div class="card-header">
            <span class="card-title">Appointment Details</span>
        </div>
        <div class="card-body">
            <form method="POST" action="{{ route('appointments.store') }}" id="bookForm">
                @csrf

                {{-- ── STUDENT SEARCH (counselors / staff / admin only) ── --}}
                @if(auth()->user()->isCounselor() || auth()->user()->isOfficeStaff() || auth()->user()->isSystemAdmin())
                <div class="form-group">
                    <label>Search Student <span style="color:var(--danger)">*</span></label>
                    <div class="student-search-wrap" id="studentSearchWrap">
                        <i class="ph-bold ph-magnifying-glass student-search-icon"></i>
                        <input type="text" id="studentSearchInput" class="student-search-input"
                            placeholder="Search by name or student number…" autocomplete="off">
                        <div class="student-dropdown" id="studentDropdown"></div>
                    </div>
                    <div id="studentPill" class="student-pill" style="display:none;">
                        <div class="s-avatar" id="pillAvatar"></div>
                        <span class="pill-name" id="pillName"></span>
                        <span class="s-meta" id="pillMeta"></span>
                        <button type="button" class="pill-clear" id="clearStudent" title="Remove">
                            <i class="ph-bold ph-x"></i>
                        </button>
                    </div>
                    <input type="hidden" name="student_id" id="studentIdInput" value="{{ old('student_id') }}">
                    @error('student_id')
                        <div class="field-error"><i class="ph-bold ph-warning-circle"></i>{{ $message }}</div>
                    @enderror
                </div>
                @endif

                {{-- ── COUNSELOR SELECT (unchanged from original) ── --}}
                <div class="form-group">
                    <label for="counselor_id">Select Counselor <span style="color:var(--danger)">*</span></label>
                    <select name="counselor_id" id="counselor_id"
                        class="form-control {{ $errors->has('counselor_id') ? 'is-invalid' : '' }}"
                        required onchange="onCounselorChange(this)">
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
                    @error('counselor_id')
                        <div class="field-error"><i class="ph-bold ph-warning-circle"></i>{{ $message }}</div>
                    @enderror
                </div>

                {{-- Counselor Info Panel (unchanged from original) --}}
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

                {{-- ── DATE PICKER (keeps your existing style, drives the slot grid) ── --}}
                <div class="form-group">
                    <label for="appointmentDate">Preferred Date <span style="color:var(--danger)">*</span></label>
                    <input type="date" id="appointmentDate" class="form-control"
                        min="{{ now()->addDay()->toDateString() }}"
                        value="{{ old('appointment_date', '') }}"
                        required>
                    <div class="form-hint">Select a date, then choose an available time slot below</div>
                </div>

                {{-- ── TIME SLOTS ── --}}
                <div class="form-group" id="timeslotSection" style="display:none;">
                    <label>Available Time Slots <span style="color:var(--danger)">*</span></label>
                    <div id="timeslotGrid" class="timeslot-grid">
                        <div class="timeslot-loading">Loading…</div>
                    </div>
                    <div class="slot-legend">
                        <span><span class="l-dot l-avail"></span> Available</span>
                        <span><span class="l-dot l-sel"></span> Selected</span>
                        <span><span class="l-dot l-book"></span> Booked / Conflict</span>
                    </div>
                    @error('appointment_datetime')
                        <div class="field-error"><i class="ph-bold ph-warning-circle"></i>{{ $message }}</div>
                    @enderror
                </div>

                {{-- Hidden: final ISO datetime sent to server --}}
                <input type="hidden" name="appointment_datetime" id="appointmentDatetime" value="{{ old('appointment_datetime') }}">

                {{-- ── CONCERN TYPE (unchanged) ── --}}
                <div class="form-group">
                    <label for="concern_type">Concern Type <span style="color:var(--danger)">*</span></label>
                    <select name="concern_type" id="concern_type"
                        class="form-control {{ $errors->has('concern_type') ? 'is-invalid' : '' }}" required>
                        <option value="">— Select concern type —</option>
                        @foreach(['Academic','Personal/Social','Career & Future Planning','Behavioral','Family Issues','Mental Health & Wellness','Financial Concerns','Peer Relationships','Stress Management','Other'] as $ct)
                        <option value="{{ $ct }}" {{ old('concern_type') === $ct ? 'selected' : '' }}>{{ $ct }}</option>
                        @endforeach
                    </select>
                    @error('concern_type')
                        <div class="field-error"><i class="ph-bold ph-warning-circle"></i>{{ $message }}</div>
                    @enderror
                </div>

                {{-- ── DESCRIPTION (unchanged) ── --}}
                <div class="form-group">
                    <label for="concern_description">Brief Description <span style="color:var(--text-muted);font-weight:400;">(optional)</span></label>
                    <textarea name="concern_description" id="concern_description"
                        class="form-control" rows="4"
                        placeholder="Briefly describe what you'd like to discuss. This helps your counselor prepare for the session."
                        maxlength="1000">{{ old('concern_description') }}</textarea>
                    <div class="form-hint">Maximum 1000 characters. This is visible only to your assigned counselor.</div>
                </div>

                {{-- Info banner (unchanged) --}}
                <div style="padding:14px;background:#FFFFF0;border:1px solid #FAF089;border-radius:10px;margin-bottom:20px;">
                    <div style="display:flex;gap:8px;font-size:13px;color:#744210;">
                        <i class="ph-bold ph-info" style="font-size:16px;flex-shrink:0;margin-top:1px;"></i>
                        <div>
                            <strong>Important:</strong> Your appointment request will be reviewed and approved by the guidance office or your assigned counselor. You will be notified once it's approved.
                        </div>
                    </div>
                </div>

                <div class="flex gap-2">
                    <button type="submit" class="btn btn-primary" id="submitBtn" disabled>
                        <i class="ph-bold ph-calendar-plus"></i>
                        Submit Request
                    </button>
                    <a href="{{ route('appointments.index') }}" class="btn btn-outline">Cancel</a>
                </div>

            </form>
        </div>
    </div>

    {{-- ── SIDEBAR (unchanged from original) ── --}}
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
(function () {
    /* ── Time slots (9 AM – 4 PM, hourly) ── */
    const SLOTS = [
        '09:00','10:00','11:00',
        '13:00','14:00','15:00',
        '16:00',
    ];

    function to12(t) {
        const [h, m] = t.split(':').map(Number);
        const ampm = h >= 12 ? 'PM' : 'AM';
        return `${h % 12 || 12}:${String(m).padStart(2,'0')} ${ampm}`;
    }

    /* ── State ── */
    let selCounselorId = '{{ old('counselor_id') }}' || null;
    let selDate        = '{{ old('appointment_date') }}' || null;
    let selTime        = null;   // HH:MM
    let counselorBooked = [];
    let studentBooked   = [];

    const dateInput     = document.getElementById('appointmentDate');
    const slotSection   = document.getElementById('timeslotSection');
    const slotGrid      = document.getElementById('timeslotGrid');
    const datetimeHidden= document.getElementById('appointmentDatetime');
    const submitBtn     = document.getElementById('submitBtn');

    /* ── Counselor change — keeps original info panel AND triggers slot refresh ── */
    window.onCounselorChange = function (select) {
        // Original info panel logic
        const opt   = select.options[select.selectedIndex];
        const panel = document.getElementById('counselorInfo');
        if (!select.value) { panel.style.display = 'none'; }
        else {
            document.getElementById('ci-dept').textContent  = opt.dataset.dept  || 'N/A';
            document.getElementById('ci-spec').textContent  = opt.dataset.spec  || 'General';
            document.getElementById('ci-avail').textContent = opt.dataset.avail || 'Mon–Fri';
            document.getElementById('ci-hours').textContent = `${opt.dataset.from} – ${opt.dataset.until}`;
            panel.style.display = 'block';
        }

        // Slot logic
        selCounselorId = select.value || null;
        selTime = null;
        datetimeHidden.value = '';
        refreshSlots();
    };

    // Fire on load for old() value
    document.addEventListener('DOMContentLoaded', () => {
        const sel = document.getElementById('counselor_id');
        if (sel.value) onCounselorChange(sel);
    });

    /* ── Date change ── */
    dateInput.addEventListener('change', () => {
        selDate = dateInput.value || null;
        selTime = null;
        datetimeHidden.value = '';
        refreshSlots();
    });

    /* ── Fetch booked slots then render ── */
    function refreshSlots() {
        updateSubmit();
        if (!selCounselorId || !selDate) { slotSection.style.display = 'none'; return; }

        slotSection.style.display = 'block';
        slotGrid.innerHTML = '<div class="timeslot-loading">Loading available slots…</div>';

        const studentId = document.getElementById('studentIdInput')?.value || '';
        let url = `{{ route('appointments.booked-slots') }}?counselor_id=${selCounselorId}&date=${selDate}`;
        if (studentId) url += `&student_id=${studentId}`;

        fetch(url, { headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' } })
            .then(r => r.json())
            .then(d => {
                counselorBooked = d.counselor_booked || [];
                studentBooked   = d.student_booked   || [];
                renderSlots();
            })
            .catch(() => { counselorBooked = []; studentBooked = []; renderSlots(); });
    }

    /* ── Render slot buttons ── */
    function renderSlots() {
        const now          = new Date();
        const todayStr     = now.toISOString().slice(0, 10);
        const nowMinutes   = now.getHours() * 60 + now.getMinutes();

        slotGrid.innerHTML = '';

        SLOTS.forEach(t => {
            const [h, m]   = t.split(':').map(Number);
            const slotMins = h * 60 + m;
            const isPast   = (selDate === todayStr) && slotMins <= nowMinutes;
            const isCBooked= counselorBooked.includes(t);
            const isSBooked= studentBooked.includes(t);
            const isBooked = isCBooked || isSBooked;

            const btn = document.createElement('button');
            btn.type  = 'button';
            btn.dataset.time = t;

            if (isPast) {
                btn.className = 'timeslot-btn past';
                btn.disabled  = true;
                btn.innerHTML = `<span class="slot-time">${to12(t)}</span>`;
            } else if (isBooked) {
                btn.className = 'timeslot-btn booked';
                btn.disabled  = true;
                // Lock emoji + strikethrough time, matching the screenshot
                btn.innerHTML = `<span class="slot-lock">🔒</span><span class="slot-time">${to12(t)}</span>`;
            } else {
                btn.className = 'timeslot-btn' + (t === selTime ? ' selected' : '');
                btn.innerHTML = `<span class="slot-time">${to12(t)}</span>`;
                btn.addEventListener('click', () => pickTime(t));
            }

            slotGrid.appendChild(btn);
        });

        if (selTime) highlightSlot(selTime);
    }

    function pickTime(t) {
        selTime = t;
        datetimeHidden.value = `${selDate}T${t}:00`;
        highlightSlot(t);
        updateSubmit();
    }

    function highlightSlot(t) {
        slotGrid.querySelectorAll('.timeslot-btn').forEach(b => {
            b.classList.toggle('selected', b.dataset.time === t && !b.disabled);
        });
    }

    /* ── Submit button state ── */
    function updateSubmit() {
        @if(auth()->user()->isCounselor() || auth()->user()->isOfficeStaff() || auth()->user()->isSystemAdmin())
        const hasStudent = !!document.getElementById('studentIdInput')?.value;
        @else
        const hasStudent = true;
        @endif
        submitBtn.disabled = !(selCounselorId && selDate && selTime && hasStudent);
    }

    /* ── Restore old() time slot on validation fail ── */
    @if(old('appointment_datetime'))
    (function () {
        const dt = '{{ old('appointment_datetime') }}';
        if (dt.includes('T')) {
            selDate = dt.split('T')[0];
            selTime = dt.split('T')[1].slice(0,5);
            dateInput.value = selDate;
            datetimeHidden.value = dt;
            if (selCounselorId) refreshSlots();
        }
    })();
    @endif

    /* ════════════════════════════════════════════
       STUDENT SEARCH  (staff / counselor / admin)
    ════════════════════════════════════════════ */
    @if(auth()->user()->isCounselor() || auth()->user()->isOfficeStaff() || auth()->user()->isSystemAdmin())

    const searchInput  = document.getElementById('studentSearchInput');
    const dropdown     = document.getElementById('studentDropdown');
    const pill         = document.getElementById('studentPill');
    const pillAvatar   = document.getElementById('pillAvatar');
    const pillName     = document.getElementById('pillName');
    const pillMeta     = document.getElementById('pillMeta');
    const studentHidden= document.getElementById('studentIdInput');
    const clearBtn     = document.getElementById('clearStudent');

    let searchTimer = null;

    searchInput.addEventListener('input', () => {
        clearTimeout(searchTimer);
        const q = searchInput.value.trim();
        if (q.length < 2) { dropdown.classList.remove('open'); return; }
        searchTimer = setTimeout(() => fetchStudents(q), 300);
    });

    searchInput.addEventListener('focus', () => {
        if (searchInput.value.trim().length >= 2) dropdown.classList.add('open');
    });

    document.addEventListener('click', e => {
        if (!e.target.closest('#studentSearchWrap')) dropdown.classList.remove('open');
    });

    clearBtn.addEventListener('click', () => {
        studentHidden.value = '';
        pill.style.display  = 'none';
        searchInput.style.display = '';
        searchInput.value   = '';
        searchInput.focus();
        counselorBooked = []; studentBooked = [];
        if (selCounselorId && selDate) refreshSlots();
        updateSubmit();
    });

    function fetchStudents(q) {
        fetch(`{{ route('students.search') }}?q=${encodeURIComponent(q)}`, {
            headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' }
        })
        .then(r => r.json())
        .then(d => renderResults(d.students || d))
        .catch(() => renderResults([]));
    }

    function renderResults(list) {
        dropdown.innerHTML = '';
        if (!list.length) {
            dropdown.innerHTML = '<div class="no-results">No students found.</div>';
            dropdown.classList.add('open'); return;
        }
        list.forEach(s => {
            const initials = ((s.first_name||'')[0]||(s.full_name||'')[0]||'') +
                             ((s.last_name||'')[0]||'');
            const div = document.createElement('div');
            div.className = 'student-option';
            div.innerHTML = `
                <div class="s-avatar">${initials.toUpperCase()}</div>
                <div>
                    <div class="s-name">${s.full_name || s.first_name+' '+s.last_name}</div>
                    <div class="s-meta">${s.student_num||''} · ${s.department||''}</div>
                </div>`;
            div.addEventListener('click', () => selectStudent(s, initials));
            dropdown.appendChild(div);
        });
        dropdown.classList.add('open');
    }

    function selectStudent(s, initials) {
        studentHidden.value       = s.student_id;
        pillAvatar.textContent    = initials.toUpperCase();
        pillName.textContent      = s.full_name || `${s.first_name} ${s.last_name}`;
        pillMeta.textContent      = `${s.student_num||''} · ${s.department||''}`;
        pill.style.display        = 'flex';
        searchInput.style.display = 'none';
        dropdown.classList.remove('open');
        // Re-fetch slots to flag this student's conflicts
        if (selCounselorId && selDate) refreshSlots();
        updateSubmit();
    }

    // Restore selected student when validation fails and old('student_id') exists
    @if(old('student_id'))
    fetch(`{{ route('students.search') }}?q={{ old('student_id') }}&by_id=1`, {
        headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' }
    }).then(r => r.json()).then(d => {
        const list = d.students || d;
        if (list.length) {
            const s = list[0];
            const initials = ((s.first_name||'')[0]||'') + ((s.last_name||'')[0]||'');
            selectStudent(s, initials);
        }
    });
    @endif

    @endif  {{-- end isStaff/Counselor/Admin --}}

})();
</script>
@endpush
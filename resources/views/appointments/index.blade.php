@extends('layouts.app')

@section('title', 'Appointments')
@section('page-title', 'Appointments')
@section('breadcrumb', 'Manage and track all counseling appointments')

@section('content')
<div class="page-header">
    <div>
        <h1>Appointments</h1>
        <p>Manage and track counseling appointment requests</p>
    </div>
    @if(auth()->user()->isStudent())
    <a href="{{ route('appointments.create') }}" class="btn btn-primary">
        <i class="ph-bold ph-plus"></i>
        Book Appointment
    </a>
    @endif
</div>

<div class="card">
    {{-- Filter Bar --}}
    <form method="GET" action="{{ route('appointments.index') }}">
        <div class="filter-bar">
            <select name="status" class="form-control" style="width:160px;" onchange="this.form.submit()">
                <option value="">All Statuses</option>
                @foreach(['pending','approved','completed','cancelled','rescheduled','no_show'] as $s)
                <option value="{{ $s }}" {{ request('status') === $s ? 'selected' : '' }}>{{ ucfirst(str_replace('_',' ',$s)) }}</option>
                @endforeach
            </select>

            @if(!auth()->user()->isCounselor() && !auth()->user()->isStudent())
            <select name="counselor_id" class="form-control" style="width:180px;" onchange="this.form.submit()">
                <option value="">All Counselors</option>
                @foreach($counselors as $c)
                <option value="{{ $c->counselor_id }}" {{ request('counselor_id') == $c->counselor_id ? 'selected' : '' }}>
                    {{ $c->user->full_name }}
                </option>
                @endforeach
            </select>
            @endif

            <input type="date" name="date_from" class="form-control" style="width:150px;" value="{{ request('date_from') }}" placeholder="From">
            <input type="date" name="date_to" class="form-control" style="width:150px;" value="{{ request('date_to') }}" placeholder="To">

            <button type="submit" class="btn btn-primary btn-sm">
                <i class="ph-bold ph-funnel"></i> Filter
            </button>
            @if(request()->hasAny(['status','counselor_id','date_from','date_to']))
            <a href="{{ route('appointments.index') }}" class="btn btn-outline btn-sm">
                <i class="ph-bold ph-x"></i> Clear
            </a>
            @endif
        </div>
    </form>

    {{-- Table --}}
    <div class="table-wrap">
        @if($appointments->isEmpty())
        <div class="empty-state">
            <i class="ph ph-calendar-blank"></i>
            <h3>No appointments found</h3>
            <p>Try adjusting your filters or book a new appointment.</p>
        </div>
        @else
        <table>
            <thead>
                <tr>
                    <th>#</th>
                    @if(!auth()->user()->isStudent()) <th>Student</th> @endif
                    @if(!auth()->user()->isCounselor()) <th>Counselor</th> @endif
                    <th>Date & Time</th>
                    <th>Concern</th>
                    <th>Status</th>
                    <th>Booked</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                @foreach($appointments as $appt)
                @php
                    $statusMap = ['pending'=>'warning','approved'=>'success','completed'=>'info','cancelled'=>'danger','no_show'=>'secondary','rescheduled'=>'primary'];
                @endphp
                <tr>
                    <td class="text-muted text-sm">#{{ $appt->appointment_id }}</td>

                    @if(!auth()->user()->isStudent())
                    <td>
                        <div class="flex items-center gap-2">
                            <div class="avatar">{{ $appt->student->user->initials }}</div>
                            <div>
                                <div class="font-semibold">{{ $appt->student->user->full_name }}</div>
                                <div class="text-sm text-muted">{{ $appt->student->student_num }}</div>
                            </div>
                        </div>
                    </td>
                    @endif

                    @if(!auth()->user()->isCounselor())
                    <td>
                        <div class="font-semibold">{{ $appt->counselor->user->full_name }}</div>
                        <div class="text-sm text-muted">{{ $appt->counselor->specialization }}</div>
                    </td>
                    @endif

                    <td>
                        <div class="font-semibold">{{ $appt->appointment_datetime->format('M d, Y') }}</div>
                        <div class="text-sm text-muted">{{ $appt->appointment_datetime->format('h:i A') }}</div>
                    </td>

                    <td>{{ $appt->concern_type ?? '—' }}</td>

                    <td>
                        <span class="badge badge-{{ $statusMap[$appt->appointment_status] ?? 'secondary' }}">
                            {{ ucfirst(str_replace('_', ' ', $appt->appointment_status)) }}
                        </span>
                    </td>

                    <td class="text-sm text-muted">{{ $appt->created_at->format('M d, Y') }}</td>

                    <td>
                        <div class="flex gap-2">
                            <a href="{{ route('appointments.show', $appt) }}" class="btn btn-outline btn-sm btn-icon" title="View">
                                <i class="ph-bold ph-eye"></i>
                            </a>

                            @if($appt->isPending() && (auth()->user()->isCounselor() || auth()->user()->isOfficeStaff() || auth()->user()->isSystemAdmin()))
                            <form method="POST" action="{{ route('appointments.approve', $appt) }}">
                                @csrf
                                <button type="submit" class="btn btn-success btn-sm btn-icon" title="Approve">
                                    <i class="ph-bold ph-check"></i>
                                </button>
                            </form>
                            @endif

                            @if($appt->canBeCancelled())
                            <button class="btn btn-danger btn-sm btn-icon" title="Cancel"
                                onclick="showCancelModal({{ $appt->appointment_id }})">
                                <i class="ph-bold ph-x"></i>
                            </button>
                            @endif

                            @if($appt->isApproved() && auth()->user()->isCounselor())
                            <form method="POST" action="{{ route('appointments.start-session', $appt) }}">
                                @csrf
                                <button type="submit" class="btn btn-primary btn-sm" title="Start Session">
                                    <i class="ph-bold ph-play"></i> Start
                                </button>
                            </form>
                            @endif
                        </div>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
        @endif
    </div>

    @if($appointments->hasPages())
    <div style="padding:16px 20px;border-top:1px solid var(--border-light);display:flex;align-items:center;justify-content:space-between;">
        <div class="text-sm text-muted">
            Showing {{ $appointments->firstItem() }}–{{ $appointments->lastItem() }} of {{ $appointments->total() }} appointments
        </div>
        <div class="pagination">
            {{ $appointments->links() }}
        </div>
    </div>
    @endif
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
        <p style="font-size:13.5px;color:var(--text-muted);margin-bottom:18px;">Please provide a reason for cancelling this appointment. This will be shared with the other party.</p>
        <form id="cancelForm" method="POST">
            @csrf
            <div class="form-group">
                <label>Cancellation Reason <span style="color:var(--danger)">*</span></label>
                <textarea name="cancellation_reason" class="form-control" rows="3" placeholder="e.g., Schedule conflict, personal emergency..." required></textarea>
            </div>
            <div class="flex gap-2" style="justify-content:flex-end;margin-top:8px;">
                <button type="button" class="btn btn-outline" onclick="hideCancelModal()">Keep Appointment</button>
                <button type="submit" class="btn btn-danger">Cancel Appointment</button>
            </div>
        </form>
    </div>
</div>

@endsection

@push('scripts')
<script>
function showCancelModal(id) {
    document.getElementById('cancelForm').action = `/appointments/${id}/cancel`;
    document.getElementById('cancelModal').style.display = 'flex';
}
function hideCancelModal() {
    document.getElementById('cancelModal').style.display = 'none';
}
document.getElementById('cancelModal').addEventListener('click', function(e) {
    if (e.target === this) hideCancelModal();
});
</script>
@endpush

@extends('layouts.app')

@section('title', 'Sessions')
@section('page-title', 'Sessions')

@section('content')
<div class="page-header">
    <div>
        <h1>Counseling Sessions</h1>
        <p>View and manage all counseling session records</p>
    </div>
</div>

<div class="card">
    <form method="GET" action="{{ route('sessions.index') }}">
        <div class="filter-bar">
            <select name="status" class="form-control" style="width:160px;" onchange="this.form.submit()">
                <option value="">All Statuses</option>
                <option value="ongoing"   {{ request('status') === 'ongoing'   ? 'selected' : '' }}>Ongoing</option>
                <option value="completed" {{ request('status') === 'completed' ? 'selected' : '' }}>Completed</option>
                <option value="cancelled" {{ request('status') === 'cancelled' ? 'selected' : '' }}>Cancelled</option>
            </select>
            <input type="date" name="date_from" class="form-control" style="width:150px;" value="{{ request('date_from') }}">
            <input type="date" name="date_to"   class="form-control" style="width:150px;" value="{{ request('date_to') }}">
            <button type="submit" class="btn btn-primary btn-sm"><i class="ph-bold ph-funnel"></i> Filter</button>
            @if(request()->hasAny(['status','date_from','date_to']))
            <a href="{{ route('sessions.index') }}" class="btn btn-outline btn-sm"><i class="ph-bold ph-x"></i> Clear</a>
            @endif
        </div>
    </form>

    <div class="table-wrap">
        @if($sessions->isEmpty())
        <div class="empty-state">
            <i class="ph ph-chats"></i>
            <h3>No sessions found</h3>
            <p>Sessions are created when an approved appointment is started by a counselor.</p>
        </div>
        @else
        <table>
            <thead>
                <tr>
                    <th>#</th>
                    @if(!auth()->user()->isStudent()) <th>Student</th> @endif
                    @if(!auth()->user()->isCounselor()) <th>Counselor</th> @endif
                    <th>Session Date</th>
                    <th>Duration</th>
                    <th>Type</th>
                    <th>Status</th>
                    <th>Case Notes</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                @foreach($sessions as $session)
                <tr>
                    <td class="text-muted text-sm">#{{ $session->session_id }}</td>

                    @if(!auth()->user()->isStudent())
                    <td>
                        <div class="flex items-center gap-2">
                            <div class="avatar">{{ $session->student->user->initials }}</div>
                            <div>
                                <div class="font-semibold">{{ $session->student->user->full_name }}</div>
                                <div class="text-sm text-muted">{{ $session->student->student_num }}</div>
                            </div>
                        </div>
                    </td>
                    @endif

                    @if(!auth()->user()->isCounselor())
                    <td>{{ $session->counselor->user->full_name }}</td>
                    @endif

                    <td>
                        <div class="font-semibold">{{ $session->session_datetime->format('M d, Y') }}</div>
                        <div class="text-sm text-muted">{{ $session->session_datetime->format('h:i A') }}</div>
                    </td>

                    <td>{{ $session->actual_duration_minutes ? $session->actual_duration_minutes . ' min' : '—' }}</td>

                    <td>
                        <span class="badge badge-secondary" style="text-transform:capitalize;">
                            {{ str_replace('_', ' ', $session->session_type) }}
                        </span>
                    </td>

                    <td>
                        <span class="badge badge-{{ $session->session_status === 'completed' ? 'success' : ($session->session_status === 'ongoing' ? 'info' : 'danger') }}">
                            {{ ucfirst($session->session_status) }}
                        </span>
                    </td>

                    <td>
                        @if(!auth()->user()->isStudent())
                        <span class="badge badge-secondary">{{ $session->caseNotes->count() ?? 0 }} note(s)</span>
                        @else
                        <span class="text-muted text-sm">Confidential</span>
                        @endif
                    </td>

                    <td>
                        <div class="flex gap-2">
                            <a href="{{ route('sessions.show', $session) }}" class="btn btn-outline btn-sm btn-icon">
                                <i class="ph-bold ph-eye"></i>
                            </a>

                            @if($session->isOngoing() && auth()->user()->isCounselor())
                            <button class="btn btn-success btn-sm" onclick="showCompleteModal({{ $session->session_id }})">
                                <i class="ph-bold ph-check"></i> Complete
                            </button>
                            @endif

                            @if($session->isCompleted() && auth()->user()->isCounselor())
                            <a href="{{ route('case-notes.create') }}?session={{ $session->session_id }}" class="btn btn-outline btn-sm">
                                <i class="ph-bold ph-plus"></i> Note
                            </a>
                            @endif
                        </div>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
        @endif
    </div>

    @if($sessions->hasPages())
    <div style="padding:16px 20px;border-top:1px solid var(--border-light);display:flex;justify-content:space-between;align-items:center;">
        <div class="text-sm text-muted">Showing {{ $sessions->firstItem() }}–{{ $sessions->lastItem() }} of {{ $sessions->total() }}</div>
        {{ $sessions->links() }}
    </div>
    @endif
</div>

{{-- Complete Session Modal --}}
<div id="completeModal" style="display:none;position:fixed;inset:0;z-index:300;background:rgba(0,0,0,.4);align-items:center;justify-content:center;">
    <div style="background:white;border-radius:16px;padding:28px;width:100%;max-width:480px;margin:20px;box-shadow:0 20px 60px rgba(0,0,0,.2);">
        <div style="display:flex;align-items:center;gap:10px;margin-bottom:18px;">
            <div style="width:40px;height:40px;background:var(--success-light);border-radius:10px;display:flex;align-items:center;justify-content:center;">
                <i class="ph-bold ph-check-circle" style="font-size:20px;color:var(--success);"></i>
            </div>
            <h3 style="font-size:16px;font-weight:700;">Complete Session</h3>
        </div>
        <form id="completeForm" method="POST">
            @csrf
            <div class="form-group">
                <label>Actual Duration (minutes) <span style="color:var(--danger)">*</span></label>
                <input type="number" name="actual_duration_minutes" class="form-control" min="1" max="480" value="60" required>
            </div>
            <div class="form-group">
                <label>Session Summary <span style="color:var(--text-muted);font-weight:400;">(optional)</span></label>
                <textarea name="session_summary" class="form-control" rows="3" placeholder="Brief summary of the session..."></textarea>
            </div>
            <div class="flex gap-2" style="justify-content:flex-end;">
                <button type="button" class="btn btn-outline" onclick="document.getElementById('completeModal').style.display='none'">Cancel</button>
                <button type="submit" class="btn btn-success"><i class="ph-bold ph-check"></i> Mark Complete</button>
            </div>
        </form>
    </div>
</div>

@endsection

@push('scripts')
<script>
function showCompleteModal(sessionId) {
    document.getElementById('completeForm').action = `/sessions/${sessionId}/complete`;
    document.getElementById('completeModal').style.display = 'flex';
}
document.getElementById('completeModal').addEventListener('click', function(e) {
    if (e.target === this) this.style.display = 'none';
});
</script>
@endpush

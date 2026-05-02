@extends('layouts.app')

@section('title', 'Case Notes')
@section('page-title', 'Case Notes')

@section('content')
<div class="page-header">
    <div>
        <h1>Case Notes</h1>
        <p>Confidential session notes — visible to assigned counselors and system administrators only</p>
    </div>
</div>

<div class="card">
    <div class="table-wrap">
        @if($caseNotes->isEmpty())
        <div class="empty-state">
            <i class="ph ph-notebook"></i>
            <h3>No case notes yet</h3>
            <p>Case notes will appear here after sessions are completed and documented.</p>
        </div>
        @else
        <table>
            <thead>
                <tr>
                    <th>#</th>
                    <th>Student</th>
                    <th>Session Date</th>
                    <th>Type</th>
                    <th>Counselor</th>
                    <th>Created</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                @foreach($caseNotes as $note)
                <tr>
                    <td class="text-muted text-sm">#{{ $note->note_id }}</td>
                    <td>
                        <div class="flex items-center gap-2">
                            <div class="avatar">{{ $note->session->student->user->initials }}</div>
                            <div>
                                <div class="font-semibold">{{ $note->session->student->user->full_name }}</div>
                                <div class="text-sm text-muted">{{ $note->session->student->student_num }}</div>
                            </div>
                        </div>
                    </td>
                    <td>
                        <div>{{ $note->session->session_datetime->format('M d, Y') }}</div>
                        <div class="text-sm text-muted">{{ $note->session->session_datetime->format('h:i A') }}</div>
                    </td>
                    <td>
                        <span class="badge badge-secondary" style="text-transform:capitalize;">{{ $note->note_type }}</span>
                        @if($note->is_confidential)
                        <span class="badge" style="background:#FFF5F5;color:var(--danger);border:1px solid #FED7D7;margin-left:4px;">
                            <i class="ph-bold ph-lock-simple"></i> Confidential
                        </span>
                        @endif
                    </td>
                    <td>{{ $note->counselor->user->full_name }}</td>
                    <td class="text-sm text-muted">{{ $note->created_at->format('M d, Y') }}</td>
                    <td>
                        <div class="flex gap-2">
                            <a href="{{ route('case-notes.show', $note) }}" class="btn btn-outline btn-sm btn-icon">
                                <i class="ph-bold ph-eye"></i>
                            </a>
                            @if(auth()->user()->isCounselor() && $note->counselor_id === auth()->user()->counselor?->counselor_id)
                            <a href="{{ route('case-notes.edit', $note) }}" class="btn btn-outline btn-sm btn-icon">
                                <i class="ph-bold ph-pencil"></i>
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

    @if($caseNotes->hasPages())
    <div style="padding:16px 20px;border-top:1px solid var(--border-light);">
        {{ $caseNotes->links() }}
    </div>
    @endif
</div>
@endsection

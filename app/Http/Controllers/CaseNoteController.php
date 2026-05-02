<?php

namespace App\Http\Controllers;

use App\Models\CaseNote;
use App\Models\Session;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CaseNoteController extends Controller
{
    public function index(Request $request)
    {
        $user = Auth::user();

        // Only counselors and system admins can access case notes
        if (!$user->isCounselor() && !$user->isSystemAdmin()) {
            abort(403, 'Case notes are confidential.');
        }

        $query = CaseNote::with(['session.student.user', 'counselor.user']);

        if ($user->isCounselor()) {
            $query->where('counselor_id', $user->counselor->counselor_id ?? 0);
        }

        $caseNotes = $query->orderByDesc('created_at')->paginate(15);
        return view('case-notes.index', compact('caseNotes'));
    }

    public function create(Request $request)
    {
        $user = Auth::user();
        if (!$user->isCounselor()) abort(403);

        $session = Session::with(['student.user', 'appointment'])
            ->findOrFail($request->session);

        // Ensure the session belongs to this counselor
        if ($session->counselor_id !== $user->counselor->counselor_id) abort(403);

        return view('case-notes.create', compact('session'));
    }

    public function store(Request $request)
    {
        $user = Auth::user();
        if (!$user->isCounselor()) abort(403);

        $validated = $request->validate([
            'session_id'                => ['required', 'exists:sessions,session_id'],
            'notes_content'             => ['required', 'string'],
            'note_type'                 => ['required', 'in:initial,progress,termination,referral'],
            'interventions_used'        => ['nullable', 'array'],
            'follow_up_actions'         => ['nullable', 'string', 'max:1000'],
            'next_session_recommended'  => ['nullable', 'date', 'after:today'],
        ]);

        $session = Session::findOrFail($validated['session_id']);
        if ($session->counselor_id !== $user->counselor->counselor_id) abort(403);

        CaseNote::create([
            ...$validated,
            'counselor_id'    => $user->counselor->counselor_id,
            'is_confidential' => true,
        ]);

        return redirect()->route('sessions.show', $session)->with('success', 'Case note saved successfully.');
    }

    public function show(CaseNote $caseNote)
    {
        $user = Auth::user();

        // Only assigned counselor or system admin
        if ($user->isCounselor() && $caseNote->counselor_id !== $user->counselor?->counselor_id) abort(403);
        if ($user->isStudent()) abort(403);

        $caseNote->load(['session.student.user', 'counselor.user']);
        return view('case-notes.show', compact('caseNote'));
    }

    public function edit(CaseNote $caseNote)
    {
        $user = Auth::user();
        if (!$user->isCounselor() || $caseNote->counselor_id !== $user->counselor?->counselor_id) abort(403);

        $caseNote->load('session.student.user');
        return view('case-notes.edit', compact('caseNote'));
    }

    public function update(Request $request, CaseNote $caseNote)
    {
        $user = Auth::user();
        if (!$user->isCounselor() || $caseNote->counselor_id !== $user->counselor?->counselor_id) abort(403);

        $validated = $request->validate([
            'notes_content'             => ['required', 'string'],
            'note_type'                 => ['required', 'in:initial,progress,termination,referral'],
            'interventions_used'        => ['nullable', 'array'],
            'follow_up_actions'         => ['nullable', 'string', 'max:1000'],
            'next_session_recommended'  => ['nullable', 'date', 'after:today'],
        ]);

        $caseNote->update($validated);
        return redirect()->route('case-notes.show', $caseNote)->with('success', 'Case note updated.');
    }
}

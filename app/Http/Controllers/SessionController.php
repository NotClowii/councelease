<?php

namespace App\Http\Controllers;

use App\Models\Session;
use App\Models\CaseNote;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class SessionController extends Controller
{
    public function index(Request $request)
    {
        $user = Auth::user();
        $query = Session::with(['student.user', 'counselor.user', 'appointment']);

        if ($user->isStudent()) {
            $query->where('student_id', $user->student->student_id ?? 0);
        } elseif ($user->isCounselor()) {
            $query->where('counselor_id', $user->counselor->counselor_id ?? 0);
        }

        if ($request->filled('status')) {
            $query->where('session_status', $request->status);
        }
        if ($request->filled('date_from')) {
            $query->whereDate('session_datetime', '>=', $request->date_from);
        }
        if ($request->filled('date_to')) {
            $query->whereDate('session_datetime', '<=', $request->date_to);
        }

        $sessions = $query->orderByDesc('session_datetime')->paginate(15)->withQueryString();
        return view('sessions.index', compact('sessions'));
    }

    public function show(Session $session)
    {
        $this->authorizeView($session);
        $user = Auth::user();
        $session->load(['student.user', 'counselor.user', 'appointment', 'caseNotes.counselor.user']);

        // Students cannot see case notes
        $caseNotes = ($user->isStudent()) ? collect() : $session->caseNotes;

        return view('sessions.show', compact('session', 'caseNotes'));
    }

    public function complete(Request $request, Session $session)
    {
        $this->authorize('complete', $session);

        $validated = $request->validate([
            'session_summary'         => ['nullable', 'string', 'max:2000'],
            'actual_duration_minutes' => ['required', 'integer', 'min:1', 'max:480'],
        ]);

        $session->update([
            ...$validated,
            'session_status' => 'completed',
            'ended_at'       => now(),
        ]);

        return redirect()->route('case-notes.create', ['session' => $session->session_id])
            ->with('success', 'Session marked as complete. Please add case notes.');
    }

    private function authorizeView(Session $session): void
    {
        $user = Auth::user();
        if ($user->isStudent() && $session->student_id !== $user->student?->student_id) abort(403);
        if ($user->isCounselor() && $session->counselor_id !== $user->counselor?->counselor_id) abort(403);
    }
}

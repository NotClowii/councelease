<?php

namespace App\Http\Controllers;

use App\Models\Appointment;
use App\Models\Counselor;
use App\Models\Session;
use App\Models\Notification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class AppointmentController extends Controller
{
    public function index(Request $request)
    {
        $user = Auth::user();
        $query = Appointment::with(['student.user', 'counselor.user']);

        if ($user->isStudent()) {
            $query->where('student_id', $user->student->student_id ?? 0);
        } elseif ($user->isCounselor()) {
            $query->where('counselor_id', $user->counselor->counselor_id ?? 0);
        }

        // Filters
        if ($request->filled('status')) {
            $query->where('appointment_status', $request->status);
        }
        if ($request->filled('date_from')) {
            $query->whereDate('appointment_datetime', '>=', $request->date_from);
        }
        if ($request->filled('date_to')) {
            $query->whereDate('appointment_datetime', '<=', $request->date_to);
        }
        if ($request->filled('counselor_id') && !$user->isCounselor()) {
            $query->where('counselor_id', $request->counselor_id);
        }

        $appointments = $query->orderByDesc('appointment_datetime')->paginate(15)->withQueryString();
        $counselors = Counselor::with('user')->get();

        return view('appointments.index', compact('appointments', 'counselors'));
    }

    public function create()
    {
        $this->authorize('create', Appointment::class);
        $counselors = Counselor::with(['user', 'department'])->get();
        return view('appointments.create', compact('counselors'));
    }

    public function store(Request $request)
    {
        $this->authorize('create', Appointment::class);
        $user = Auth::user();

        $validated = $request->validate([
            'counselor_id'          => ['required', 'exists:counselors,counselor_id'],
            'appointment_datetime'  => ['required', 'date', 'after:now'],
            'concern_type'          => ['required', 'string', 'max:100'],
            'concern_description'   => ['nullable', 'string', 'max:1000'],
        ]);

        $counselor = Counselor::findOrFail($validated['counselor_id']);

        // Business Rule: Check for overlapping appointment for this counselor
        $overlap = Appointment::where('counselor_id', $counselor->counselor_id)
            ->where('appointment_datetime', $validated['appointment_datetime'])
            ->whereIn('appointment_status', ['pending', 'approved'])
            ->exists();

        if ($overlap) {
            return back()->withErrors(['appointment_datetime' => 'The counselor already has an appointment at this time. Please choose a different time.'])->withInput();
        }

        // Business Rule: Student cannot have two pending/approved appointments at same time
        $studentId = $user->student->student_id;
        $studentOverlap = Appointment::where('student_id', $studentId)
            ->where('appointment_datetime', $validated['appointment_datetime'])
            ->whereIn('appointment_status', ['pending', 'approved'])
            ->exists();

        if ($studentOverlap) {
            return back()->withErrors(['appointment_datetime' => 'You already have an appointment at this time.'])->withInput();
        }

        $appointment = Appointment::create([
            ...$validated,
            'student_id'           => $studentId,
            'appointment_status'   => 'pending',
            'duration_minutes'     => 60,
        ]);

        // Notify counselor
        Notification::create([
            'user_id'          => $counselor->user_id,
            'title'            => 'New Appointment Request',
            'message'          => "A new appointment request from {$user->full_name} on " . \Carbon\Carbon::parse($validated['appointment_datetime'])->format('M d, Y h:i A'),
            'type'             => 'appointment',
            'notifiable_type'  => Appointment::class,
            'notifiable_id'    => $appointment->appointment_id,
            'date_sent'        => now(),
        ]);

        return redirect()->route('appointments.show', $appointment)->with('success', 'Appointment request submitted successfully. Awaiting approval.');
    }

    public function show(Appointment $appointment)
    {
        $this->authorizeView($appointment);
        $appointment->load(['student.user', 'counselor.user', 'session.caseNotes', 'approvedByUser']);
        return view('appointments.show', compact('appointment'));
    }

    public function approve(Appointment $appointment)
    {
        $this->authorize('approve', $appointment);

        if (!$appointment->isPending()) {
            return back()->with('error', 'Only pending appointments can be approved.');
        }

        $appointment->update([
            'appointment_status' => 'approved',
            'approved_by'        => Auth::id(),
            'approved_at'        => now(),
        ]);

        // Notify student
        Notification::create([
            'user_id'         => $appointment->student->user_id,
            'title'           => 'Appointment Approved',
            'message'         => "Your appointment on " . $appointment->appointment_datetime->format('M d, Y h:i A') . " has been approved.",
            'type'            => 'appointment',
            'notifiable_type' => Appointment::class,
            'notifiable_id'   => $appointment->appointment_id,
            'date_sent'       => now(),
        ]);

        return back()->with('success', 'Appointment approved successfully.');
    }

    public function cancel(Request $request, Appointment $appointment)
    {
        $this->authorize('cancel', $appointment);

        if (!$appointment->canBeCancelled()) {
            return back()->with('error', 'This appointment cannot be cancelled.');
        }

        $validated = $request->validate([
            'cancellation_reason' => ['required', 'string', 'max:500'],
        ]);

        $appointment->update([
            'appointment_status'  => 'cancelled',
            'cancellation_reason' => $validated['cancellation_reason'],
            'cancelled_at'        => now(),
        ]);

        // Notify the other party
        $notifyUserId = Auth::user()->isStudent()
            ? $appointment->counselor->user_id
            : $appointment->student->user_id;

        Notification::create([
            'user_id'         => $notifyUserId,
            'title'           => 'Appointment Cancelled',
            'message'         => "Appointment on " . $appointment->appointment_datetime->format('M d, Y h:i A') . " has been cancelled. Reason: {$validated['cancellation_reason']}",
            'type'            => 'appointment',
            'notifiable_type' => Appointment::class,
            'notifiable_id'   => $appointment->appointment_id,
            'date_sent'       => now(),
        ]);

        return back()->with('success', 'Appointment cancelled.');
    }

    public function startSession(Appointment $appointment)
    {
        $this->authorize('startSession', $appointment);

        if (!$appointment->isApproved()) {
            return back()->with('error', 'Only approved appointments can start a session.');
        }

        DB::transaction(function () use ($appointment) {
            $session = Session::create([
                'appointment_id'   => $appointment->appointment_id,
                'student_id'       => $appointment->student_id,
                'counselor_id'     => $appointment->counselor_id,
                'session_datetime' => now(),
                'session_status'   => 'ongoing',
                'session_type'     => 'individual',
            ]);

            $appointment->update(['appointment_status' => 'completed']);
        });

        return redirect()->route('sessions.show', $appointment->session)->with('success', 'Session started.');
    }

    private function authorizeView(Appointment $appointment): void
    {
        $user = Auth::user();
        if ($user->isStudent() && $appointment->student_id !== $user->student?->student_id) {
            abort(403);
        }
        if ($user->isCounselor() && $appointment->counselor_id !== $user->counselor?->counselor_id) {
            abort(403);
        }
    }
}

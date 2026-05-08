<?php

namespace App\Http\Controllers;

use App\Models\Appointment;
use App\Models\Counselor;
use App\Models\Session;
use App\Models\Student;
use App\Models\Notification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class AppointmentController extends Controller
{
    public function index(Request $request)
    {
        $user  = Auth::user();
        $query = Appointment::with(['student.user', 'counselor.user']);

        if ($user->isStudent()) {
            $query->where('student_id', $user->student->student_id ?? 0);
        } elseif ($user->isCounselor()) {
            $query->where('counselor_id', $user->counselor->counselor_id ?? 0);
        }

        if ($request->filled('status')) {
            $query->where('appointment_status', $request->status);
        }
        if ($request->filled('date_from')) {
            $query->whereDate('appointment_datetime', '>=', $request->date_from);
        }
        if ($request->filled('date_to')) {
            $query->whereDate('appointment_datetime', '<=', $request->date_to);
        }
        if ($request->filled('counselor_id') && ! $user->isCounselor()) {
            $query->where('counselor_id', $request->counselor_id);
        }

        $appointments = $query->orderByDesc('appointment_datetime')->paginate(15)->withQueryString();
        $counselors   = Counselor::with('user')->get();

        return view('appointments.index', compact('appointments', 'counselors'));
    }

    public function create()
    {
        $this->authorize('create', Appointment::class);
        $counselors = Counselor::with(['user', 'department'])->get();
        return view('appointments.create', compact('counselors'));
    }

    /**
     * Return booked time slots (HH:MM) for a given counselor + date.
     * Also returns the student's booked slots for that date so the UI
     * can flag time conflicts for the student as well.
     *
     * GET /appointments/booked-slots?counselor_id=&date=&student_id=
     */
    public function bookedSlots(Request $request)
    {
        $request->validate([
            'counselor_id' => ['required', 'exists:counselors,counselor_id'],
            'date'         => ['required', 'date_format:Y-m-d'],
            'student_id'   => ['nullable', 'exists:students,student_id'],
        ]);

        $date        = $request->date;
        $counselorId = $request->counselor_id;
        $studentId   = $request->student_id;

        // Counselor's booked times on this date
        $counselorBooked = Appointment::where('counselor_id', $counselorId)
            ->whereDate('appointment_datetime', $date)
            ->whereIn('appointment_status', ['pending', 'approved'])
            ->pluck('appointment_datetime')
            ->map(fn ($dt) => Carbon::parse($dt)->format('H:i'))
            ->values()
            ->toArray();

        // Student's booked times on this date (if a student is selected)
        $studentBooked = [];
        if ($studentId) {
            $studentBooked = Appointment::where('student_id', $studentId)
                ->whereDate('appointment_datetime', $date)
                ->whereIn('appointment_status', ['pending', 'approved'])
                ->pluck('appointment_datetime')
                ->map(fn ($dt) => Carbon::parse($dt)->format('H:i'))
                ->values()
                ->toArray();
        }

        return response()->json([
            'counselor_booked' => $counselorBooked,
            'student_booked'   => $studentBooked,
        ]);
    }

    public function store(Request $request)
    {
        $this->authorize('create', Appointment::class);
        $user = Auth::user();

        // ── Determine who is booking for whom ──────────────────────────────
        // Counselors, office staff, and admins can book on behalf of a student.
        // Students always book for themselves.
        $isBookingForStudent = $user->isCounselor()
                            || $user->isOfficeStaff()
                            || $user->isSystemAdmin();

        $rules = [
            'counselor_id'         => ['required', 'exists:counselors,counselor_id'],
            'appointment_datetime' => ['required', 'date', 'after:now'],
            'concern_type'         => ['required', 'string', 'max:100'],
            'concern_description'  => ['nullable', 'string', 'max:1000'],
        ];

        if ($isBookingForStudent) {
            $rules['student_id'] = ['required', 'exists:students,student_id'];
        }

        $validated = $request->validate($rules);

        // Resolve the student ID
        if ($isBookingForStudent) {
            $studentId = (int) $validated['student_id'];
            $student   = Student::with('user')->findOrFail($studentId);
        } else {
            // Guard: the authenticated user must have a student profile
            if (! $user->student) {
                return back()
                    ->withErrors(['general' => 'No student profile is linked to your account. Please contact the administrator.'])
                    ->withInput();
            }
            $studentId = $user->student->student_id;
            $student   = $user->student->load('user');
        }

        $counselor = Counselor::with('user')->findOrFail($validated['counselor_id']);

        // ── Business Rule: Counselor slot must be free ─────────────────────
        $counselorOverlap = Appointment::where('counselor_id', $counselor->counselor_id)
            ->where('appointment_datetime', $validated['appointment_datetime'])
            ->whereIn('appointment_status', ['pending', 'approved'])
            ->exists();

        if ($counselorOverlap) {
            return back()
                ->withErrors(['appointment_datetime' => 'The counselor already has an appointment at this time. Please choose a different slot.'])
                ->withInput();
        }

        // ── Business Rule: Student must be free at that time ───────────────
        $studentOverlap = Appointment::where('student_id', $studentId)
            ->where('appointment_datetime', $validated['appointment_datetime'])
            ->whereIn('appointment_status', ['pending', 'approved'])
            ->exists();

        if ($studentOverlap) {
            return back()
                ->withErrors(['appointment_datetime' => 'The student already has an appointment at this time. Please choose a different slot.'])
                ->withInput();
        }

        // ── Create the appointment ─────────────────────────────────────────
        $appointment = Appointment::create([
            'counselor_id'        => $counselor->counselor_id,
            'student_id'          => $studentId,
            'appointment_datetime'=> $validated['appointment_datetime'],
            'concern_type'        => $validated['concern_type'],
            'concern_description' => $validated['concern_description'] ?? null,
            'appointment_status'  => 'pending',
            'duration_minutes'    => 60,
            'booked_by_user_id'   => Auth::id(), // track who made the booking
        ]);

        $formattedDate = Carbon::parse($validated['appointment_datetime'])->format('M d, Y h:i A');

        // ── Notify counselor ───────────────────────────────────────────────
        $bookerName = $user->full_name;
        $notifyMsg  = $isBookingForStudent
            ? "An appointment for {$student->user->full_name} was booked by {$bookerName} on {$formattedDate}."
            : "A new appointment request from {$bookerName} on {$formattedDate}.";

        Notification::create([
            'user_id'         => $counselor->user_id,
            'title'           => 'New Appointment Request',
            'message'         => $notifyMsg,
            'type'            => 'appointment',
            'notifiable_type' => Appointment::class,
            'notifiable_id'   => $appointment->appointment_id,
            'date_sent'       => now(),
        ]);

        // ── Notify student (if booked by staff/counselor) ──────────────────
        if ($isBookingForStudent) {
            Notification::create([
                'user_id'         => $student->user_id,
                'title'           => 'Appointment Booked for You',
                'message'         => "An appointment has been scheduled for you with {$counselor->user->full_name} on {$formattedDate} by {$bookerName}.",
                'type'            => 'appointment',
                'notifiable_type' => Appointment::class,
                'notifiable_id'   => $appointment->appointment_id,
                'date_sent'       => now(),
            ]);
        }

        return redirect()
            ->route('appointments.show', $appointment)
            ->with('success', 'Appointment request submitted successfully. Awaiting approval.');
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

        if (! $appointment->isPending()) {
            return back()->with('error', 'Only pending appointments can be approved.');
        }

        $appointment->update([
            'appointment_status' => 'approved',
            'approved_by'        => Auth::id(),
            'approved_at'        => now(),
        ]);

        Notification::create([
            'user_id'         => $appointment->student->user_id,
            'title'           => 'Appointment Approved',
            'message'         => 'Your appointment on ' . $appointment->appointment_datetime->format('M d, Y h:i A') . ' has been approved.',
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

        if (! $appointment->canBeCancelled()) {
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

        $notifyUserId = Auth::user()->isStudent()
            ? $appointment->counselor->user_id
            : $appointment->student->user_id;

        Notification::create([
            'user_id'         => $notifyUserId,
            'title'           => 'Appointment Cancelled',
            'message'         => 'Appointment on ' . $appointment->appointment_datetime->format('M d, Y h:i A') . ' has been cancelled. Reason: ' . $validated['cancellation_reason'],
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

        if (! $appointment->isApproved()) {
            return back()->with('error', 'Only approved appointments can start a session.');
        }

        DB::transaction(function () use ($appointment) {
            Session::create([
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

    // ──────────────────────────────────────────────────────────────────────────
    // Private helpers
    // ──────────────────────────────────────────────────────────────────────────

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
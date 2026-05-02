<?php

namespace App\Http\Controllers;

use App\Models\Appointment;
use App\Models\Session;
use App\Models\Student;
use App\Models\Counselor;
use App\Models\Notification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        $data = [];

        if ($user->isStudent()) {
            $student = $user->student;
            $data = [
                'upcoming_appointments' => $student ? $student->appointments()
                    ->with('counselor.user')
                    ->whereIn('appointment_status', ['pending', 'approved'])
                    ->where('appointment_datetime', '>=', now())
                    ->orderBy('appointment_datetime')
                    ->take(5)->get() : collect(),
                'past_sessions' => $student ? $student->sessions()
                    ->with('counselor.user')
                    ->where('session_status', 'completed')
                    ->orderByDesc('session_datetime')
                    ->take(5)->get() : collect(),
                'total_sessions' => $student ? $student->sessions()->where('session_status', 'completed')->count() : 0,
                'pending_count'  => $student ? $student->appointments()->where('appointment_status', 'pending')->count() : 0,
            ];

        } elseif ($user->isCounselor()) {
            $counselor = $user->counselor;
            $data = [
                'todays_appointments' => $counselor ? $counselor->appointments()
                    ->with('student.user')
                    ->whereDate('appointment_datetime', today())
                    ->whereIn('appointment_status', ['approved', 'pending'])
                    ->orderBy('appointment_datetime')
                    ->get() : collect(),
                'pending_approvals' => $counselor ? $counselor->appointments()
                    ->where('appointment_status', 'pending')
                    ->with('student.user')
                    ->count() : 0,
                'total_sessions_this_month' => $counselor ? $counselor->sessions()
                    ->whereMonth('session_datetime', now()->month)
                    ->where('session_status', 'completed')
                    ->count() : 0,
                'total_students_served' => $counselor ? $counselor->sessions()
                    ->distinct('student_id')
                    ->count('student_id') : 0,
                'recent_sessions' => $counselor ? $counselor->sessions()
                    ->with('student.user')
                    ->orderByDesc('session_datetime')
                    ->take(5)->get() : collect(),
            ];

        } else {
            // Admin / Office Staff / School Admin
            $data = [
                'total_students'        => Student::count(),
                'total_counselors'      => Counselor::count(),
                'todays_appointments'   => Appointment::whereDate('appointment_datetime', today())->count(),
                'pending_appointments'  => Appointment::where('appointment_status', 'pending')->count(),
                'sessions_this_month'   => Session::whereMonth('session_datetime', now()->month)->where('session_status', 'completed')->count(),
                'recent_appointments'   => Appointment::with(['student.user', 'counselor.user'])
                    ->orderByDesc('created_at')->take(10)->get(),
                'appointment_stats' => [
                    'pending'   => Appointment::where('appointment_status', 'pending')->count(),
                    'approved'  => Appointment::where('appointment_status', 'approved')->count(),
                    'completed' => Appointment::where('appointment_status', 'completed')->count(),
                    'cancelled' => Appointment::where('appointment_status', 'cancelled')->count(),
                ],
            ];
        }

        $data['unread_notifications'] = $user->unreadNotifications()->orderByDesc('date_sent')->take(5)->get();
        $data['unread_count'] = $user->unreadNotifications()->count();

        return view('dashboard.index', compact('data', 'user'));
    }
}

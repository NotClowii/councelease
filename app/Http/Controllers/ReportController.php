<?php

namespace App\Http\Controllers;

use App\Models\Report;
use App\Models\Appointment;
use App\Models\Session;
use App\Models\Student;
use App\Models\Counselor;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ReportController extends Controller
{
    public function index()
    {
        $user = Auth::user();

        if (!$user->canAccessReports()) {
            abort(403, 'You do not have permission to view reports.');
        }

        $reports = Report::with('generatedByUser')
            ->orderByDesc('created_at')
            ->paginate(15);

        return view('reports.index', compact('reports'));
    }

    public function generate(Request $request)
    {
        $user = Auth::user();

        if (!$user->canAccessReports()) abort(403);

        $validated = $request->validate([
            'report_type' => ['required', 'in:session_summary,counselor_workload,student_visit,monthly,annual'],
            'date_from'   => ['nullable', 'date'],
            'date_to'     => ['nullable', 'date', 'after_or_equal:date_from'],
            'counselor_id'=> ['nullable', 'exists:counselors,counselor_id'],
        ]);

        $dateFrom = $validated['date_from'] ? \Carbon\Carbon::parse($validated['date_from']) : now()->startOfMonth();
        $dateTo   = $validated['date_to']   ? \Carbon\Carbon::parse($validated['date_to'])   : now()->endOfMonth();

        $reportData = $this->compileReportData($validated['report_type'], $dateFrom, $dateTo, $validated['counselor_id'] ?? null);

        // Store report record
        $report = Report::create([
            'report_title'    => ucwords(str_replace('_', ' ', $validated['report_type'])) . ' Report',
            'report_type'     => $validated['report_type'],
            'generated_by'    => $user->user_id,
            'date_from'       => $dateFrom,
            'date_to'         => $dateTo,
            'filters_applied' => $validated,
            'status'          => 'completed',
        ]);

        return view('reports.show', compact('report', 'reportData', 'dateFrom', 'dateTo'));
    }

    private function compileReportData(string $type, $from, $to, ?int $counselorId): array
    {
        $data = [];

        switch ($type) {
            case 'session_summary':
                $query = Session::whereBetween('session_datetime', [$from, $to]);
                if ($counselorId) $query->where('counselor_id', $counselorId);
                $data['total_sessions']    = $query->count();
                $data['completed']         = (clone $query)->where('session_status', 'completed')->count();
                $data['cancelled']         = (clone $query)->where('session_status', 'cancelled')->count();
                $data['sessions_by_month'] = (clone $query)->selectRaw('MONTH(session_datetime) as month, COUNT(*) as count')
                    ->groupBy('month')->pluck('count', 'month')->toArray();
                break;

            case 'counselor_workload':
                $data['counselors'] = Counselor::with('user')
                    ->withCount(['sessions' => fn($q) => $q->whereBetween('session_datetime', [$from, $to])])
                    ->withCount(['appointments' => fn($q) => $q->whereBetween('appointment_datetime', [$from, $to])])
                    ->get();
                break;

            case 'student_visit':
                $data['students'] = Student::with('user')
                    ->withCount(['sessions' => fn($q) => $q->whereBetween('session_datetime', [$from, $to])])
                    ->having('sessions_count', '>', 0)
                    ->get();
                break;

            case 'monthly':
            case 'annual':
                $data['total_appointments'] = Appointment::whereBetween('appointment_datetime', [$from, $to])->count();
                $data['total_sessions']     = Session::whereBetween('session_datetime', [$from, $to])->count();
                $data['new_students']       = Student::whereBetween('created_at', [$from, $to])->count();
                $data['appointment_status'] = Appointment::whereBetween('appointment_datetime', [$from, $to])
                    ->selectRaw('appointment_status, COUNT(*) as count')
                    ->groupBy('appointment_status')
                    ->pluck('count', 'appointment_status')->toArray();
                break;
        }

        return $data;
    }
}

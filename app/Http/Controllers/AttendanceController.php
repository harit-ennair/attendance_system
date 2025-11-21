<?php

namespace App\Http\Controllers;

use App\Models\Attendance;
use App\Models\Student;
use Illuminate\Http\Request;
use Carbon\Carbon;

class AttendanceController extends Controller
{
    /**
     * Display a listing of attendance records.
     */
    public function index(Request $request)
    {
        $query = Attendance::with(['student.user']);

        // Filter by date if provided
        if ($request->has('date')) {
            $query->forDate($request->date);
        }

        // Filter by status if provided
        if ($request->has('status')) {
            $query->withStatus($request->status);
        }

        // Filter by student if provided
        if ($request->has('student_id')) {
            $query->where('student_id', $request->student_id);
        }

        $attendance = $query->orderBy('attendance_date', 'desc')
                          ->orderBy('created_at', 'desc')
                          ->paginate(20);

        return response()->json($attendance);
    }

    /**
     * Mark attendance for a student.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'student_id' => 'required|exists:students,id',
            'attendance_date' => 'required|date',
            'check_in_time' => 'nullable|date_format:H:i:s',
            'check_out_time' => 'nullable|date_format:H:i:s|after:check_in_time',
            'status' => 'required|in:present,absent,late,excused',
            'notes' => 'nullable|string|max:500',
            'session_type' => 'nullable|string|max:50',
        ]);

        // Check if attendance already exists for this student, date, and session
        $existingAttendance = Attendance::where('student_id', $validated['student_id'])
                                      ->where('attendance_date', $validated['attendance_date'])
                                      ->where('session_type', $validated['session_type'] ?? null)
                                      ->first();

        if ($existingAttendance) {
            return response()->json([
                'message' => 'Attendance already marked for this student on this date and session'
            ], 422);
        }

        $attendance = Attendance::create($validated);

        return response()->json([
            'message' => 'Attendance marked successfully',
            'attendance' => $attendance->load('student.user')
        ], 201);
    }

    /**
     * Display the specified attendance record.
     */
    public function show(Attendance $attendance)
    {
        return response()->json($attendance->load('student.user'));
    }

    /**
     * Update the specified attendance record.
     */
    public function update(Request $request, Attendance $attendance)
    {
        $validated = $request->validate([
            'check_in_time' => 'nullable|date_format:H:i:s',
            'check_out_time' => 'nullable|date_format:H:i:s|after:check_in_time',
            'status' => 'sometimes|in:present,absent,late,excused',
            'notes' => 'nullable|string|max:500',
        ]);

        $attendance->update($validated);

        return response()->json([
            'message' => 'Attendance updated successfully',
            'attendance' => $attendance->load('student.user')
        ]);
    }

    /**
     * Remove the specified attendance record.
     */
    public function destroy(Attendance $attendance)
    {
        $attendance->delete();
        return response()->json(['message' => 'Attendance record deleted successfully']);
    }

    /**
     * Mark check-in for a student.
     */
    public function checkIn(Request $request)
    {
        $validated = $request->validate([
            'student_id' => 'required|exists:students,id',
            'session_type' => 'nullable|string|max:50',
        ]);

        $today = Carbon::today();
        $currentTime = Carbon::now()->format('H:i:s');

        $attendance = Attendance::updateOrCreate(
            [
                'student_id' => $validated['student_id'],
                'attendance_date' => $today,
                'session_type' => $validated['session_type'] ?? null,
            ],
            [
                'check_in_time' => $currentTime,
                'status' => 'present',
            ]
        );

        return response()->json([
            'message' => 'Check-in recorded successfully',
            'attendance' => $attendance->load('student.user')
        ]);
    }

    /**
     * Mark check-out for a student.
     */
    public function checkOut(Request $request)
    {
        $validated = $request->validate([
            'student_id' => 'required|exists:students,id',
            'session_type' => 'nullable|string|max:50',
        ]);

        $today = Carbon::today();
        $currentTime = Carbon::now()->format('H:i:s');

        $attendance = Attendance::where('student_id', $validated['student_id'])
                               ->where('attendance_date', $today)
                               ->where('session_type', $validated['session_type'] ?? null)
                               ->first();

        if (!$attendance) {
            return response()->json([
                'message' => 'No check-in record found for today'
            ], 404);
        }

        $attendance->update(['check_out_time' => $currentTime]);

        return response()->json([
            'message' => 'Check-out recorded successfully',
            'attendance' => $attendance->load('student.user')
        ]);
    }

    /**
     * Get attendance report for a specific date range.
     */
    public function report(Request $request)
    {
        $validated = $request->validate([
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
            'student_id' => 'nullable|exists:students,id',
        ]);

        $query = Attendance::with(['student.user'])
                          ->whereBetween('attendance_date', [$validated['start_date'], $validated['end_date']]);

        if (isset($validated['student_id'])) {
            $query->where('student_id', $validated['student_id']);
        }

        $attendance = $query->orderBy('attendance_date', 'desc')->get();

        // Generate summary statistics
        $summary = [
            'total_records' => $attendance->count(),
            'present' => $attendance->where('status', 'present')->count(),
            'absent' => $attendance->where('status', 'absent')->count(),
            'late' => $attendance->where('status', 'late')->count(),
            'excused' => $attendance->where('status', 'excused')->count(),
        ];

        return response()->json([
            'attendance' => $attendance,
            'summary' => $summary,
            'period' => [
                'start_date' => $validated['start_date'],
                'end_date' => $validated['end_date']
            ]
        ]);
    }
}

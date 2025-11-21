<?php

namespace App\Http\Controllers;

use App\Models\Student;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class StudentController extends Controller
{
    /**
     * Display a listing of the students.
     */
    public function index()
    {
        $students = Student::with('user')->paginate(15);
        return response()->json($students);
    }

    /**
     * Show the form for creating a new student.
     */
    public function create()
    {
        return response()->json(['message' => 'Create student form']);
    }

    /**
     * Store a newly created student in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email_um6p' => 'required|email|unique:students,email_um6p',
            'health_insurance_number' => 'required|string|unique:students,health_insurance_number',
            'cin' => 'required|string|unique:students,cin',
            'age' => 'required|integer|min:16|max:50',
            'date_naissance' => 'nullable|date',
            'ville' => 'required|string|max:255',
            'etudes' => 'required|string|max:255',
            'telephone' => 'required|string|max:20',
        ]);

        // Create user account for student
        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email_um6p'],
            'password' => Hash::make('password123'), // Default password
            'role_id' => 3, // Assuming student role ID is 3
        ]);

        // Create student record
        $student = Student::create([
            'email_um6p' => $validated['email_um6p'],
            'health_insurance_number' => $validated['health_insurance_number'],
            'cin' => $validated['cin'],
            'age' => $validated['age'],
            'date_naissance' => $validated['date_naissance'],
            'ville' => $validated['ville'],
            'etudes' => $validated['etudes'],
            'telephone' => $validated['telephone'],
            'user_id' => $user->id,
        ]);

        return response()->json([
            'message' => 'Student created successfully',
            'student' => $student->load('user')
        ], 201);
    }

    /**
     * Display the specified student.
     */
    public function show(Student $student)
    {
        return response()->json($student->load(['user', 'attendances']));
    }

    /**
     * Show the form for editing the specified student.
     */
    public function edit(Student $student)
    {
        return response()->json($student->load('user'));
    }

    /**
     * Update the specified student in storage.
     */
    public function update(Request $request, Student $student)
    {
        $validated = $request->validate([
            'name' => 'sometimes|string|max:255',
            'email_um6p' => ['sometimes', 'email', Rule::unique('students', 'email_um6p')->ignore($student->id)],
            'health_insurance_number' => ['sometimes', 'string', Rule::unique('students', 'health_insurance_number')->ignore($student->id)],
            'cin' => ['sometimes', 'string', Rule::unique('students', 'cin')->ignore($student->id)],
            'age' => 'sometimes|integer|min:16|max:50',
            'date_naissance' => 'nullable|date',
            'ville' => 'sometimes|string|max:255',
            'etudes' => 'sometimes|string|max:255',
            'telephone' => 'sometimes|string|max:20',
        ]);

        // Update user if name or email changed
        if (isset($validated['name']) || isset($validated['email_um6p'])) {
            $student->user->update([
                'name' => $validated['name'] ?? $student->user->name,
                'email' => $validated['email_um6p'] ?? $student->user->email,
            ]);
        }

        // Update student record
        $student->update($validated);

        return response()->json([
            'message' => 'Student updated successfully',
            'student' => $student->load('user')
        ]);
    }

    /**
     * Remove the specified student from storage.
     */
    public function destroy(Student $student)
    {
        // Delete associated user account
        $student->user->delete();
        
        // Student will be deleted automatically due to cascade
        return response()->json(['message' => 'Student deleted successfully']);
    }
}

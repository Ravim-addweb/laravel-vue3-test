<?php
// app/Http/Controllers/StudentController.php

namespace App\Http\Controllers;

use App\Domain\DTOs\StudentDTO;
use App\Domain\UseCases\Student\{
    CreateStudentUseCase,
    DeleteStudentUseCase,
    FetchAllStudentsUseCase,
    FetchStudentUseCase,
};
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Validation\Rule;

class StudentController extends Controller
{
    /**
     * Fetch all students with pagination.
     *
     * @param FetchAllStudentsUseCase $useCase
     * @return JsonResponse
     */
    public function index(FetchAllStudentsUseCase $useCase, Request $request): JsonResponse
    {
        $request->validate([
            'per_page' => 'nullable|integer|min:1', // per_page should be an integer greater than or equal to 1
        ]);

        // Get per_page parameter from request or use default value
        $perPage = $request->input('per_page', 10); // Default to 10 if per_page is not provided or not an integer

        try {
            $students = $useCase->execute()->paginate($perPage);
            return response()->json($students);
        } catch (\Exception $e) {
            Log::error('Error fetching students: ' . $e->getMessage());
            return response()->json(['error' => 'Failed to fetch students'], 500);
        }
    }

    /**
     * Fetch a student by ID.
     *
     * @param FetchStudentUseCase $useCase
     * @param int $id
     * @return JsonResponse
     */
    public function show(FetchStudentUseCase $useCase, int $id): JsonResponse
    {
        try {
            $student = $useCase->execute($id);
            if (!$student) {
                return response()->json(['error' => 'Student not found'], 404);
            }
            return response()->json($student);
        } catch (ModelNotFoundException $e) {
            Log::error('Error Student Not found: ' . $e->getMessage());
            return response()->json(['error' => 'Student not found'], 404);
        } catch (\Exception $e) {
            Log::error('Error fetching student: ' . $e->getMessage());
            return response()->json(['error' => 'Failed to fetch student'], 500);
        }
    }

    /**
     * Create a new student.
     *
     * @param Request $request
     * @param CreateStudentUseCase $useCase
     * @return JsonResponse
     */
    public function store(Request $request, CreateStudentUseCase $useCase): JsonResponse
    {
        try {
            $request->validate([
                'username' => 'required|string',
                'email' => [
                    'required',
                    'email',
                    Rule::unique('students', 'email')
                ],
            ]);

            $studentDTO = new StudentDTO($request->username, $request->email);
            $student = $useCase->execute($studentDTO);

            return response()->json($student, 201);
        } catch (ValidationException $e) {
            return response()->json(['error' => $e->getMessage()], 400);
        } catch (\Exception $e) {
            Log::error('Error creating student: ' . $e->getMessage());
            return response()->json(['error' => 'Failed to create student'], 500);
        }
    }

    /**
     * Delete a student by ID.
     *
     * @param DeleteStudentUseCase $useCase
     * @param int $id
     * @return JsonResponse
     */
    public function destroy(DeleteStudentUseCase $useCase, int $id): JsonResponse
    {
        try {
            $useCase->execute($id);
            return response()->json(['message' => 'Student deleted successfully'], 200);
        } catch (ModelNotFoundException $e) {
            Log::error('Error Student Not found: ' . $e->getMessage());
            return response()->json(['error' => 'Student not found'], 404);
        } catch (\Exception $e) {
            Log::error('Error deleting student: ' . $e->getMessage());
            return response()->json(['error' => 'Failed to delete student'], 500);
        }
    }
}

<?php

namespace Tests\Unit\Controllers;

use App\Models\Student;
use App\Domain\UseCases\Student\{
    CreateStudentUseCase,
    DeleteStudentUseCase,
    FetchAllStudentsUseCase,
    FetchStudentUseCase,
};
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Validation\ValidationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Tests\TestCase;

class StudentControllerTest extends TestCase
{
    use RefreshDatabase;
    use WithFaker;

    public function testIndexReturnsPaginatedStudents()
    {
        $perPage = 5;
        $students = Student::factory()->count($perPage * 2)->create();
        $response = $this->json('GET', '/api/students?per_page=' . $perPage);
        $response->assertStatus(200);
        $response->assertJsonStructure(['data', 'links', 'per_page']);
        $response->assertJsonCount($perPage, 'data');
    }

    public function testShowReturnsStudentById()
    {
        $student = Student::factory()->create();
        $response = $this->json('GET', '/api/students/' . $student->id);
        $response->assertStatus(200);
        $response->assertJson(['username' => $student->username, 'email' => $student->email]);
    }

    public function testShowReturnsNotFoundForInvalidId()
    {
        $response = $this->json('GET', '/api/students/' . $this->faker->randomNumber());
        $response->assertStatus(404);
    }

    public function testStoreCreatesNewStudent()
    {
        $studentData = [
            'username' => $this->faker->userName,
            'email' => $this->faker->email
        ];
        $response = $this->json('POST', '/api/students', $studentData);
        $response->assertStatus(201);
        $response->assertJson($studentData);
    }

    public function testStoreReturnsErrorForInvalidData()
    {
        $response = $this->json('POST', '/api/students', []);
        $response->assertStatus(400);
    }

    public function testStoreReturnsErrorForDuplicateEmail()
    {
        $student = Student::factory()->create();
        $response = $this->json('POST', '/api/students', ['username' => $this->faker->userName, 'email' => $student->email]);
        $response->assertStatus(400);
    }

    public function testDestroyDeletesStudentById()
    {
        $student = Student::factory()->create();
        $response = $this->json('DELETE', '/api/students/' . $student->id);
        $response->assertStatus(200);
        $response->assertJson(['message' => 'Student deleted successfully']);
    }

    public function testDestroyReturnsNotFoundForInvalidId()
    {
        $response = $this->json('DELETE', '/api/students/' . $this->faker->randomNumber());
        $response->assertStatus(404);
    }

    public function testIndexReturnsErrorForInvalidPerPageParameter()
    {
        $response = $this->json('GET', '/api/students?per_page=invalid');
        $response->assertStatus(422);
    }

    public function testIndexReturnsDefaultPerPageIfPerPageNotInteger()
    {
        Student::factory()->count(100)->create();
        $response = $this->json('GET', '/api/students');
        $response->assertStatus(200);
        $response->assertJsonCount(10, 'data'); // Default per_page is 10
    }

    public function testIndexReturnsInternalServerErrorOnUseCaseFailure()
    {
        // Mocking the UseCase to throw an exception
        $this->mock(FetchAllStudentsUseCase::class, function ($mock) {
            $mock->shouldReceive('execute')->andThrow(new \Exception('Some error occurred'));
        });

        $response = $this->json('GET', '/api/students');
        $response->assertStatus(500);
    }

    public function testShowReturnsInternalServerErrorOnUseCaseFailure()
    {
        // Mocking the UseCase to throw an exception
        $this->mock(FetchStudentUseCase::class, function ($mock) {
            $mock->shouldReceive('execute')->andThrow(new \Exception('Some error occurred'));
        });

        $response = $this->json('GET', '/api/students/1');
        $response->assertStatus(500);
    }

    public function testStoreReturnsValidationErrorForInvalidEmail()
    {
        $studentData = [
            'username' => $this->faker->userName,
            'email' => 'invalid_email'
        ];

        $response = $this->json('POST', '/api/students', $studentData);
        $response->assertStatus(400);
    }

    public function testDeleteNonExistingStudentReturnsNotFound()
    {
        $nonExistingId = 9999;
        $response = $this->json('DELETE', '/api/students/' . $nonExistingId);
        $response->assertStatus(404);
    }

    public function testDeleteStudentErrorReturnsInternalServerError()
    {
        // Mocking the DeleteStudentUseCase to throw an exception
        $this->mock(DeleteStudentUseCase::class, function ($mock) {
            $mock->shouldReceive('execute')->andThrow(new \Exception('Delete failed'));
        });

        $response = $this->json('DELETE', '/api/students/' . $this->faker->randomNumber());
        $response->assertStatus(500);
    }

    public function testIndexReturnsInternalServerErrorOnPaginationError()
    {
        // Mocking the FetchAllStudentsUseCase to throw an exception when paginating
        $this->mock(FetchAllStudentsUseCase::class, function ($mock) {
            $mock->shouldReceive('execute')->andReturnSelf();
            $mock->shouldReceive('paginate')->andThrow(new \Exception('Pagination failed'));
        });

        $response = $this->json('GET', '/api/students');
        $response->assertStatus(500);
    }

    public function testIndexHandlesPaginationError()
    {
        // Mocking the FetchAllStudentsUseCase to throw an exception when paginating
        $this->mock(FetchAllStudentsUseCase::class, function ($mock) {
            $mock->shouldReceive('execute')->andReturnSelf();
            $mock->shouldReceive('paginate')->andThrow(new \Exception('Pagination failed'));
        });

        $response = $this->json('GET', '/api/students');
        $response->assertStatus(500);
    }

    public function testStoreHandlesValidationException()
    {
        // Mocking the CreateStudentUseCase to bypass execution
        $this->mock(CreateStudentUseCase::class, function ($mock) {});

        // Sending invalid data that triggers a validation exception
        $response = $this->json('POST', '/api/students', []);
        $response->assertStatus(400);
    }

    public function testStoreHandlesDatabaseError()
    {
        // Mocking the CreateStudentUseCase to throw an exception when executing
        $this->mock(CreateStudentUseCase::class, function ($mock) {
            $mock->shouldReceive('execute')->andThrow(new \Exception('Database error'));
        });

        // Sending valid data
        $response = $this->json('POST', '/api/students', [
            'username' => 'test_user',
            'email' => 'test@example.com'
        ]);
        $response->assertStatus(500);
    }

    public function testDestroyHandlesModelNotFoundException()
    {
        // Mocking the DeleteStudentUseCase to throw ModelNotFoundException
        $this->mock(DeleteStudentUseCase::class, function ($mock) {
            $mock->shouldReceive('execute')->andThrow(new ModelNotFoundException());
        });

        $response = $this->json('DELETE', '/api/students/123');
        $response->assertStatus(404);
    }

    public function testDestroyHandlesOtherExceptions()
    {
        // Mocking the DeleteStudentUseCase to throw an exception other than ModelNotFoundException
        $this->mock(DeleteStudentUseCase::class, function ($mock) {
            $mock->shouldReceive('execute')->andThrow(new \Exception('Database error'));
        });

        $response = $this->json('DELETE', '/api/students/123');
        $response->assertStatus(500);
    }
    public function testShowHandlesOtherExceptions()
    {
        // Mocking the FetchStudentUseCase to throw an exception other than ModelNotFoundException
        $this->mock(FetchStudentUseCase::class, function ($mock) {
            $mock->shouldReceive('execute')->andThrow(new \Exception('Database error'));
        });

        $response = $this->json('GET', '/api/students/123');
        $response->assertStatus(500);
    }

    public function testStoreHandlesOtherExceptions()
    {
        // Mocking the CreateStudentUseCase to throw an exception other than validation or database error
        $this->mock(CreateStudentUseCase::class, function ($mock) {
            $mock->shouldReceive('execute')->andThrow(new \Exception('Unexpected error'));
        });

        // Sending valid data
        $response = $this->json('POST', '/api/students', [
            'username' => 'test_user',
            'email' => 'test@example.com'
        ]);
        $response->assertStatus(500);
    }
}

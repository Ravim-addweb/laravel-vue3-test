<?php
namespace Tests\Feature;

use App\Models\Student;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class StudentFeatureTest extends TestCase
{
    use RefreshDatabase;

    public function test_can_list_students()
    {
        // Create some students
        $students = Student::factory()->count(3)->create();

        // Hit the API endpoint to list students
        $response = $this->getJson('/api/students');

        // Assert HTTP status code
        $response->assertStatus(200);

        $response->assertJsonStructure([
            'current_page',
            'data',
            'first_page_url',
            'from',
            'last_page',
            'last_page_url',
            'links',
            'next_page_url',
            'path',
            'per_page',
            'prev_page_url',
            'to',
            'total'
        ]);
    }

    public function test_can_create_student()
    {
        // Student data
        $studentData = [
            'username' => 'testuser',
            'email' => 'test@example.com',
        ];

        // Hit the API endpoint to create a student
        $response = $this->postJson('/api/students', $studentData);

        // Assert HTTP status code
        $response->assertStatus(201);

        // Assert the response structure
        $response->assertJsonStructure([
            'id', 'username', 'email', 'created_at', 'updated_at'
        ]);

        // Assert that the created student matches the data provided
        $response->assertJson($studentData);
    }

    public function test_can_delete_student()
    {
        // Create a student
        $student = Student::factory()->create();

        // Hit the API endpoint to delete the student
        $response = $this->deleteJson("/api/students/{$student->id}");

        // Assert HTTP status code
        $response->assertStatus(200);
    }


    public function test_can_create_student_with_invalid_data()
    {
        // Attempt to create a student with invalid data (missing required fields)
        $response = $this->postJson('/api/students', []);

        // Assert HTTP status code 422 (Unprocessable Entity)
        $response->assertStatus(400);

        // Assert response structure
        $response->assertJsonStructure([
            'error',
        ]);
    }

    public function test_cannot_delete_nonexistent_student()
    {
        // Attempt to delete a student that does not exist
        $response = $this->deleteJson('/api/students/999');

        // Assert HTTP status code 404 (Not Found)
        $response->assertStatus(404);

        // Assert response structure
        $response->assertJsonStructure([
            'error'
        ]);
    }
}

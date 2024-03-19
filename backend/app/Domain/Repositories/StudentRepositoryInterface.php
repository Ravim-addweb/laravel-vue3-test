<?php
namespace App\Domain\Repositories;

use App\Domain\DTOs\StudentDTO;
use App\Models\Student;
use Illuminate\Support\Collection;

interface StudentRepositoryInterface
{
    /**
     * Fetch all students.
     *
     * @return Collection
     */
    public function fetchAll(): Collection;

    /**
     * Fetch a student by ID.
     *
     * @param int $id
     * @return Student|null
     */
    public function fetchById(int $id): ?Student;

    /**
     * Create a new student.
     *
     * @param StudentDTO $studentDTO
     * @return Student
     */
    public function create(StudentDTO $studentDTO): Student;

    /**
     * Delete a student by ID.
     *
     * @param int $id
     * @return void
     */
    public function delete(int $id): void;
}

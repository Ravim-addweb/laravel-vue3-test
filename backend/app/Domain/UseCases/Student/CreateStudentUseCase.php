<?php
namespace App\Domain\UseCases\Student;

use App\Domain\DTOs\StudentDTO;
use App\Models\Student;

class CreateStudentUseCase
{
    public function execute(StudentDTO $studentDTO)
    {
        return Student::create([
            'username' => $studentDTO->getUsername(),
            'email' => $studentDTO->getEmail(),
        ]);
    }
}
<?php
namespace App\Domain\UseCases\Student;

use App\Models\Student;

class FetchStudentUseCase
{
    public function execute($id)
    {
        return Student::findOrFail($id);
    }
}

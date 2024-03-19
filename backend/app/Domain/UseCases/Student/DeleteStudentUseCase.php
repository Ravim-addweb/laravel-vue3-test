<?php

namespace App\Domain\UseCases\Student;

use App\Models\Student;

class DeleteStudentUseCase
{
    public function execute($id)
    {
        $student = Student::findOrFail($id);
        $student->delete();
    }
}
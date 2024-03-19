<?php

namespace App\Domain\UseCases\Student;

use App\Models\Student;

class FetchAllStudentsUseCase
{
    public function execute()
    {
        return Student::query();
    }
}
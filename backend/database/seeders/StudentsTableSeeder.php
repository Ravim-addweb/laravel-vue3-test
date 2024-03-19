<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Student;

class StudentsTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Define the number of students to create
        $numberOfStudents = 100;

        // Use the factory to create student records
        Student::factory()->count($numberOfStudents)->create();
    }
}

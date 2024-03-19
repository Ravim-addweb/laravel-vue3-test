<?php

namespace Tests\Unit\DTO;

use App\Domain\DTOs\StudentDTO;
use PHPUnit\Framework\TestCase;

class StudentDTOTest extends TestCase
{
    public function testCreateStudentDTO()
    {
        $username = 'testuser';
        $email = 'test@example.com';
        $studentDTO = new StudentDTO($username, $email);

        $this->assertEquals($username, $studentDTO->getUsername());
        $this->assertEquals($email, $studentDTO->getEmail());
    }

    public function testSetters()
    {
        $studentDTO = new StudentDTO('oldusername', 'old@example.com');

        $studentDTO->setUsername('newusername');
        $this->assertEquals('newusername', $studentDTO->getUsername());

        $studentDTO->setEmail('new@example.com');
        $this->assertEquals('new@example.com', $studentDTO->getEmail());
    }

    public function testEdgeCases()
    {
        // Test empty string for username
        $studentDTO = new StudentDTO('', 'test@example.com');
        $this->assertEquals('', $studentDTO->getUsername());

        // Test special characters in email
        $studentDTO = new StudentDTO('testuser', 'test@example.com!@#');
        $this->assertEquals('test@example.com!@#', $studentDTO->getEmail());
    }
}

<?php

namespace Tests\Unit\Domain\Management;

use App\Domain\Management\Import\PartImportValidator;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

class PartImportValidatorTest extends TestCase
{
    private PartImportValidator $validator;

    protected function setUp(): void
    {
        $this->validator = new PartImportValidator;
    }

    #[Test]
    public function valid_row_returns_empty_error_array(): void
    {
        $row = ['name' => 'Test', 'attack' => 80, 'stamina' => 50];

        $errors = $this->validator->validate($row, ['name', 'attack', 'stamina'], ['attack', 'stamina']);

        $this->assertEmpty($errors);
    }

    #[Test]
    public function missing_required_field_produces_error(): void
    {
        $row = ['attack' => 80];

        $errors = $this->validator->validate($row, ['name', 'attack'], ['attack']);

        $this->assertCount(1, $errors);
        $this->assertStringContainsString("'name'", $errors[0]);
    }

    #[Test]
    public function performance_field_above_100_produces_error(): void
    {
        $row = ['name' => 'Test', 'attack' => 150];

        $errors = $this->validator->validate($row, ['name', 'attack'], ['attack']);

        $this->assertCount(1, $errors);
        $this->assertStringContainsString("'attack'", $errors[0]);
    }

    #[Test]
    public function performance_field_below_0_produces_error(): void
    {
        $row = ['name' => 'Test', 'attack' => -5];

        $errors = $this->validator->validate($row, ['name', 'attack'], ['attack']);

        $this->assertCount(1, $errors);
    }

    #[Test]
    public function performance_field_at_boundary_values_is_valid(): void
    {
        $row = ['name' => 'Test', 'attack' => 0, 'defense' => 100];

        $errors = $this->validator->validate($row, ['name', 'attack', 'defense'], ['attack', 'defense']);

        $this->assertEmpty($errors);
    }

    #[Test]
    public function non_numeric_performance_field_produces_error(): void
    {
        $row = ['name' => 'Test', 'attack' => 'strong'];

        $errors = $this->validator->validate($row, ['name', 'attack'], ['attack']);

        $this->assertCount(1, $errors);
    }

    #[Test]
    public function empty_string_required_field_produces_error(): void
    {
        $row = ['name' => '', 'attack' => 80];

        $errors = $this->validator->validate($row, ['name', 'attack'], ['attack']);

        $this->assertCount(1, $errors);
        $this->assertStringContainsString("'name'", $errors[0]);
    }
}

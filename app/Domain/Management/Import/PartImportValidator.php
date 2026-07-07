<?php

namespace App\Domain\Management\Import;

final class PartImportValidator
{
    /**
     * Validate a single row from a JSON import file.
     *
     * Checks required fields are present and non-empty, and that
     * performance fields are numeric values in the 0–100 range.
     * Does NOT touch the database — duplicate checking is the responsibility
     * of the Livewire layer that has access to Repositories.
     *
     * @param  array<string, mixed>  $row
     * @param  string[]  $requiredKeys
     * @param  string[]  $performanceKeys  values must be 0-100
     * @return string[]  list of error messages; empty means valid
     */
    public function validate(array $row, array $requiredKeys, array $performanceKeys): array
    {
        $errors = [];

        foreach ($requiredKeys as $key) {
            if (! array_key_exists($key, $row) || $row[$key] === null || $row[$key] === '') {
                $errors[] = "Field '{$key}' wajib diisi.";
            }
        }

        foreach ($performanceKeys as $key) {
            if (isset($row[$key]) && (! is_numeric($row[$key]) || $row[$key] < 0 || $row[$key] > 100)) {
                $errors[] = "Field '{$key}' harus angka 0-100.";
            }
        }

        return $errors;
    }
}

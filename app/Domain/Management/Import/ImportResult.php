<?php

namespace App\Domain\Management\Import;

final class ImportResult
{
    /**
     * @param  array<int, string>  $errors  format: "Baris 3 ('Wizard Rod'): pesan error"
     */
    public function __construct(
        public readonly int $successCount,
        public readonly int $skippedCount,
        public readonly int $failedCount,
        public readonly array $errors,
    ) {}
}

<?php

namespace App\Repositories\Contracts;

use App\Models\Bit;
use Illuminate\Support\Collection;

interface BitRepositoryInterface
{
    public function all(): Collection;

    public function find(int $id): ?Bit;

    public function findByName(string $name): ?Bit;

    public function create(array $data): Bit;

    public function update(int $id, array $data): Bit;

    public function delete(int $id): bool;

    public function isNameTaken(string $name, ?int $excludeId = null): bool;
}

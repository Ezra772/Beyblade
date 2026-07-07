<?php

namespace App\Repositories\Contracts;

use App\Models\Ratchet;
use Illuminate\Support\Collection;

interface RatchetRepositoryInterface
{
    public function all(): Collection;

    public function find(int $id): ?Ratchet;

    public function findByName(string $name): ?Ratchet;

    public function create(array $data): Ratchet;

    public function update(int $id, array $data): Ratchet;

    public function delete(int $id): bool;

    public function isNameTaken(string $name, ?int $excludeId = null): bool;
}

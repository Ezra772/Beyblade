<?php

namespace App\Repositories\Contracts;

use App\Models\Beyblade;
use Illuminate\Support\Collection;

interface BeybladeRepositoryInterface
{
    public function all(): Collection;

    public function find(int $id): ?Beyblade;

    public function findByName(string $name): ?Beyblade;

    public function create(array $data): Beyblade;

    public function update(int $id, array $data): Beyblade;

    public function delete(int $id): bool;

    public function isNameTaken(string $name, ?int $excludeId = null): bool;

    /**
     * @return Collection<Beyblade> Beyblade yang memakai part tertentu
     */
    public function findUsingPart(string $partType, int $partId): Collection;
}

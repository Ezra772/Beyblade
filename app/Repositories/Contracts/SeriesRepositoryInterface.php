<?php

namespace App\Repositories\Contracts;

use App\Models\Series;
use Illuminate\Support\Collection;

interface SeriesRepositoryInterface
{
    public function all(): Collection;

    public function find(int $id): ?Series;

    public function findByName(string $name): ?Series;

    public function create(array $data): Series;

    public function update(int $id, array $data): Series;

    public function delete(int $id): bool;

    public function isNameTaken(string $name, ?int $excludeId = null): bool;
}

<?php

namespace App\Repositories\Contracts;

use App\Models\Blade;
use Illuminate\Support\Collection;

interface BladeRepositoryInterface
{
    public function all(): Collection;

    public function find(int $id): ?Blade;

    public function findByName(string $name): ?Blade;

    public function create(array $data): Blade;

    public function update(int $id, array $data): Blade;

    public function delete(int $id): bool;

    public function isNameTaken(string $name, ?int $excludeId = null): bool;
}

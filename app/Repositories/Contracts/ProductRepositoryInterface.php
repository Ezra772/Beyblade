<?php

namespace App\Repositories\Contracts;

use App\Models\Product;
use Illuminate\Support\Collection;

interface ProductRepositoryInterface
{
    public function all(): Collection;

    public function find(int $id): ?Product;

    public function findByName(string $name): ?Product;

    public function create(array $data): Product;

    public function update(int $id, array $data): Product;

    public function delete(int $id): bool;

    public function isNameTaken(string $name, ?int $excludeId = null): bool;
}

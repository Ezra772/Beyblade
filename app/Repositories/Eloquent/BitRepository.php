<?php

namespace App\Repositories\Eloquent;

use App\Models\Bit;
use App\Repositories\Contracts\BitRepositoryInterface;
use Illuminate\Support\Collection;

class BitRepository implements BitRepositoryInterface
{
    public function all(): Collection
    {
        return Bit::orderBy('name')->get();
    }

    public function find(int $id): ?Bit
    {
        return Bit::find($id);
    }

    public function findByName(string $name): ?Bit
    {
        return Bit::where('name', $name)->first();
    }

    public function create(array $data): Bit
    {
        return Bit::create($data);
    }

    public function update(int $id, array $data): Bit
    {
        $bit = Bit::findOrFail($id);
        $bit->update($data);

        return $bit->fresh();
    }

    public function delete(int $id): bool
    {
        return (bool) Bit::destroy($id);
    }

    public function isNameTaken(string $name, ?int $excludeId = null): bool
    {
        return Bit::where('name', $name)
            ->when($excludeId, fn ($query) => $query->where('id', '!=', $excludeId))
            ->exists();
    }
}

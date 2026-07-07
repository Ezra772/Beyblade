<?php

namespace App\Repositories\Eloquent;

use App\Models\Ratchet;
use App\Repositories\Contracts\RatchetRepositoryInterface;
use Illuminate\Support\Collection;

class RatchetRepository implements RatchetRepositoryInterface
{
    public function all(): Collection
    {
        return Ratchet::orderBy('name')->get();
    }

    public function find(int $id): ?Ratchet
    {
        return Ratchet::find($id);
    }

    public function findByName(string $name): ?Ratchet
    {
        return Ratchet::where('name', $name)->first();
    }

    public function create(array $data): Ratchet
    {
        return Ratchet::create($data);
    }

    public function update(int $id, array $data): Ratchet
    {
        $ratchet = Ratchet::findOrFail($id);
        $ratchet->update($data);

        return $ratchet->fresh();
    }

    public function delete(int $id): bool
    {
        return (bool) Ratchet::destroy($id);
    }

    public function isNameTaken(string $name, ?int $excludeId = null): bool
    {
        return Ratchet::where('name', $name)
            ->when($excludeId, fn ($query) => $query->where('id', '!=', $excludeId))
            ->exists();
    }
}

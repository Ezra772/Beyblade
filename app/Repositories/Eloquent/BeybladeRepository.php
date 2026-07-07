<?php

namespace App\Repositories\Eloquent;

use App\Models\Beyblade;
use App\Repositories\Contracts\BeybladeRepositoryInterface;
use Illuminate\Support\Collection;

class BeybladeRepository implements BeybladeRepositoryInterface
{
    public function all(): Collection
    {
        return Beyblade::with(['blade', 'ratchet', 'bit'])->orderBy('name')->get();
    }

    public function find(int $id): ?Beyblade
    {
        return Beyblade::find($id);
    }

    public function findByName(string $name): ?Beyblade
    {
        return Beyblade::where('name', $name)->first();
    }

    public function create(array $data): Beyblade
    {
        return Beyblade::create($data);
    }

    public function update(int $id, array $data): Beyblade
    {
        $beyblade = Beyblade::findOrFail($id);
        $beyblade->update($data);

        return $beyblade;
    }

    public function delete(int $id): bool
    {
        return Beyblade::destroy($id) > 0;
    }

    public function isNameTaken(string $name, ?int $excludeId = null): bool
    {
        $query = Beyblade::where('name', $name);

        if ($excludeId) {
            $query->where('id', '!=', $excludeId);
        }

        return $query->exists();
    }

    /**
     * @return Collection<Beyblade> Beyblade yang memakai part tertentu
     */
    public function findUsingPart(string $partType, int $partId): Collection
    {
        return Beyblade::where("{$partType}_id", $partId)->get();
    }
}

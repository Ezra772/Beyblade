<?php

namespace App\Repositories\Eloquent;

use App\Models\Blade;
use App\Repositories\Contracts\BladeRepositoryInterface;
use Illuminate\Support\Collection;

class BladeRepository implements BladeRepositoryInterface
{
    public function all(): Collection
    {
        return Blade::orderBy('name')->get();
    }

    public function find(int $id): ?Blade
    {
        return Blade::find($id);
    }

    public function findByName(string $name): ?Blade
    {
        return Blade::where('name', $name)->first();
    }

    public function create(array $data): Blade
    {
        return Blade::create($data);
    }

    public function update(int $id, array $data): Blade
    {
        $blade = Blade::findOrFail($id);
        $blade->update($data);

        return $blade->fresh();
    }

    public function delete(int $id): bool
    {
        return (bool) Blade::destroy($id);
    }

    public function isNameTaken(string $name, ?int $excludeId = null): bool
    {
        return Blade::where('name', $name)
            ->when($excludeId, fn ($query) => $query->where('id', '!=', $excludeId))
            ->exists();
    }
}

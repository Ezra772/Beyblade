<?php

namespace App\Repositories\Eloquent;

use App\Models\Series;
use App\Repositories\Contracts\SeriesRepositoryInterface;
use Illuminate\Support\Collection;

class SeriesRepository implements SeriesRepositoryInterface
{
    public function all(): Collection
    {
        return Series::orderBy('release_year', 'desc')->orderBy('name')->get();
    }

    public function find(int $id): ?Series
    {
        return Series::find($id);
    }

    public function findByName(string $name): ?Series
    {
        return Series::where('name', $name)->first();
    }

    public function create(array $data): Series
    {
        return Series::create($data);
    }

    public function update(int $id, array $data): Series
    {
        $series = Series::findOrFail($id);
        $series->update($data);

        return $series;
    }

    public function delete(int $id): bool
    {
        return Series::destroy($id) > 0;
    }

    public function isNameTaken(string $name, ?int $excludeId = null): bool
    {
        $query = Series::where('name', $name);

        if ($excludeId) {
            $query->where('id', '!=', $excludeId);
        }

        return $query->exists();
    }
}

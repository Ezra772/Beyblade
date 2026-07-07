<?php

namespace App\Repositories\Eloquent;

use App\Models\Product;
use App\Repositories\Contracts\ProductRepositoryInterface;
use Illuminate\Support\Collection;

class ProductRepository implements ProductRepositoryInterface
{
    public function all(): Collection
    {
        return Product::with('series')->orderBy('release_date', 'desc')->get();
    }

    public function find(int $id): ?Product
    {
        return Product::find($id);
    }

    public function findByName(string $name): ?Product
    {
        return Product::where('name', $name)->first();
    }

    public function create(array $data): Product
    {
        return Product::create($data);
    }

    public function update(int $id, array $data): Product
    {
        $product = Product::findOrFail($id);
        $product->update($data);

        return $product;
    }

    public function delete(int $id): bool
    {
        return Product::destroy($id) > 0;
    }

    public function isNameTaken(string $name, ?int $excludeId = null): bool
    {
        // For product, maybe product_code is what we want to check, but interface uses name.
        // We'll check product_code for now, or just name. The requirement says:
        // product_code is unique. Let's check product_code in isNameTaken.
        // Wait, the prompt says isNameTaken. Let's stick to name or product_code.
        // The prompt says for ProductManager, fields are productCode, seriesId, name, releaseDate.
        // So name is there. We'll just query by name.
        
        $query = Product::where('name', $name);

        if ($excludeId) {
            $query->where('id', '!=', $excludeId);
        }

        return $query->exists();
    }
}

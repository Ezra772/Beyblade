<?php

namespace App\Livewire\Management;

use App\Repositories\Contracts\ProductRepositoryInterface;
use App\Repositories\Contracts\SeriesRepositoryInterface;
use Livewire\Component;

class ProductManager extends Component
{
    public string $search = '';

    // Form state
    public bool $showForm = false;
    public ?int $editingId = null;
    public string $productCode = '';
    public ?int $seriesId = null;
    public string $name = '';
    public ?string $releaseDate = null;

    // Delete confirmation state
    public ?int $confirmingDeleteId = null;

    protected function rules(): array
    {
        return [
            'productCode' => ['required', 'string', 'max:255'],
            'seriesId' => ['required', 'integer', 'exists:series,id'],
            'name' => ['required', 'string', 'max:255'],
            'releaseDate' => ['nullable', 'date'],
        ];
    }

    public function updated($propertyName): void
    {
        $this->validateOnly($propertyName, $this->rules());
    }

    public function openCreateForm(): void
    {
        $this->resetForm();
        $this->showForm = true;
    }

    public function openEditForm(int $id, ProductRepositoryInterface $productRepository): void
    {
        $product = $productRepository->find($id);

        if (! $product) {
            return;
        }

        $this->editingId = $product->id;
        $this->productCode = $product->product_code;
        $this->seriesId = $product->series_id;
        $this->name = $product->name;
        $this->releaseDate = $product->release_date ? $product->release_date->format('Y-m-d') : null;
        $this->showForm = true;
    }

    public function save(ProductRepositoryInterface $productRepository): void
    {
        $this->validate();

        if ($productRepository->isNameTaken($this->productCode, $this->editingId)) {
            $this->addError('productCode', 'Kode Produk sudah dipakai, gunakan kode lain.');

            return;
        }

        $data = [
            'product_code' => $this->productCode,
            'series_id' => $this->seriesId,
            'name' => $this->name,
            'release_date' => $this->releaseDate ?: null,
        ];

        if ($this->editingId) {
            $productRepository->update($this->editingId, $data);
        } else {
            $productRepository->create($data);
        }

        $this->resetForm();
    }

    public function confirmDelete(int $id): void
    {
        $this->confirmingDeleteId = $id;
    }

    public function cancelDelete(): void
    {
        $this->confirmingDeleteId = null;
    }

    public function delete(ProductRepositoryInterface $productRepository): void
    {
        if ($this->confirmingDeleteId) {
            $productRepository->delete($this->confirmingDeleteId);
            $this->confirmingDeleteId = null;
        }
    }

    public function resetForm(): void
    {
        $this->reset(['editingId', 'productCode', 'seriesId', 'name', 'releaseDate']);
        $this->resetErrorBag();
        $this->showForm = false;
    }

    public function cancelForm(): void
    {
        $this->resetForm();
    }

    public function render(ProductRepositoryInterface $productRepository, SeriesRepositoryInterface $seriesRepository)
    {
        $products = $productRepository->all()
            ->when($this->search !== '', fn ($collection) => $collection->filter(
                fn ($product) => str_contains(strtolower($product->name), strtolower($this->search))
                    || str_contains(strtolower($product->product_code), strtolower($this->search))
            ))
            ->values();

        $seriesList = $seriesRepository->all();

        return view('livewire.management.product-manager', [
            'products' => $products,
            'seriesList' => $seriesList,
        ])->layout('layouts.app', ['title' => 'Kelola Product']);
    }
}

<?php

namespace App\Livewire\Management;

use App\Repositories\Contracts\SeriesRepositoryInterface;
use Livewire\Component;

class SeriesManager extends Component
{
    public string $search = '';

    // Form state
    public bool $showForm = false;
    public ?int $editingId = null;
    public string $name = '';
    public ?int $releaseYear = null;
    public ?string $description = null;

    // Delete confirmation state
    public ?int $confirmingDeleteId = null;

    protected function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'releaseYear' => ['nullable', 'integer', 'min:1999', 'max:2100'],
            'description' => ['nullable', 'string'],
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

    public function openEditForm(int $id, SeriesRepositoryInterface $seriesRepository): void
    {
        $series = $seriesRepository->find($id);

        if (! $series) {
            return;
        }

        $this->editingId = $series->id;
        $this->name = $series->name;
        $this->releaseYear = $series->release_year;
        $this->description = $series->description;
        $this->showForm = true;
    }

    public function save(SeriesRepositoryInterface $seriesRepository): void
    {
        $this->validate();

        if ($seriesRepository->isNameTaken($this->name, $this->editingId)) {
            $this->addError('name', 'Nama Series sudah dipakai, gunakan nama lain.');

            return;
        }

        $data = [
            'name' => $this->name,
            'release_year' => $this->releaseYear ?: null,
            'description' => $this->description ?: null,
        ];

        if ($this->editingId) {
            $seriesRepository->update($this->editingId, $data);
        } else {
            $seriesRepository->create($data);
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

    public function delete(SeriesRepositoryInterface $seriesRepository): void
    {
        if ($this->confirmingDeleteId) {
            $seriesRepository->delete($this->confirmingDeleteId);
            $this->confirmingDeleteId = null;
        }
    }

    public function resetForm(): void
    {
        $this->reset(['editingId', 'name', 'releaseYear', 'description']);
        $this->resetErrorBag();
        $this->showForm = false;
    }

    public function cancelForm(): void
    {
        $this->resetForm();
    }

    public function render(SeriesRepositoryInterface $seriesRepository)
    {
        $seriesList = $seriesRepository->all()
            ->when($this->search !== '', fn ($collection) => $collection->filter(
                fn ($series) => str_contains(strtolower($series->name), strtolower($this->search))
            ))
            ->values();

        return view('livewire.management.series-manager', [
            'seriesList' => $seriesList,
        ])->layout('layouts.app', ['title' => 'Kelola Series']);
    }
}

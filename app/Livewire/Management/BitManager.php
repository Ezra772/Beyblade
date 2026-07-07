<?php

namespace App\Livewire\Management;

use App\Repositories\Contracts\BeybladeRepositoryInterface;
use App\Repositories\Contracts\BitRepositoryInterface;
use App\Services\ImageStorageService;
use Illuminate\Support\Facades\Storage;
use Livewire\Component;
use Livewire\WithFileUploads;

class BitManager extends Component
{
    use WithFileUploads;

    public string $search = '';

    // Form state
    public bool $showForm = false;

    public ?int $editingId = null;

    public string $name = '';

    public ?float $speed = null;

    public ?float $stamina = null;

    public ?float $grip = null;

    public ?float $control = null;

    public ?float $movement = null;

    public ?float $dash = null;

    // Image upload state
    public $photo = null;

    public ?string $existingImagePath = null;

    // Delete confirmation state
    public ?int $confirmingDeleteId = null;

    public array $affectedBeyblades = [];

    protected function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'speed' => ['required', 'numeric', 'min:0', 'max:100'],
            'stamina' => ['required', 'numeric', 'min:0', 'max:100'],
            'grip' => ['required', 'numeric', 'min:0', 'max:100'],
            'control' => ['required', 'numeric', 'min:0', 'max:100'],
            'movement' => ['required', 'numeric', 'min:0', 'max:100'],
            'dash' => ['required', 'numeric', 'min:0', 'max:100'],
            'photo' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
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

    public function openEditForm(int $id, BitRepositoryInterface $bitRepository): void
    {
        $bit = $bitRepository->find($id);

        if (! $bit) {
            return;
        }

        $this->editingId = $bit->id;
        $this->name = $bit->name;
        $this->speed = (float) $bit->speed;
        $this->stamina = (float) $bit->stamina;
        $this->grip = (float) $bit->grip;
        $this->control = (float) $bit->control;
        $this->movement = (float) $bit->movement;
        $this->dash = (float) $bit->dash;
        $this->existingImagePath = $bit->image?->path;
        $this->showForm = true;
    }

    public function save(
        BitRepositoryInterface $bitRepository,
        ImageStorageService $imageStorageService,
    ): void {
        $this->validate();

        if ($bitRepository->isNameTaken($this->name, $this->editingId)) {
            $this->addError('name', 'Nama Bit sudah dipakai, gunakan nama lain.');

            return;
        }

        $data = [
            'name' => $this->name,
            'speed' => $this->speed,
            'stamina' => $this->stamina,
            'grip' => $this->grip,
            'control' => $this->control,
            'movement' => $this->movement,
            'dash' => $this->dash,
        ];

        $bit = $this->editingId
            ? $bitRepository->update($this->editingId, $data)
            : $bitRepository->create($data);

        if ($this->photo) {
            $imageStorageService->store($this->photo, 'bit', $bit->id);
        }

        $this->resetForm();
    }

    public function removePhoto(int $bitId, ImageStorageService $imageStorageService): void
    {
        $imageStorageService->deleteByOwner('bit', $bitId);
        $this->existingImagePath = null;
    }

    public function confirmDelete(int $id, BeybladeRepositoryInterface $beybladeRepository): void
    {
        $this->confirmingDeleteId = $id;
        $this->affectedBeyblades = $beybladeRepository->findUsingPart('bit', $id)
            ->pluck('name')
            ->toArray();
    }

    public function cancelDelete(): void
    {
        $this->confirmingDeleteId = null;
        $this->affectedBeyblades = [];
    }

    public function delete(BitRepositoryInterface $bitRepository, ImageStorageService $imageStorageService): void
    {
        if ($this->confirmingDeleteId) {
            $imageStorageService->deleteByOwner('bit', $this->confirmingDeleteId);
            $bitRepository->delete($this->confirmingDeleteId);
            $this->confirmingDeleteId = null;
            $this->affectedBeyblades = [];
        }
    }

    public function deleteWithReferences(
        BitRepositoryInterface $bitRepository,
        BeybladeRepositoryInterface $beybladeRepository,
        ImageStorageService $imageStorageService
    ): void {
        if ($this->confirmingDeleteId) {
            $beyblades = $beybladeRepository->findUsingPart('bit', $this->confirmingDeleteId);
            foreach ($beyblades as $beyblade) {
                $beybladeRepository->delete($beyblade->id);
            }

            $imageStorageService->deleteByOwner('bit', $this->confirmingDeleteId);
            $bitRepository->delete($this->confirmingDeleteId);

            $this->confirmingDeleteId = null;
            $this->affectedBeyblades = [];
        }
    }

    public function resetForm(): void
    {
        $this->reset([
            'editingId', 'name', 'speed', 'stamina', 'grip', 'control', 'movement', 'dash',
            'photo', 'existingImagePath',
        ]);
        $this->resetErrorBag();
        $this->showForm = false;
    }

    public function cancelForm(): void
    {
        $this->resetForm();
    }

    public function render(BitRepositoryInterface $bitRepository)
    {
        $bits = $bitRepository->all()
            ->when($this->search !== '', fn ($collection) => $collection->filter(
                fn ($bit) => str_contains(strtolower($bit->name), strtolower($this->search))
            ))
            ->values();

        return view('livewire.management.bit-manager', [
            'bits' => $bits,
            'storageUrl' => fn (string $path) => Storage::disk('public')->url($path),
        ])->layout('layouts.app', ['title' => 'Kelola Bit']);
    }
}

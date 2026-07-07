<?php

namespace App\Livewire\Management;

use App\Repositories\Contracts\BeybladeRepositoryInterface;
use App\Repositories\Contracts\RatchetRepositoryInterface;
use App\Services\ImageStorageService;
use Illuminate\Support\Facades\Storage;
use Livewire\Component;
use Livewire\WithFileUploads;

class RatchetManager extends Component
{
    use WithFileUploads;

    public string $search = '';

    // Form state
    public bool $showForm = false;

    public ?int $editingId = null;

    public string $name = '';

    public ?float $height = null;

    public ?float $weight = null;

    public ?float $stability = null;

    public ?float $burstResistance = null;

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
            'height' => ['required', 'numeric', 'min:0'],
            'weight' => ['required', 'numeric', 'min:0'],
            'stability' => ['required', 'numeric', 'min:0', 'max:100'],
            'burstResistance' => ['required', 'numeric', 'min:0', 'max:100'],
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

    public function openEditForm(int $id, RatchetRepositoryInterface $ratchetRepository): void
    {
        $ratchet = $ratchetRepository->find($id);

        if (! $ratchet) {
            return;
        }

        $this->editingId = $ratchet->id;
        $this->name = $ratchet->name;
        $this->height = (float) $ratchet->height;
        $this->weight = (float) $ratchet->weight;
        $this->stability = (float) $ratchet->stability;
        $this->burstResistance = (float) $ratchet->burst_resistance;
        $this->existingImagePath = $ratchet->image?->path;
        $this->showForm = true;
    }

    public function save(
        RatchetRepositoryInterface $ratchetRepository,
        ImageStorageService $imageStorageService,
    ): void {
        $this->validate();

        if ($ratchetRepository->isNameTaken($this->name, $this->editingId)) {
            $this->addError('name', 'Nama Ratchet sudah dipakai, gunakan nama lain.');

            return;
        }

        $data = [
            'name' => $this->name,
            'height' => $this->height,
            'weight' => $this->weight,
            'stability' => $this->stability,
            'burst_resistance' => $this->burstResistance,
        ];

        $ratchet = $this->editingId
            ? $ratchetRepository->update($this->editingId, $data)
            : $ratchetRepository->create($data);

        if ($this->photo) {
            $imageStorageService->store($this->photo, 'ratchet', $ratchet->id);
        }

        $this->resetForm();
    }

    public function removePhoto(int $ratchetId, ImageStorageService $imageStorageService): void
    {
        $imageStorageService->deleteByOwner('ratchet', $ratchetId);
        $this->existingImagePath = null;
    }

    public function confirmDelete(int $id, BeybladeRepositoryInterface $beybladeRepository): void
    {
        $this->confirmingDeleteId = $id;
        $this->affectedBeyblades = $beybladeRepository->findUsingPart('ratchet', $id)
            ->pluck('name')
            ->toArray();
    }

    public function cancelDelete(): void
    {
        $this->confirmingDeleteId = null;
        $this->affectedBeyblades = [];
    }

    public function delete(RatchetRepositoryInterface $ratchetRepository, ImageStorageService $imageStorageService): void
    {
        if ($this->confirmingDeleteId) {
            $imageStorageService->deleteByOwner('ratchet', $this->confirmingDeleteId);
            $ratchetRepository->delete($this->confirmingDeleteId);
            $this->confirmingDeleteId = null;
            $this->affectedBeyblades = [];
        }
    }

    public function deleteWithReferences(
        RatchetRepositoryInterface $ratchetRepository,
        BeybladeRepositoryInterface $beybladeRepository,
        ImageStorageService $imageStorageService
    ): void {
        if ($this->confirmingDeleteId) {
            $beyblades = $beybladeRepository->findUsingPart('ratchet', $this->confirmingDeleteId);
            foreach ($beyblades as $beyblade) {
                $beybladeRepository->delete($beyblade->id);
            }

            $imageStorageService->deleteByOwner('ratchet', $this->confirmingDeleteId);
            $ratchetRepository->delete($this->confirmingDeleteId);

            $this->confirmingDeleteId = null;
            $this->affectedBeyblades = [];
        }
    }

    public function resetForm(): void
    {
        $this->reset([
            'editingId', 'name', 'height', 'weight', 'stability', 'burstResistance',
            'photo', 'existingImagePath',
        ]);
        $this->resetErrorBag();
        $this->showForm = false;
    }

    public function cancelForm(): void
    {
        $this->resetForm();
    }

    public function render(RatchetRepositoryInterface $ratchetRepository)
    {
        $ratchets = $ratchetRepository->all()
            ->when($this->search !== '', fn ($collection) => $collection->filter(
                fn ($ratchet) => str_contains(strtolower($ratchet->name), strtolower($this->search))
            ))
            ->values();

        return view('livewire.management.ratchet-manager', [
            'ratchets' => $ratchets,
            'storageUrl' => fn (string $path) => Storage::disk('public')->url($path),
        ])->layout('layouts.app', ['title' => 'Kelola Ratchet']);
    }
}

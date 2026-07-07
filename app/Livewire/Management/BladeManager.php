<?php

namespace App\Livewire\Management;

use App\Repositories\Contracts\BeybladeRepositoryInterface;
use App\Repositories\Contracts\BladeRepositoryInterface;
use App\Repositories\Contracts\SeriesRepositoryInterface;
use App\Services\ImageStorageService;
use Illuminate\Support\Facades\Storage;
use Livewire\Component;
use Livewire\WithFileUploads;

class BladeManager extends Component
{
    use WithFileUploads;

    public string $search = '';

    // Form state
    public bool $showForm = false;

    public ?int $editingId = null;

    public string $name = '';

    public ?int $seriesId = null;

    public string $productCode = '';

    public ?float $weight = null;

    public ?float $attack = null;

    public ?float $defense = null;

    public ?float $stamina = null;

    public ?float $smash = null;

    public ?float $upper = null;

    public ?float $recoil = null;

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
            'seriesId' => ['required', 'integer', 'exists:series,id'],
            'productCode' => ['nullable', 'string', 'max:255'],
            'weight' => ['required', 'numeric', 'min:0'],
            'attack' => ['required', 'numeric', 'min:0', 'max:100'],
            'defense' => ['required', 'numeric', 'min:0', 'max:100'],
            'stamina' => ['required', 'numeric', 'min:0', 'max:100'],
            'smash' => ['required', 'numeric', 'min:0', 'max:100'],
            'upper' => ['required', 'numeric', 'min:0', 'max:100'],
            'recoil' => ['required', 'numeric', 'min:0', 'max:100'],
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

    public function openEditForm(int $id, BladeRepositoryInterface $bladeRepository): void
    {
        $blade = $bladeRepository->find($id);

        if (! $blade) {
            return;
        }

        $this->editingId = $blade->id;
        $this->name = $blade->name;
        $this->seriesId = $blade->series_id;
        $this->productCode = $blade->product_code ?? '';
        $this->weight = (float) $blade->weight;
        $this->attack = (float) $blade->attack;
        $this->defense = (float) $blade->defense;
        $this->stamina = (float) $blade->stamina;
        $this->smash = (float) $blade->smash;
        $this->upper = (float) $blade->upper;
        $this->recoil = (float) $blade->recoil;
        $this->burstResistance = (float) $blade->burst_resistance;
        $this->existingImagePath = $blade->image?->path;
        $this->showForm = true;
    }

    public function save(
        BladeRepositoryInterface $bladeRepository,
        ImageStorageService $imageStorageService,
    ): void {
        $this->validate();

        if ($bladeRepository->isNameTaken($this->name, $this->editingId)) {
            $this->addError('name', 'Nama Blade sudah dipakai, gunakan nama lain.');

            return;
        }

        $data = [
            'name' => $this->name,
            'series_id' => $this->seriesId,
            'product_code' => $this->productCode ?: null,
            'weight' => $this->weight,
            'attack' => $this->attack,
            'defense' => $this->defense,
            'stamina' => $this->stamina,
            'smash' => $this->smash,
            'upper' => $this->upper,
            'recoil' => $this->recoil,
            'burst_resistance' => $this->burstResistance,
        ];

        $blade = $this->editingId
            ? $bladeRepository->update($this->editingId, $data)
            : $bladeRepository->create($data);

        if ($this->photo) {
            $imageStorageService->store($this->photo, 'blade', $blade->id);
        }

        $this->resetForm();
    }

    public function removePhoto(int $bladeId, ImageStorageService $imageStorageService): void
    {
        $imageStorageService->deleteByOwner('blade', $bladeId);
        $this->existingImagePath = null;
    }

    public function confirmDelete(int $id, BeybladeRepositoryInterface $beybladeRepository): void
    {
        $this->confirmingDeleteId = $id;
        $this->affectedBeyblades = $beybladeRepository->findUsingPart('blade', $id)
            ->pluck('name')
            ->toArray();
    }

    public function cancelDelete(): void
    {
        $this->confirmingDeleteId = null;
        $this->affectedBeyblades = [];
    }

    public function delete(BladeRepositoryInterface $bladeRepository, ImageStorageService $imageStorageService): void
    {
        if ($this->confirmingDeleteId) {
            $imageStorageService->deleteByOwner('blade', $this->confirmingDeleteId);
            $bladeRepository->delete($this->confirmingDeleteId);
            $this->confirmingDeleteId = null;
            $this->affectedBeyblades = [];
        }
    }

    public function deleteWithReferences(
        BladeRepositoryInterface $bladeRepository,
        BeybladeRepositoryInterface $beybladeRepository,
        ImageStorageService $imageStorageService
    ): void {
        if ($this->confirmingDeleteId) {
            // Hapus dulu semua Beyblade terkait
            $beyblades = $beybladeRepository->findUsingPart('blade', $this->confirmingDeleteId);
            foreach ($beyblades as $beyblade) {
                $beybladeRepository->delete($beyblade->id);
            }

            // Baru hapus Blade-nya
            $imageStorageService->deleteByOwner('blade', $this->confirmingDeleteId);
            $bladeRepository->delete($this->confirmingDeleteId);

            $this->confirmingDeleteId = null;
            $this->affectedBeyblades = [];
        }
    }

    public function resetForm(): void
    {
        $this->reset([
            'editingId', 'name', 'seriesId', 'productCode', 'weight',
            'attack', 'defense', 'stamina', 'smash', 'upper', 'recoil', 'burstResistance',
            'photo', 'existingImagePath',
        ]);
        $this->resetErrorBag();
        $this->showForm = false;
    }

    public function cancelForm(): void
    {
        $this->resetForm();
    }

    public function render(BladeRepositoryInterface $bladeRepository, SeriesRepositoryInterface $seriesRepository)
    {
        $blades = $bladeRepository->all()
            ->when($this->search !== '', fn ($collection) => $collection->filter(
                fn ($blade) => str_contains(strtolower($blade->name), strtolower($this->search))
                    || str_contains(strtolower($blade->series->name ?? ''), strtolower($this->search))
            ))
            ->values();

        $seriesList = $seriesRepository->all();

        return view('livewire.management.blade-manager', [
            'blades' => $blades,
            'seriesList' => $seriesList,
            'storageUrl' => fn (string $path) => Storage::disk('public')->url($path),
        ])->layout('layouts.app', ['title' => 'Kelola Blade']);
    }
}

<?php

namespace App\Livewire\Management;

use App\Repositories\Contracts\BeybladeRepositoryInterface;
use App\Repositories\Contracts\BitRepositoryInterface;
use App\Repositories\Contracts\BladeRepositoryInterface;
use App\Repositories\Contracts\RatchetRepositoryInterface;
use Livewire\Component;

class BeybladeManager extends Component
{
    public string $search = '';

    // Form state
    public bool $showForm = false;
    public ?int $editingId = null;
    public string $name = '';
    public ?int $bladeId = null;
    public ?int $ratchetId = null;
    public ?int $bitId = null;

    // Delete confirmation state
    public ?int $confirmingDeleteId = null;

    protected function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'bladeId' => ['required', 'integer', 'exists:blades,id'],
            'ratchetId' => ['required', 'integer', 'exists:ratchets,id'],
            'bitId' => ['required', 'integer', 'exists:bits,id'],
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

    public function openEditForm(int $id, BeybladeRepositoryInterface $beybladeRepository): void
    {
        $beyblade = $beybladeRepository->find($id);

        if (! $beyblade) {
            return;
        }

        $this->editingId = $beyblade->id;
        $this->name = $beyblade->name;
        $this->bladeId = $beyblade->blade_id;
        $this->ratchetId = $beyblade->ratchet_id;
        $this->bitId = $beyblade->bit_id;
        $this->showForm = true;
    }

    public function save(BeybladeRepositoryInterface $beybladeRepository): void
    {
        $this->validate();

        if ($beybladeRepository->isNameTaken($this->name, $this->editingId)) {
            $this->addError('name', 'Nama Beyblade sudah dipakai, gunakan nama lain.');

            return;
        }

        $data = [
            'name' => $this->name,
            'blade_id' => $this->bladeId,
            'ratchet_id' => $this->ratchetId,
            'bit_id' => $this->bitId,
        ];

        if ($this->editingId) {
            $beybladeRepository->update($this->editingId, $data);
        } else {
            $beybladeRepository->create($data);
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

    public function delete(BeybladeRepositoryInterface $beybladeRepository): void
    {
        if ($this->confirmingDeleteId) {
            $beybladeRepository->delete($this->confirmingDeleteId);
            $this->confirmingDeleteId = null;
        }
    }

    public function resetForm(): void
    {
        $this->reset(['editingId', 'name', 'bladeId', 'ratchetId', 'bitId']);
        $this->resetErrorBag();
        $this->showForm = false;
    }

    public function cancelForm(): void
    {
        $this->resetForm();
    }

    public function render(
        BeybladeRepositoryInterface $beybladeRepository,
        BladeRepositoryInterface $bladeRepository,
        RatchetRepositoryInterface $ratchetRepository,
        BitRepositoryInterface $bitRepository
    ) {
        $beyblades = $beybladeRepository->all()
            ->when($this->search !== '', fn ($collection) => $collection->filter(
                fn ($beyblade) => str_contains(strtolower($beyblade->name), strtolower($this->search))
            ))
            ->values();

        $blades = $bladeRepository->all();
        $ratchets = $ratchetRepository->all();
        $bits = $bitRepository->all();

        return view('livewire.management.beyblade-manager', [
            'beyblades' => $beyblades,
            'blades' => $blades,
            'ratchets' => $ratchets,
            'bits' => $bits,
        ])->layout('layouts.app', ['title' => 'Kelola Beyblade']);
    }
}

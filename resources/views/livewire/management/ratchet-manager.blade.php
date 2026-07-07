<div class="space-y-6">
    {{-- Header --}}
    <div class="flex items-center justify-between">
        <flux:heading size="xl">Kelola Ratchet</flux:heading>
        <flux:button wire:click="openCreateForm" variant="primary" icon="plus">
            Tambah Ratchet Baru
        </flux:button>
    </div>

    {{-- Search --}}
    <flux:field>
        <flux:input
            wire:model.live="search"
            placeholder="Cari berdasarkan nama..."
            icon="magnifying-glass"
        />
    </flux:field>

    {{-- Delete Confirmation --}}
    @if ($confirmingDeleteId)
        <flux:callout variant="danger" icon="exclamation-triangle">
            <flux:callout.heading>Konfirmasi Hapus</flux:callout.heading>
            @if(count($affectedBeyblades) > 0)
                <flux:callout.text>
                    Ratchet ini masih dipakai oleh {{ count($affectedBeyblades) }} Official Beyblade: 
                    <strong>{{ implode(', ', $affectedBeyblades) }}</strong>. 
                    Menghapus Ratchet ini juga akan menghapus Beyblade tersebut.
                </flux:callout.text>
                <div class="mt-3 flex gap-2">
                    <flux:button wire:click="cancelDelete" variant="ghost" size="sm">Batalkan</flux:button>
                    <flux:button wire:click="deleteWithReferences" variant="danger" size="sm">Hapus Beserta Referensinya</flux:button>
                </div>
            @else
                <flux:callout.text>Apakah kamu yakin ingin menghapus Ratchet ini? Tindakan ini tidak dapat dibatalkan.</flux:callout.text>
                <div class="mt-3 flex gap-2">
                    <flux:button wire:click="delete" variant="danger" size="sm">Ya, Hapus</flux:button>
                    <flux:button wire:click="cancelDelete" variant="ghost" size="sm">Batal</flux:button>
                </div>
            @endif
        </flux:callout>
    @endif

    {{-- Form Tambah / Edit --}}
    @if ($showForm)
        <flux:card>
            <flux:heading size="lg" class="mb-4">
                {{ $editingId ? 'Ubah Ratchet' : 'Tambah Ratchet Baru' }}
            </flux:heading>

            <div class="space-y-4">
                {{-- Info Dasar --}}
                <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                    <flux:field>
                        <flux:label>Nama</flux:label>
                        <flux:input wire:model.blur="name" placeholder="Nama Ratchet" />
                        <flux:error name="name" />
                    </flux:field>
                </div>

                <flux:separator />

                {{-- Atribut Fisik --}}
                <flux:heading size="base">Atribut Fisik</flux:heading>
                <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                    <flux:field>
                        <flux:label>Tinggi (mm)</flux:label>
                        <flux:input wire:model.blur="height" type="number" step="0.01" min="0" placeholder="0.00" />
                        <flux:error name="height" />
                    </flux:field>

                    <flux:field>
                        <flux:label>Berat (gram)</flux:label>
                        <flux:input wire:model.blur="weight" type="number" step="0.01" min="0" placeholder="0.00" />
                        <flux:error name="weight" />
                    </flux:field>
                </div>

                <flux:separator />

                {{-- Atribut Performa --}}
                <flux:heading size="base">Atribut Performa</flux:heading>
                <div class="grid grid-cols-2 gap-4 sm:grid-cols-4">
                    <flux:field>
                        <flux:label>Stability</flux:label>
                        <flux:input wire:model.blur="stability" type="number" step="0.01" min="0" max="100" placeholder="0–100" />
                        <flux:error name="stability" />
                    </flux:field>

                    <flux:field>
                        <flux:label>Burst Resistance</flux:label>
                        <flux:input wire:model.blur="burstResistance" type="number" step="0.01" min="0" max="100" placeholder="0–100" />
                        <flux:error name="burstResistance" />
                    </flux:field>
                </div>

                <flux:separator />

                {{-- Gambar --}}
                <flux:heading size="base">Gambar <flux:badge size="sm" variant="ghost">Opsional</flux:badge></flux:heading>

                @if ($existingImagePath && ! $photo)
                    <div class="flex items-center gap-4">
                        <img src="{{ $storageUrl($existingImagePath) }}" alt="Gambar Ratchet" class="h-24 w-24 rounded-lg object-cover" />
                        @if ($editingId)
                            <flux:button wire:click="removePhoto({{ $editingId }})" variant="danger" size="sm" icon="trash">
                                Hapus Gambar
                            </flux:button>
                        @endif
                    </div>
                @endif

                @if ($photo)
                    <div class="flex items-center gap-4">
                        <img src="{{ $photo->temporaryUrl() }}" alt="Preview" class="h-24 w-24 rounded-lg object-cover" />
                        <flux:text size="sm" class="text-zinc-500">Preview — belum tersimpan</flux:text>
                    </div>
                @endif

                <flux:field>
                    <flux:label>Upload Gambar</flux:label>
                    <input type="file" wire:model="photo" accept="image/jpeg,image/png,image/webp"
                        class="block w-full text-sm text-zinc-700 dark:text-zinc-300 file:mr-4 file:rounded-md file:border-0 file:bg-zinc-100 file:px-4 file:py-2 file:text-sm file:font-medium dark:file:bg-zinc-700" />
                    <flux:error name="photo" />
                </flux:field>

                {{-- Form Actions --}}
                <div class="flex gap-2 pt-2">
                    <flux:button wire:click="save" variant="primary">Simpan</flux:button>
                    <flux:button wire:click="cancelForm" variant="ghost">Batal</flux:button>
                </div>
            </div>
        </flux:card>
    @endif

    {{-- Tabel Ratchet --}}
    <flux:table>
        <flux:table.columns>
            <flux:table.column>Gambar</flux:table.column>
            <flux:table.column>Nama</flux:table.column>
            <flux:table.column>Tinggi</flux:table.column>
            <flux:table.column>Berat</flux:table.column>
            <flux:table.column>Stability</flux:table.column>
            <flux:table.column>Burst Resistance</flux:table.column>
            <flux:table.column>Aksi</flux:table.column>
        </flux:table.columns>

        <flux:table.rows>
            @forelse ($ratchets as $ratchet)
                <flux:table.row wire:key="ratchet-{{ $ratchet->id }}">
                    <flux:table.cell>
                        @if ($ratchet->image)
                            <img src="{{ $storageUrl($ratchet->image->path) }}" alt="{{ $ratchet->name }}" class="h-10 w-10 rounded object-cover" />
                        @else
                            <div class="flex h-10 w-10 items-center justify-center rounded bg-zinc-100 dark:bg-zinc-700">
                                <flux:icon name="photo" class="h-5 w-5 text-zinc-400" />
                            </div>
                        @endif
                    </flux:table.cell>
                    <flux:table.cell class="font-medium">{{ $ratchet->name }}</flux:table.cell>
                    <flux:table.cell>{{ $ratchet->height }}mm</flux:table.cell>
                    <flux:table.cell>{{ $ratchet->weight }}g</flux:table.cell>
                    <flux:table.cell>{{ $ratchet->stability }}</flux:table.cell>
                    <flux:table.cell>{{ $ratchet->burst_resistance }}</flux:table.cell>
                    <flux:table.cell>
                        <div class="flex gap-2">
                            <flux:button
                                wire:click="openEditForm({{ $ratchet->id }})"
                                size="sm"
                                variant="ghost"
                                icon="pencil"
                            >
                                Ubah
                            </flux:button>
                            <flux:button
                                wire:click="confirmDelete({{ $ratchet->id }})"
                                size="sm"
                                variant="danger"
                                icon="trash"
                            >
                                Hapus
                            </flux:button>
                        </div>
                    </flux:table.cell>
                </flux:table.row>
            @empty
                <flux:table.row>
                    <flux:table.cell colspan="7" class="text-center text-zinc-500 dark:text-zinc-400">
                        @if ($search !== '')
                            Tidak ada Ratchet yang cocok dengan pencarian "{{ $search }}".
                        @else
                            Belum ada data Ratchet. Klik "Tambah Ratchet Baru" untuk mulai.
                        @endif
                    </flux:table.cell>
                </flux:table.row>
            @endforelse
        </flux:table.rows>
    </flux:table>
</div>

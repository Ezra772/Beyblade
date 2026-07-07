<div class="space-y-6">
    {{-- Header --}}
    <div class="flex items-center justify-between">
        <flux:heading size="xl">Kelola Bit</flux:heading>
        <flux:button wire:click="openCreateForm" variant="primary" icon="plus">
            Tambah Bit Baru
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
                    Bit ini masih dipakai oleh {{ count($affectedBeyblades) }} Official Beyblade: 
                    <strong>{{ implode(', ', $affectedBeyblades) }}</strong>. 
                    Menghapus Bit ini juga akan menghapus Beyblade tersebut.
                </flux:callout.text>
                <div class="mt-3 flex gap-2">
                    <flux:button wire:click="cancelDelete" variant="ghost" size="sm">Batalkan</flux:button>
                    <flux:button wire:click="deleteWithReferences" variant="danger" size="sm">Hapus Beserta Referensinya</flux:button>
                </div>
            @else
                <flux:callout.text>Apakah kamu yakin ingin menghapus Bit ini? Tindakan ini tidak dapat dibatalkan.</flux:callout.text>
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
                {{ $editingId ? 'Ubah Bit' : 'Tambah Bit Baru' }}
            </flux:heading>

            <div class="space-y-4">
                {{-- Info Dasar --}}
                <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                    <flux:field>
                        <flux:label>Nama</flux:label>
                        <flux:input wire:model.blur="name" placeholder="Nama Bit" />
                        <flux:error name="name" />
                    </flux:field>
                </div>

                <flux:separator />

                {{-- Atribut Performa --}}
                <flux:heading size="base">Atribut Performa</flux:heading>
                <div class="grid grid-cols-2 gap-4 sm:grid-cols-3">
                    <flux:field>
                        <flux:label>Speed</flux:label>
                        <flux:input wire:model.blur="speed" type="number" step="0.01" min="0" max="100" placeholder="0–100" />
                        <flux:error name="speed" />
                    </flux:field>

                    <flux:field>
                        <flux:label>Stamina</flux:label>
                        <flux:input wire:model.blur="stamina" type="number" step="0.01" min="0" max="100" placeholder="0–100" />
                        <flux:error name="stamina" />
                    </flux:field>

                    <flux:field>
                        <flux:label>Grip</flux:label>
                        <flux:input wire:model.blur="grip" type="number" step="0.01" min="0" max="100" placeholder="0–100" />
                        <flux:error name="grip" />
                    </flux:field>

                    <flux:field>
                        <flux:label>Control</flux:label>
                        <flux:input wire:model.blur="control" type="number" step="0.01" min="0" max="100" placeholder="0–100" />
                        <flux:error name="control" />
                    </flux:field>

                    <flux:field>
                        <flux:label>Movement</flux:label>
                        <flux:input wire:model.blur="movement" type="number" step="0.01" min="0" max="100" placeholder="0–100" />
                        <flux:error name="movement" />
                    </flux:field>

                    <flux:field>
                        <flux:label>Dash</flux:label>
                        <flux:input wire:model.blur="dash" type="number" step="0.01" min="0" max="100" placeholder="0–100" />
                        <flux:error name="dash" />
                    </flux:field>
                </div>

                <flux:separator />

                {{-- Gambar --}}
                <flux:heading size="base">Gambar <flux:badge size="sm" variant="ghost">Opsional</flux:badge></flux:heading>

                @if ($existingImagePath && ! $photo)
                    <div class="flex items-center gap-4">
                        <img src="{{ $storageUrl($existingImagePath) }}" alt="Gambar Bit" class="h-24 w-24 rounded-lg object-cover" />
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

    {{-- Tabel Bit --}}
    <flux:table>
        <flux:table.columns>
            <flux:table.column>Gambar</flux:table.column>
            <flux:table.column>Nama</flux:table.column>
            <flux:table.column>Speed</flux:table.column>
            <flux:table.column>Stamina</flux:table.column>
            <flux:table.column>Grip</flux:table.column>
            <flux:table.column>Control</flux:table.column>
            <flux:table.column>Movement</flux:table.column>
            <flux:table.column>Dash</flux:table.column>
            <flux:table.column>Aksi</flux:table.column>
        </flux:table.columns>

        <flux:table.rows>
            @forelse ($bits as $bit)
                <flux:table.row wire:key="bit-{{ $bit->id }}">
                    <flux:table.cell>
                        @if ($bit->image)
                            <img src="{{ $storageUrl($bit->image->path) }}" alt="{{ $bit->name }}" class="h-10 w-10 rounded object-cover" />
                        @else
                            <div class="flex h-10 w-10 items-center justify-center rounded bg-zinc-100 dark:bg-zinc-700">
                                <flux:icon name="photo" class="h-5 w-5 text-zinc-400" />
                            </div>
                        @endif
                    </flux:table.cell>
                    <flux:table.cell class="font-medium">{{ $bit->name }}</flux:table.cell>
                    <flux:table.cell>{{ $bit->speed }}</flux:table.cell>
                    <flux:table.cell>{{ $bit->stamina }}</flux:table.cell>
                    <flux:table.cell>{{ $bit->grip }}</flux:table.cell>
                    <flux:table.cell>{{ $bit->control }}</flux:table.cell>
                    <flux:table.cell>{{ $bit->movement }}</flux:table.cell>
                    <flux:table.cell>{{ $bit->dash }}</flux:table.cell>
                    <flux:table.cell>
                        <div class="flex gap-2">
                            <flux:button
                                wire:click="openEditForm({{ $bit->id }})"
                                size="sm"
                                variant="ghost"
                                icon="pencil"
                            >
                                Ubah
                            </flux:button>
                            <flux:button
                                wire:click="confirmDelete({{ $bit->id }})"
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
                    <flux:table.cell colspan="9" class="text-center text-zinc-500 dark:text-zinc-400">
                        @if ($search !== '')
                            Tidak ada Bit yang cocok dengan pencarian "{{ $search }}".
                        @else
                            Belum ada data Bit. Klik "Tambah Bit Baru" untuk mulai.
                        @endif
                    </flux:table.cell>
                </flux:table.row>
            @endforelse
        </flux:table.rows>
    </flux:table>
</div>

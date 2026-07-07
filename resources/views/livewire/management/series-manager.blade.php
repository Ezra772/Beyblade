<div class="space-y-6">
    {{-- Header --}}
    <div class="flex items-center justify-between">
        <flux:heading size="xl">Kelola Series</flux:heading>
        <flux:button wire:click="openCreateForm" variant="primary" icon="plus">
            Tambah Series Baru
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
            <flux:callout.text>Apakah kamu yakin ingin menghapus Series ini? Jika Series ini masih dipakai oleh Blade atau Product, penghapusan mungkin akan gagal di database.</flux:callout.text>
            <div class="mt-3 flex gap-2">
                <flux:button wire:click="delete" variant="danger" size="sm">Ya, Hapus</flux:button>
                <flux:button wire:click="cancelDelete" variant="ghost" size="sm">Batal</flux:button>
            </div>
        </flux:callout>
    @endif

    {{-- Form Tambah / Edit --}}
    @if ($showForm)
        <flux:card>
            <flux:heading size="lg" class="mb-4">
                {{ $editingId ? 'Ubah Series' : 'Tambah Series Baru' }}
            </flux:heading>

            <div class="space-y-4">
                <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                    <flux:field>
                        <flux:label>Nama</flux:label>
                        <flux:input wire:model.blur="name" placeholder="Nama Series" />
                        <flux:error name="name" />
                    </flux:field>

                    <flux:field>
                        <flux:label>Tahun Rilis <flux:badge size="sm" variant="ghost">Opsional</flux:badge></flux:label>
                        <flux:input wire:model.blur="releaseYear" type="number" placeholder="Contoh: 2023" />
                        <flux:error name="releaseYear" />
                    </flux:field>
                </div>

                <flux:field>
                    <flux:label>Deskripsi <flux:badge size="sm" variant="ghost">Opsional</flux:badge></flux:label>
                    <flux:textarea wire:model.blur="description" placeholder="Deskripsi singkat tentang series ini..." />
                    <flux:error name="description" />
                </flux:field>

                <div class="flex gap-2 pt-2">
                    <flux:button wire:click="save" variant="primary">Simpan</flux:button>
                    <flux:button wire:click="cancelForm" variant="ghost">Batal</flux:button>
                </div>
            </div>
        </flux:card>
    @endif

    {{-- Tabel Series --}}
    <flux:table>
        <flux:table.columns>
            <flux:table.column>Nama</flux:table.column>
            <flux:table.column>Tahun Rilis</flux:table.column>
            <flux:table.column>Deskripsi</flux:table.column>
            <flux:table.column>Aksi</flux:table.column>
        </flux:table.columns>

        <flux:table.rows>
            @forelse ($seriesList as $series)
                <flux:table.row wire:key="series-{{ $series->id }}">
                    <flux:table.cell class="font-medium">{{ $series->name }}</flux:table.cell>
                    <flux:table.cell>{{ $series->release_year ?? '—' }}</flux:table.cell>
                    <flux:table.cell>
                        @if ($series->description)
                            <span class="truncate block max-w-xs">{{ $series->description }}</span>
                        @else
                            —
                        @endif
                    </flux:table.cell>
                    <flux:table.cell>
                        <div class="flex gap-2">
                            <flux:button
                                wire:click="openEditForm({{ $series->id }})"
                                size="sm"
                                variant="ghost"
                                icon="pencil"
                            >
                                Ubah
                            </flux:button>
                            <flux:button
                                wire:click="confirmDelete({{ $series->id }})"
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
                    <flux:table.cell colspan="4" class="text-center text-zinc-500 dark:text-zinc-400">
                        @if ($search !== '')
                            Tidak ada Series yang cocok dengan pencarian "{{ $search }}".
                        @else
                            Belum ada data Series. Klik "Tambah Series Baru" untuk mulai.
                        @endif
                    </flux:table.cell>
                </flux:table.row>
            @endforelse
        </flux:table.rows>
    </flux:table>
</div>

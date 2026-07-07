<div class="space-y-6">
    {{-- Header --}}
    <div class="flex items-center justify-between">
        <flux:heading size="xl">Kelola Product</flux:heading>
        <flux:button wire:click="openCreateForm" variant="primary" icon="plus">
            Tambah Product Baru
        </flux:button>
    </div>

    {{-- Search --}}
    <flux:field>
        <flux:input
            wire:model.live="search"
            placeholder="Cari berdasarkan nama atau kode..."
            icon="magnifying-glass"
        />
    </flux:field>

    {{-- Delete Confirmation --}}
    @if ($confirmingDeleteId)
        <flux:callout variant="danger" icon="exclamation-triangle">
            <flux:callout.heading>Konfirmasi Hapus</flux:callout.heading>
            <flux:callout.text>Apakah kamu yakin ingin menghapus Product ini? Tindakan ini tidak dapat dibatalkan.</flux:callout.text>
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
                {{ $editingId ? 'Ubah Product' : 'Tambah Product Baru' }}
            </flux:heading>

            <div class="space-y-4">
                <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                    <flux:field>
                        <flux:label>Kode Produk</flux:label>
                        <flux:input wire:model.blur="productCode" placeholder="Contoh: BX-01" />
                        <flux:error name="productCode" />
                    </flux:field>

                    <flux:field>
                        <flux:label>Series</flux:label>
                        @if ($seriesList->isEmpty())
                            <flux:text size="sm" class="text-zinc-500">Belum ada Series, tambahkan dulu di halaman Kelola Series.</flux:text>
                        @else
                            <flux:select wire:model="seriesId" placeholder="Pilih Series...">
                                @foreach($seriesList as $s)
                                    <flux:select.option value="{{ $s->id }}">{{ $s->name }}</flux:select.option>
                                @endforeach
                            </flux:select>
                        @endif
                        <flux:error name="seriesId" />
                    </flux:field>
                </div>

                <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                    <flux:field>
                        <flux:label>Nama</flux:label>
                        <flux:input wire:model.blur="name" placeholder="Nama Product (misal: Dran Sword Starter)" />
                        <flux:error name="name" />
                    </flux:field>

                    <flux:field>
                        <flux:label>Tanggal Rilis <flux:badge size="sm" variant="ghost">Opsional</flux:badge></flux:label>
                        <flux:input wire:model.blur="releaseDate" type="date" />
                        <flux:error name="releaseDate" />
                    </flux:field>
                </div>

                <div class="flex gap-2 pt-2">
                    <flux:button wire:click="save" variant="primary">Simpan</flux:button>
                    <flux:button wire:click="cancelForm" variant="ghost">Batal</flux:button>
                </div>
            </div>
        </flux:card>
    @endif

    {{-- Tabel Product --}}
    <flux:table>
        <flux:table.columns>
            <flux:table.column>Kode</flux:table.column>
            <flux:table.column>Nama</flux:table.column>
            <flux:table.column>Series</flux:table.column>
            <flux:table.column>Tanggal Rilis</flux:table.column>
            <flux:table.column>Aksi</flux:table.column>
        </flux:table.columns>

        <flux:table.rows>
            @forelse ($products as $product)
                <flux:table.row wire:key="product-{{ $product->id }}">
                    <flux:table.cell class="font-medium">{{ $product->product_code }}</flux:table.cell>
                    <flux:table.cell>{{ $product->name }}</flux:table.cell>
                    <flux:table.cell>{{ $product->series->name ?? '—' }}</flux:table.cell>
                    <flux:table.cell>{{ $product->release_date ? $product->release_date->format('d M Y') : '—' }}</flux:table.cell>
                    <flux:table.cell>
                        <div class="flex gap-2">
                            <flux:button
                                wire:click="openEditForm({{ $product->id }})"
                                size="sm"
                                variant="ghost"
                                icon="pencil"
                            >
                                Ubah
                            </flux:button>
                            <flux:button
                                wire:click="confirmDelete({{ $product->id }})"
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
                    <flux:table.cell colspan="5" class="text-center text-zinc-500 dark:text-zinc-400">
                        @if ($search !== '')
                            Tidak ada Product yang cocok dengan pencarian "{{ $search }}".
                        @else
                            Belum ada data Product. Klik "Tambah Product Baru" untuk mulai.
                        @endif
                    </flux:table.cell>
                </flux:table.row>
            @endforelse
        </flux:table.rows>
    </flux:table>
</div>

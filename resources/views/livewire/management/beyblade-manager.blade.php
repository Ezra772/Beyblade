<div class="space-y-6">
    {{-- Header --}}
    <div class="flex items-center justify-between">
        <flux:heading size="xl">Kelola Official Beyblade</flux:heading>
        <flux:button wire:click="openCreateForm" variant="primary" icon="plus">
            Tambah Beyblade Baru
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
            <flux:callout.text>Apakah kamu yakin ingin menghapus Beyblade ini? Tindakan ini tidak dapat dibatalkan.</flux:callout.text>
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
                {{ $editingId ? 'Ubah Beyblade' : 'Tambah Beyblade Baru' }}
            </flux:heading>

            <div class="space-y-4">
                <flux:field>
                    <flux:label>Nama Official (misal: Wizard Rod 5-70 Ball)</flux:label>
                    <flux:input wire:model.blur="name" placeholder="Nama Beyblade" />
                    <flux:error name="name" />
                </flux:field>

                <div class="grid grid-cols-1 gap-4 sm:grid-cols-3">
                    <flux:field>
                        <flux:label>Blade</flux:label>
                        @if ($blades->isEmpty())
                            <flux:text size="sm" class="text-zinc-500">Belum ada Blade.</flux:text>
                        @else
                            <flux:select wire:model="bladeId" placeholder="Pilih Blade...">
                                @foreach($blades as $b)
                                    <flux:select.option value="{{ $b->id }}">{{ $b->name }}</flux:select.option>
                                @endforeach
                            </flux:select>
                        @endif
                        <flux:error name="bladeId" />
                    </flux:field>

                    <flux:field>
                        <flux:label>Ratchet</flux:label>
                        @if ($ratchets->isEmpty())
                            <flux:text size="sm" class="text-zinc-500">Belum ada Ratchet.</flux:text>
                        @else
                            <flux:select wire:model="ratchetId" placeholder="Pilih Ratchet...">
                                @foreach($ratchets as $r)
                                    <flux:select.option value="{{ $r->id }}">{{ $r->name }}</flux:select.option>
                                @endforeach
                            </flux:select>
                        @endif
                        <flux:error name="ratchetId" />
                    </flux:field>

                    <flux:field>
                        <flux:label>Bit</flux:label>
                        @if ($bits->isEmpty())
                            <flux:text size="sm" class="text-zinc-500">Belum ada Bit.</flux:text>
                        @else
                            <flux:select wire:model="bitId" placeholder="Pilih Bit...">
                                @foreach($bits as $b)
                                    <flux:select.option value="{{ $b->id }}">{{ $b->name }}</flux:select.option>
                                @endforeach
                            </flux:select>
                        @endif
                        <flux:error name="bitId" />
                    </flux:field>
                </div>

                <div class="flex gap-2 pt-2">
                    <flux:button wire:click="save" variant="primary">Simpan</flux:button>
                    <flux:button wire:click="cancelForm" variant="ghost">Batal</flux:button>
                </div>
            </div>
        </flux:card>
    @endif

    {{-- Tabel Beyblade --}}
    <flux:table>
        <flux:table.columns>
            <flux:table.column>Nama Official</flux:table.column>
            <flux:table.column>Blade</flux:table.column>
            <flux:table.column>Ratchet</flux:table.column>
            <flux:table.column>Bit</flux:table.column>
            <flux:table.column>Aksi</flux:table.column>
        </flux:table.columns>

        <flux:table.rows>
            @forelse ($beyblades as $beyblade)
                <flux:table.row wire:key="beyblade-{{ $beyblade->id }}">
                    <flux:table.cell class="font-medium">{{ $beyblade->name }}</flux:table.cell>
                    <flux:table.cell>{{ $beyblade->blade->name ?? '—' }}</flux:table.cell>
                    <flux:table.cell>{{ $beyblade->ratchet->name ?? '—' }}</flux:table.cell>
                    <flux:table.cell>{{ $beyblade->bit->name ?? '—' }}</flux:table.cell>
                    <flux:table.cell>
                        <div class="flex gap-2">
                            <flux:button
                                wire:click="openEditForm({{ $beyblade->id }})"
                                size="sm"
                                variant="ghost"
                                icon="pencil"
                            >
                                Ubah
                            </flux:button>
                            <flux:button
                                wire:click="confirmDelete({{ $beyblade->id }})"
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
                            Tidak ada Beyblade yang cocok dengan pencarian "{{ $search }}".
                        @else
                            Belum ada data Beyblade. Klik "Tambah Beyblade Baru" untuk mulai.
                        @endif
                    </flux:table.cell>
                </flux:table.row>
            @endforelse
        </flux:table.rows>
    </flux:table>
</div>

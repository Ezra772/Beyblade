<div class="space-y-8">
    {{-- Header --}}
    <flux:heading size="xl">Import & Export</flux:heading>

    {{-- ═══════════════════════════════════════════════════════════════════════ --}}
    {{-- BAGIAN A: IMPORT --}}
    {{-- ═══════════════════════════════════════════════════════════════════════ --}}
    <flux:card>
        <flux:heading size="lg" class="mb-1">Import Data</flux:heading>
        <flux:text size="sm" class="mb-4 text-zinc-500">
            Tambah data baru secara massal dari file JSON format scraping (camelCase, satu kategori per file).
        </flux:text>

        <div class="space-y-4">
            <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                {{-- Kategori --}}
                <flux:field>
                    <flux:label>Kategori</flux:label>
                    <flux:select wire:model="importEntityType">
                        <flux:select.option value="blade">Blade</flux:select.option>
                        <flux:select.option value="ratchet">Ratchet</flux:select.option>
                        <flux:select.option value="bit">Bit</flux:select.option>
                    </flux:select>
                </flux:field>

                {{-- Strategi Duplikat --}}
                <flux:field>
                    <flux:label>Strategi Nama Duplikat</flux:label>
                    <flux:select wire:model="importStrategy">
                        <flux:select.option value="SKIP_DUPLICATES">Lewati (Skip)</flux:select.option>
                        <flux:select.option value="OVERWRITE_DUPLICATES">Timpa (Overwrite)</flux:select.option>
                        <flux:select.option value="REJECT_ON_DUPLICATE">Tolak & Laporkan</flux:select.option>
                    </flux:select>
                </flux:field>
            </div>

            {{-- File JSON --}}
            <flux:field>
                <flux:label>File JSON</flux:label>
                <input type="file" wire:model="importFile" accept=".json"
                    class="block w-full text-sm text-zinc-700 dark:text-zinc-300 file:mr-4 file:rounded-md file:border-0 file:bg-zinc-100 file:px-4 file:py-2 file:text-sm file:font-medium dark:file:bg-zinc-700" />
                <flux:error name="importFile" />
            </flux:field>

            <flux:button wire:click="runImport" variant="primary" icon="arrow-up-tray">
                Jalankan Import
            </flux:button>
        </div>

        {{-- Hasil Import --}}
        @if ($lastImportResult)
            <div class="mt-4 space-y-3">
                <flux:separator />

                <div class="grid grid-cols-3 gap-3 text-center">
                    <div class="rounded-lg bg-green-50 p-3 dark:bg-green-900/20">
                        <div class="text-2xl font-bold text-green-600 dark:text-green-400">{{ $lastImportResult['successCount'] }}</div>
                        <div class="text-xs text-green-700 dark:text-green-300">Berhasil</div>
                    </div>
                    <div class="rounded-lg bg-yellow-50 p-3 dark:bg-yellow-900/20">
                        <div class="text-2xl font-bold text-yellow-600 dark:text-yellow-400">{{ $lastImportResult['skippedCount'] }}</div>
                        <div class="text-xs text-yellow-700 dark:text-yellow-300">Dilewati</div>
                    </div>
                    <div class="rounded-lg bg-red-50 p-3 dark:bg-red-900/20">
                        <div class="text-2xl font-bold text-red-600 dark:text-red-400">{{ $lastImportResult['failedCount'] }}</div>
                        <div class="text-xs text-red-700 dark:text-red-300">Gagal</div>
                    </div>
                </div>

                @if (! empty($lastImportResult['errors']))
                    <flux:callout variant="danger">
                        <flux:callout.heading>Detail Error</flux:callout.heading>
                        <ul class="mt-1 list-inside list-disc space-y-1 text-sm">
                            @foreach ($lastImportResult['errors'] as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </flux:callout>
                @endif
            </div>
        @endif
    </flux:card>

    {{-- ═══════════════════════════════════════════════════════════════════════ --}}
    {{-- BAGIAN B: EXPORT / BACKUP --}}
    {{-- ═══════════════════════════════════════════════════════════════════════ --}}
    <flux:card>
        <flux:heading size="lg" class="mb-1">Export / Backup</flux:heading>
        <flux:text size="sm" class="mb-4 text-zinc-500">
            Unduh seluruh data Blade, Ratchet, dan Bit dalam satu file JSON (format internal snake_case).
        </flux:text>

        <flux:button wire:click="exportBackup" variant="ghost" icon="arrow-down-tray">
            Unduh Backup
        </flux:button>
    </flux:card>

    {{-- ═══════════════════════════════════════════════════════════════════════ --}}
    {{-- BAGIAN C: RESTORE --}}
    {{-- ═══════════════════════════════════════════════════════════════════════ --}}
    <flux:card>
        <flux:heading size="lg" class="mb-1">Restore dari Backup</flux:heading>
        <flux:text size="sm" class="mb-4 text-zinc-500">
            Pulihkan data dari file backup yang diunduh sebelumnya. Mode "Timpa Semua" akan menghapus seluruh data yang ada terlebih dahulu.
        </flux:text>

        <div class="space-y-4">
            {{-- Mode Restore --}}
            <flux:field>
                <flux:label>Mode Restore</flux:label>
                <flux:select wire:model="restoreMode">
                    <flux:select.option value="MERGE">Gabungkan (Merge) — hanya tambahkan data baru</flux:select.option>
                    <flux:select.option value="REPLACE_ALL">Timpa Semua — hapus data lama, pulihkan dari backup</flux:select.option>
                </flux:select>
            </flux:field>

            {{-- Peringatan REPLACE_ALL --}}
            @if ($restoreMode === 'REPLACE_ALL')
                <flux:callout variant="danger" icon="exclamation-triangle">
                    <flux:callout.heading>Peringatan</flux:callout.heading>
                    <flux:callout.text>Mode ini akan <strong>menghapus semua data Blade, Ratchet, dan Bit yang ada</strong> sebelum memulihkan backup. Tindakan ini tidak dapat dibatalkan.</flux:callout.text>
                </flux:callout>
            @endif

            {{-- File Backup --}}
            <flux:field>
                <flux:label>File Backup (.json)</flux:label>
                <input type="file" wire:model="restoreFile" accept=".json"
                    class="block w-full text-sm text-zinc-700 dark:text-zinc-300 file:mr-4 file:rounded-md file:border-0 file:bg-zinc-100 file:px-4 file:py-2 file:text-sm file:font-medium dark:file:bg-zinc-700" />
                <flux:error name="restoreFile" />
            </flux:field>

            <flux:button wire:click="runRestore" variant="primary" icon="arrow-path">
                Jalankan Restore
            </flux:button>
        </div>

        {{-- Hasil Restore --}}
        @if ($restoreMessage)
            <div class="mt-4">
                <flux:separator />
                <flux:callout variant="success" icon="check-circle" class="mt-3">
                    <flux:callout.text>{{ $restoreMessage }}</flux:callout.text>
                </flux:callout>
            </div>
        @endif
    </flux:card>
</div>

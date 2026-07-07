<?php

namespace App\Livewire\Management;

use App\Domain\Management\Import\ImportStrategy;
use App\Domain\Management\Import\PartImportValidator;
use App\Repositories\Contracts\BitRepositoryInterface;
use App\Repositories\Contracts\BladeRepositoryInterface;
use App\Repositories\Contracts\RatchetRepositoryInterface;
use Livewire\Component;
use Livewire\WithFileUploads;

class ImportExport extends Component
{
    use WithFileUploads;

    // ─── Import ───────────────────────────────────────────────────────────────

    public $importFile = null;

    public string $importEntityType = 'blade';

    public string $importStrategy = 'SKIP_DUPLICATES';

    /** @var array{successCount: int, skippedCount: int, failedCount: int, errors: string[]}|null */
    public ?array $lastImportResult = null;

    // ─── Restore ──────────────────────────────────────────────────────────────

    public $restoreFile = null;

    public string $restoreMode = 'MERGE';

    public ?string $restoreMessage = null;

    /**
     * Key mapping: camelCase (JSON scraping format) → snake_case (DB column).
     *
     * @var array<string, array<string, string>>
     */
    private const CAMEL_TO_SNAKE = [
        'blade' => [
            'productCode' => 'product_code',
            'burstResistance' => 'burst_resistance',
        ],
        'ratchet' => [
            'burstResistance' => 'burst_resistance',
        ],
        'bit' => [],
    ];

    /** @var array<string, array{required: string[], performance: string[]}> */
    private const VALIDATION_KEYS = [
        'blade' => [
            'required' => ['name', 'series', 'weight', 'attack', 'defense', 'stamina', 'smash', 'upper', 'recoil', 'burstResistance'],
            'performance' => ['attack', 'defense', 'stamina', 'smash', 'upper', 'recoil', 'burstResistance'],
        ],
        'ratchet' => [
            'required' => ['name', 'height', 'weight', 'stability', 'burstResistance'],
            'performance' => ['stability', 'burstResistance'],
        ],
        'bit' => [
            'required' => ['name', 'speed', 'stamina', 'grip', 'control', 'movement', 'dash'],
            'performance' => ['speed', 'stamina', 'grip', 'control', 'movement', 'dash'],
        ],
    ];

    public function runImport(
        BladeRepositoryInterface $bladeRepository,
        RatchetRepositoryInterface $ratchetRepository,
        BitRepositoryInterface $bitRepository,
        PartImportValidator $validator,
    ): void {
        $this->validate(['importFile' => ['required', 'file', 'mimes:json,txt']]);

        $content = file_get_contents($this->importFile->getRealPath());
        $rows = json_decode($content, true);

        if (! is_array($rows)) {
            $this->lastImportResult = [
                'successCount' => 0,
                'skippedCount' => 0,
                'failedCount' => 0,
                'errors' => ['File JSON tidak valid atau format tidak dikenali.'],
            ];

            return;
        }

        $strategy = match ($this->importStrategy) {
            'OVERWRITE_DUPLICATES' => ImportStrategy::OVERWRITE_DUPLICATES,
            'REJECT_ON_DUPLICATE' => ImportStrategy::REJECT_ON_DUPLICATE,
            default => ImportStrategy::SKIP_DUPLICATES,
        };
        $validationKeys = self::VALIDATION_KEYS[$this->importEntityType];
        $camelToSnake = self::CAMEL_TO_SNAKE[$this->importEntityType];

        $repository = match ($this->importEntityType) {
            'blade' => $bladeRepository,
            'ratchet' => $ratchetRepository,
            'bit' => $bitRepository,
        };

        $successCount = 0;
        $skippedCount = 0;
        $failedCount = 0;
        $errors = [];

        foreach ($rows as $index => $row) {
            $rowLabel = "Baris ".($index + 1)." ('".(isset($row['name']) ? $row['name'] : '?')."')";

            // 1. Structural + range validation (pure domain, no DB)
            $rowErrors = $validator->validate($row, $validationKeys['required'], $validationKeys['performance']);

            if (! empty($rowErrors)) {
                $failedCount++;
                foreach ($rowErrors as $err) {
                    $errors[] = "{$rowLabel}: {$err}";
                }
                continue;
            }

            // 2. Convert camelCase → snake_case for DB layer
            $data = [];
            foreach ($row as $key => $value) {
                $dbKey = $camelToSnake[$key] ?? $key;
                $data[$dbKey] = $value;
            }

            if ($this->importEntityType === 'blade') {
                $data = $this->resolveBladeSeries($data);
            }

            // 3. Duplicate check + strategy
            $isDuplicate = $repository->isNameTaken($data['name']);

            if ($isDuplicate) {
                switch ($strategy) {
                    case ImportStrategy::SKIP_DUPLICATES:
                        $skippedCount++;
                        continue 2;

                    case ImportStrategy::REJECT_ON_DUPLICATE:
                        $failedCount++;
                        $errors[] = "{$rowLabel}: nama sudah ada, ditolak.";
                        continue 2;

                    case ImportStrategy::OVERWRITE_DUPLICATES:
                        $existing = $repository->findByName($data['name']);
                        if ($existing) {
                            $repository->update($existing->id, $data);
                            $successCount++;
                        }
                        continue 2;
                }
            }

            // 4. Create new record
            $repository->create($data);
            $successCount++;
        }

        $this->lastImportResult = [
            'successCount' => $successCount,
            'skippedCount' => $skippedCount,
            'failedCount' => $failedCount,
            'errors' => $errors,
        ];
        $this->importFile = null;
    }

    public function exportBackup(
        BladeRepositoryInterface $bladeRepository,
        RatchetRepositoryInterface $ratchetRepository,
        BitRepositoryInterface $bitRepository,
    ) {
        $backup = [
            'backup_version' => 1,
            'exported_at' => now()->toIso8601String(),
            'blades' => $bladeRepository->all()->toArray(),
            'ratchets' => $ratchetRepository->all()->toArray(),
            'bits' => $bitRepository->all()->toArray(),
        ];

        $filename = 'beyblade-x-backup-'.now()->format('Y-m-d-His').'.json';

        return response()->streamDownload(
            fn () => print(json_encode($backup, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE)),
            $filename,
            ['Content-Type' => 'application/json']
        );
    }

    public function runRestore(
        BladeRepositoryInterface $bladeRepository,
        RatchetRepositoryInterface $ratchetRepository,
        BitRepositoryInterface $bitRepository,
    ): void {
        $this->validate(['restoreFile' => ['required', 'file', 'mimes:json,txt']]);

        $content = file_get_contents($this->restoreFile->getRealPath());
        $backup = json_decode($content, true);

        if (! is_array($backup) || ! isset($backup['backup_version'])) {
            $this->restoreMessage = 'File backup tidak valid: field backup_version tidak ditemukan.';

            return;
        }

        if ($backup['backup_version'] !== 1) {
            $this->restoreMessage = "Versi backup ({$backup['backup_version']}) tidak didukung oleh versi aplikasi ini.";

            return;
        }

        if ($this->restoreMode === 'REPLACE_ALL') {
            // Truncate all three tables then re-insert everything
            \App\Models\Blade::query()->delete();
            \App\Models\Ratchet::query()->delete();
            \App\Models\Bit::query()->delete();

            $bladeCount = 0;
            $ratchetCount = 0;
            $bitCount = 0;

            foreach ($backup['blades'] ?? [] as $row) {
                $data = $this->stripTimestamps($row);
                $data = $this->resolveBladeSeries($data);
                $bladeRepository->create($data);
                $bladeCount++;
            }

            foreach ($backup['ratchets'] ?? [] as $row) {
                $ratchetRepository->create($this->stripTimestamps($row));
                $ratchetCount++;
            }

            foreach ($backup['bits'] ?? [] as $row) {
                $bitRepository->create($this->stripTimestamps($row));
                $bitCount++;
            }

            $this->restoreMessage = "Restore selesai (REPLACE ALL): {$bladeCount} Blade, {$ratchetCount} Ratchet, {$bitCount} Bit dipulihkan.";
        } else {
            // MERGE — skip existing by name, insert only new ones
            $bladeInserted = 0;
            $ratchetInserted = 0;
            $bitInserted = 0;

            foreach ($backup['blades'] ?? [] as $row) {
                $data = $this->stripTimestamps($row);
                $data = $this->resolveBladeSeries($data);
                if (! $bladeRepository->isNameTaken($data['name'])) {
                    $bladeRepository->create($data);
                    $bladeInserted++;
                }
            }

            foreach ($backup['ratchets'] ?? [] as $row) {
                $data = $this->stripTimestamps($row);
                if (! $ratchetRepository->isNameTaken($data['name'])) {
                    $ratchetRepository->create($data);
                    $ratchetInserted++;
                }
            }

            foreach ($backup['bits'] ?? [] as $row) {
                $data = $this->stripTimestamps($row);
                if (! $bitRepository->isNameTaken($data['name'])) {
                    $bitRepository->create($data);
                    $bitInserted++;
                }
            }

            $this->restoreMessage = "Restore selesai (MERGE): {$bladeInserted} Blade, {$ratchetInserted} Ratchet, {$bitInserted} Bit baru ditambahkan. Data yang sudah ada tidak diubah.";
        }

        $this->restoreFile = null;
    }

    /**
     * Remove id, created_at, updated_at from backup row before insert/update.
     *
     * @param  array<string, mixed>  $row
     * @return array<string, mixed>
     */
    private function stripTimestamps(array $row): array
    {
        unset($row['id'], $row['created_at'], $row['updated_at']);

        return $row;
    }

    private function resolveBladeSeries(array $data): array
    {
        if (isset($data['series'])) {
            $seriesName = $data['series'];
            $seriesRepo = app(\App\Repositories\Contracts\SeriesRepositoryInterface::class);
            $series = $seriesRepo->findByName($seriesName);
            
            if (!$series) {
                $series = $seriesRepo->create(['name' => $seriesName]);
            }
            
            $data['series_id'] = $series->id;
            unset($data['series']);
        }
        
        return $data;
    }

    public function render()
    {
        return view('livewire.management.import-export')
            ->layout('layouts.app', ['title' => 'Import & Export']);
    }
}

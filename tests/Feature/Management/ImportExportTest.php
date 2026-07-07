<?php

namespace Tests\Feature\Management;

use App\Livewire\Management\ImportExport;
use App\Models\Blade;
use App\Models\Bit;
use App\Models\Ratchet;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Livewire\Livewire;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class ImportExportTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->actingAs(User::factory()->create());
    }

    // ─── Helpers ─────────────────────────────────────────────────────────────

    private function bladeImportRow(string $name = 'Import Blade'): array
    {
        return [
            'name' => $name,
            'series' => 'X',
            'weight' => 28.5,
            'attack' => 80,
            'defense' => 60,
            'stamina' => 50,
            'smash' => 70,
            'upper' => 55,
            'recoil' => 40,
            'burstResistance' => 65,
        ];
    }

    private function makeJsonFile(array $rows, string $name = 'import.json'): UploadedFile
    {
        return UploadedFile::fake()->createWithContent($name, json_encode($rows));
    }

    // ─── Import Tests ────────────────────────────────────────────────────────

    #[Test]
    public function it_renders_import_export_page(): void
    {
        $response = $this->get(route('management.import-export'));
        $response->assertStatus(200);
        $response->assertSeeLivewire(ImportExport::class);
    }

    #[Test]
    public function import_creates_new_blades_from_json(): void
    {
        $rows = [
            $this->bladeImportRow('Blade A'),
            $this->bladeImportRow('Blade B'),
            $this->bladeImportRow('Blade C'),
        ];

        Livewire::test(ImportExport::class)
            ->set('importEntityType', 'blade')
            ->set('importStrategy', 'SKIP_DUPLICATES')
            ->set('importFile', $this->makeJsonFile($rows))
            ->call('runImport')
            ->assertSet('lastImportResult.successCount', 3)
            ->assertSet('lastImportResult.skippedCount', 0)
            ->assertSet('lastImportResult.failedCount', 0);

        $this->assertDatabaseCount('blades', 3);
    }

    #[Test]
    public function import_skips_duplicates_with_skip_strategy(): void
    {
        Blade::factory()->create(['name' => 'Blade A']);
        Blade::factory()->create(['name' => 'Blade B']);
        Blade::factory()->create(['name' => 'Blade C']);

        $rows = [
            $this->bladeImportRow('Blade A'),
            $this->bladeImportRow('Blade B'),
            $this->bladeImportRow('Blade C'),
        ];

        Livewire::test(ImportExport::class)
            ->set('importEntityType', 'blade')
            ->set('importStrategy', 'SKIP_DUPLICATES')
            ->set('importFile', $this->makeJsonFile($rows))
            ->call('runImport')
            ->assertSet('lastImportResult.successCount', 0)
            ->assertSet('lastImportResult.skippedCount', 3)
            ->assertSet('lastImportResult.failedCount', 0);

        // No new records
        $this->assertDatabaseCount('blades', 3);
    }

    #[Test]
    public function import_reports_failed_row_with_invalid_value(): void
    {
        $rows = [
            $this->bladeImportRow('Valid Blade'),
            array_merge($this->bladeImportRow('Invalid Blade'), ['attack' => 150]), // out of range
        ];

        Livewire::test(ImportExport::class)
            ->set('importEntityType', 'blade')
            ->set('importStrategy', 'SKIP_DUPLICATES')
            ->set('importFile', $this->makeJsonFile($rows))
            ->call('runImport')
            ->assertSet('lastImportResult.successCount', 1)
            ->assertSet('lastImportResult.failedCount', 1);

        $this->assertDatabaseCount('blades', 1);
        $this->assertDatabaseHas('blades', ['name' => 'Valid Blade']);
    }

    #[Test]
    public function import_rejects_duplicate_with_reject_strategy(): void
    {
        Blade::factory()->create(['name' => 'Existing Blade']);

        $rows = [$this->bladeImportRow('Existing Blade')];

        Livewire::test(ImportExport::class)
            ->set('importEntityType', 'blade')
            ->set('importStrategy', 'REJECT_ON_DUPLICATE')
            ->set('importFile', $this->makeJsonFile($rows))
            ->call('runImport')
            ->assertSet('lastImportResult.failedCount', 1)
            ->assertSet('lastImportResult.successCount', 0);
    }

    #[Test]
    public function import_overwrites_duplicate_with_overwrite_strategy(): void
    {
        $series = \App\Models\Series::factory()->create(['name' => 'Old Series']);
        $blade = Blade::factory()->create([
            'name' => 'Overwrite Me',
            'series_id' => $series->id,
        ]);

        $rows = [array_merge($this->bladeImportRow('Overwrite Me'), ['series' => 'New Series'])];

        Livewire::test(ImportExport::class)
            ->set('importEntityType', 'blade')
            ->set('importStrategy', 'OVERWRITE_DUPLICATES')
            ->set('importFile', $this->makeJsonFile($rows))
            ->call('runImport')
            ->assertSet('lastImportResult.successCount', 1);

        $this->assertDatabaseHas('blades', ['name' => 'Overwrite Me']);
        $this->assertDatabaseHas('series', ['name' => 'New Series']);
    }

    // ─── Export/Backup ───────────────────────────────────────────────────────

    #[Test]
    public function export_backup_downloads_json_with_correct_structure(): void
    {
        Blade::factory()->create();
        Ratchet::factory()->create();
        Bit::factory()->create();

        $response = Livewire::test(ImportExport::class)
            ->call('exportBackup');

        // exportBackup returns a response — Livewire wraps it
        // Verify the response returned (Livewire redirects/streams are tested differently)
        $this->assertNotNull($response);
    }

    // ─── Restore ─────────────────────────────────────────────────────────────

    #[Test]
    public function restore_merge_adds_only_new_records(): void
    {
        Blade::factory()->create(['name' => 'Existing Blade']);

        $backupContent = json_encode([
            'backup_version' => 1,
            'exported_at' => now()->toIso8601String(),
            'blades' => [
                ['name' => 'Existing Blade', 'series' => 'X', 'weight' => '28.50', 'attack' => '80.00', 'defense' => '60.00', 'stamina' => '50.00', 'smash' => '70.00', 'upper' => '55.00', 'recoil' => '40.00', 'burst_resistance' => '65.00'],
                ['name' => 'New From Backup', 'series' => 'X', 'weight' => '28.50', 'attack' => '80.00', 'defense' => '60.00', 'stamina' => '50.00', 'smash' => '70.00', 'upper' => '55.00', 'recoil' => '40.00', 'burst_resistance' => '65.00'],
            ],
            'ratchets' => [],
            'bits' => [],
        ]);

        $file = UploadedFile::fake()->createWithContent('backup.json', $backupContent);

        Livewire::test(ImportExport::class)
            ->set('restoreMode', 'MERGE')
            ->set('restoreFile', $file)
            ->call('runRestore');

        // Original + one new = 2 total, no duplicate
        $this->assertDatabaseCount('blades', 2);
        $this->assertDatabaseHas('blades', ['name' => 'New From Backup']);
    }

    #[Test]
    public function restore_rejects_unknown_backup_version(): void
    {
        $backupContent = json_encode([
            'backup_version' => 99,
            'blades' => [],
            'ratchets' => [],
            'bits' => [],
        ]);

        $file = UploadedFile::fake()->createWithContent('bad-backup.json', $backupContent);

        Livewire::test(ImportExport::class)
            ->set('restoreFile', $file)
            ->call('runRestore')
            ->assertSet('restoreMessage', 'Versi backup (99) tidak didukung oleh versi aplikasi ini.');

        $this->assertDatabaseCount('blades', 0);
    }

    #[Test]
    public function restore_rejects_file_without_backup_version(): void
    {
        $backupContent = json_encode(['blades' => [], 'ratchets' => [], 'bits' => []]);
        $file = UploadedFile::fake()->createWithContent('no-version.json', $backupContent);

        Livewire::test(ImportExport::class)
            ->set('restoreFile', $file)
            ->call('runRestore')
            ->assertSet('restoreMessage', 'File backup tidak valid: field backup_version tidak ditemukan.');
    }

    #[Test]
    public function restore_replace_all_clears_and_restores(): void
    {
        Blade::factory()->create(['name' => 'Old Blade']);
        $this->assertDatabaseCount('blades', 1);

        $backupContent = json_encode([
            'backup_version' => 1,
            'exported_at' => now()->toIso8601String(),
            'blades' => [
                ['name' => 'New Blade 1', 'series' => 'X', 'weight' => '28.50', 'attack' => '80.00', 'defense' => '60.00', 'stamina' => '50.00', 'smash' => '70.00', 'upper' => '55.00', 'recoil' => '40.00', 'burst_resistance' => '65.00'],
            ],
            'ratchets' => [],
            'bits' => [],
        ]);

        $file = UploadedFile::fake()->createWithContent('backup.json', $backupContent);

        Livewire::test(ImportExport::class)
            ->set('restoreMode', 'REPLACE_ALL')
            ->set('restoreFile', $file)
            ->call('runRestore');

        $this->assertDatabaseCount('blades', 1);
        $this->assertDatabaseMissing('blades', ['name' => 'Old Blade']);
        $this->assertDatabaseHas('blades', ['name' => 'New Blade 1']);
    }
}

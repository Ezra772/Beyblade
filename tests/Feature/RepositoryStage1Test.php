<?php

namespace Tests\Feature;

use App\Models\Blade;
use App\Models\Bit;
use App\Models\Ratchet;
use App\Repositories\Contracts\BitRepositoryInterface;
use App\Repositories\Contracts\BladeRepositoryInterface;
use App\Repositories\Contracts\RatchetRepositoryInterface;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RepositoryStage1Test extends TestCase
{
    use RefreshDatabase;

    // ── BladeRepository ────────────────────────────────────────────────────

    public function test_blade_repository_resolves_from_container(): void
    {
        $repo = app(BladeRepositoryInterface::class);

        $this->assertInstanceOf(\App\Repositories\Eloquent\BladeRepository::class, $repo);
    }

    public function test_blade_repository_all_returns_collection(): void
    {
        Blade::factory()->createMany([
            ['name' => 'Wizard Rod'],
            ['name' => 'Dran Sword'],
        ]);

        $blades = app(BladeRepositoryInterface::class)->all();

        $this->assertCount(2, $blades);
        $this->assertEquals('Dran Sword', $blades->first()->name); // ordered by name
    }

    public function test_blade_repository_find_returns_correct_blade(): void
    {
        $blade = Blade::factory()->create(['name' => 'Hell Scythe']);

        $found = app(BladeRepositoryInterface::class)->find($blade->id);

        $this->assertNotNull($found);
        $this->assertEquals('Hell Scythe', $found->name);
    }

    public function test_blade_repository_find_by_name(): void
    {
        Blade::factory()->create(['name' => 'Wizard Rod']);

        $found = app(BladeRepositoryInterface::class)->findByName('Wizard Rod');

        $this->assertNotNull($found);
        $this->assertEquals('Wizard Rod', $found->name);
    }

    public function test_blade_repository_create(): void
    {
        $repo = app(BladeRepositoryInterface::class);
        $series = \App\Models\Series::factory()->create(['name' => 'X']);
        $blade = $repo->create([
            'name' => 'New Blade',
            'series_id' => $series->id,
            'product_code' => 'BX-99',
            'weight' => 33.50,
            'attack' => 80.00,
            'defense' => 60.00,
            'stamina' => 70.00,
            'smash' => 75.00,
            'upper' => 50.00,
            'recoil' => 45.00,
            'burst_resistance' => 65.00,
        ]);

        $this->assertInstanceOf(Blade::class, $blade);
        $this->assertDatabaseHas('blades', ['name' => 'New Blade']);
    }

    public function test_blade_repository_update(): void
    {
        $blade = Blade::factory()->create(['name' => 'Old Name', 'attack' => 50.00]);

        $updated = app(BladeRepositoryInterface::class)->update($blade->id, ['attack' => 95.00]);

        $this->assertEquals('95.00', $updated->attack);
    }

    public function test_blade_repository_delete(): void
    {
        $blade = Blade::factory()->create(['name' => 'To Delete']);

        $result = app(BladeRepositoryInterface::class)->delete($blade->id);

        $this->assertTrue($result);
        $this->assertDatabaseMissing('blades', ['name' => 'To Delete']);
    }

    public function test_blade_is_name_taken_returns_true_for_existing_name(): void
    {
        Blade::factory()->create(['name' => 'Wizard Rod']);

        $this->assertTrue(app(BladeRepositoryInterface::class)->isNameTaken('Wizard Rod'));
    }

    public function test_blade_is_name_taken_returns_false_for_new_name(): void
    {
        $this->assertFalse(app(BladeRepositoryInterface::class)->isNameTaken('Brand New Blade'));
    }

    public function test_blade_is_name_taken_excludes_own_id_on_update(): void
    {
        $blade = Blade::factory()->create(['name' => 'Wizard Rod']);

        $this->assertFalse(
            app(BladeRepositoryInterface::class)->isNameTaken('Wizard Rod', $blade->id)
        );
    }

    // ── RatchetRepository ──────────────────────────────────────────────────

    public function test_ratchet_repository_resolves_from_container(): void
    {
        $repo = app(RatchetRepositoryInterface::class);

        $this->assertInstanceOf(\App\Repositories\Eloquent\RatchetRepository::class, $repo);
    }

    public function test_ratchet_repository_all_returns_collection(): void
    {
        Ratchet::factory()->createMany([
            ['name' => '5-70'],
            ['name' => '3-60'],
        ]);

        $ratchets = app(RatchetRepositoryInterface::class)->all();

        $this->assertCount(2, $ratchets);
    }

    public function test_ratchet_is_name_taken(): void
    {
        Ratchet::factory()->create(['name' => '5-70']);

        $this->assertTrue(app(RatchetRepositoryInterface::class)->isNameTaken('5-70'));
        $this->assertFalse(app(RatchetRepositoryInterface::class)->isNameTaken('9-99'));
    }

    public function test_ratchet_height_stored_as_numeric(): void
    {
        $ratchet = Ratchet::factory()->create(['name' => '5-70', 'height' => 70.00]);

        $this->assertEquals('70.00', $ratchet->height);
    }

    // ── BitRepository ──────────────────────────────────────────────────────

    public function test_bit_repository_resolves_from_container(): void
    {
        $repo = app(BitRepositoryInterface::class);

        $this->assertInstanceOf(\App\Repositories\Eloquent\BitRepository::class, $repo);
    }

    public function test_bit_repository_all_returns_collection(): void
    {
        Bit::factory()->createMany([
            ['name' => 'Ball'],
            ['name' => 'Flat'],
        ]);

        $bits = app(BitRepositoryInterface::class)->all();

        $this->assertCount(2, $bits);
    }

    public function test_bit_is_name_taken(): void
    {
        Bit::factory()->create(['name' => 'Ball']);

        $this->assertTrue(app(BitRepositoryInterface::class)->isNameTaken('Ball'));
        $this->assertFalse(app(BitRepositoryInterface::class)->isNameTaken('Spike'));
    }
}

<?php

namespace Tests\Feature\Management;

use App\Livewire\Management\BladeManager;
use App\Models\Blade;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class BladeManagerTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->actingAs(User::factory()->create());
    }

    #[Test]
    public function it_renders_blade_manager_page(): void
    {
        $response = $this->get(route('management.blades'));

        $response->assertStatus(200);
        $response->assertSeeLivewire(BladeManager::class);
    }

    #[Test]
    public function it_lists_all_blades(): void
    {
        $series = \App\Models\Series::factory()->create(['name' => 'X']);
        $blade = Blade::factory()->create(['name' => 'Dran Sword', 'series_id' => $series->id]);

        Livewire::test(BladeManager::class)
            ->assertSee('Dran Sword')
            ->assertSee('X');
    }

    #[Test]
    public function it_can_create_a_new_blade(): void
    {
        $series = \App\Models\Series::factory()->create(['name' => 'X']);
        Livewire::test(BladeManager::class)
            ->call('openCreateForm')
            ->set('name', 'Test Blade')
            ->set('seriesId', $series->id)
            ->set('productCode', 'BX-01')
            ->set('weight', 34.5)
            ->set('attack', 80)
            ->set('defense', 60)
            ->set('stamina', 50)
            ->set('smash', 70)
            ->set('upper', 55)
            ->set('recoil', 40)
            ->set('burstResistance', 65)
            ->call('save')
            ->assertHasNoErrors()
            ->assertSet('showForm', false);

        $this->assertDatabaseHas('blades', ['name' => 'Test Blade', 'series_id' => $series->id]);
    }

    #[Test]
    public function it_rejects_duplicate_blade_name(): void
    {
        Blade::factory()->create(['name' => 'Dran Sword']);

        $series = \App\Models\Series::factory()->create(['name' => 'X']);
        Livewire::test(BladeManager::class)
            ->call('openCreateForm')
            ->set('name', 'Dran Sword')
            ->set('seriesId', $series->id)
            ->set('weight', 28.5)
            ->set('attack', 80)
            ->set('defense', 60)
            ->set('stamina', 50)
            ->set('smash', 70)
            ->set('upper', 55)
            ->set('recoil', 40)
            ->set('burstResistance', 65)
            ->call('save')
            ->assertHasErrors(['name']);

        $this->assertDatabaseCount('blades', 1);
    }

    #[Test]
    public function it_validates_attack_range(): void
    {
        $series = \App\Models\Series::factory()->create(['name' => 'X']);
        Livewire::test(BladeManager::class)
            ->call('openCreateForm')
            ->set('name', 'Test Blade')
            ->set('seriesId', $series->id)
            ->set('weight', 28.5)
            ->set('attack', 150)
            ->set('defense', 60)
            ->set('stamina', 50)
            ->set('smash', 70)
            ->set('upper', 55)
            ->set('recoil', 40)
            ->set('burstResistance', 65)
            ->call('save')
            ->assertHasErrors(['attack']);

        $this->assertDatabaseCount('blades', 0);
    }

    #[Test]
    public function it_can_edit_an_existing_blade(): void
    {
        $blade = Blade::factory()->create([
            'name' => 'Original Name',
            'series_id' => \App\Models\Series::factory()->create(['name' => 'X'])->id,
            'weight' => 28.5,
            'attack' => 80,
            'defense' => 60,
            'stamina' => 50,
            'smash' => 70,
            'upper' => 55,
            'recoil' => 40,
            'burst_resistance' => 65,
        ]);

        Livewire::test(BladeManager::class)
            ->call('openEditForm', $blade->id)
            ->assertSet('editingId', $blade->id)
            ->assertSet('name', 'Original Name')
            ->set('name', 'Updated Name')
            ->call('save');

        $this->assertDatabaseHas('blades', ['id' => $blade->id, 'name' => 'Updated Name']);
    }

    #[Test]
    public function it_shows_delete_confirmation_before_deleting(): void
    {
        $blade = Blade::factory()->create();

        Livewire::test(BladeManager::class)
            ->call('confirmDelete', $blade->id)
            ->assertSet('confirmingDeleteId', $blade->id)
            ->call('cancelDelete')
            ->assertSet('confirmingDeleteId', null);

        $this->assertDatabaseHas('blades', ['id' => $blade->id]);
    }

    #[Test]
    public function it_can_delete_a_blade(): void
    {
        $blade = Blade::factory()->create();

        Livewire::test(BladeManager::class)
            ->call('confirmDelete', $blade->id)
            ->call('delete');

        $this->assertDatabaseMissing('blades', ['id' => $blade->id]);
    }

    #[Test]
    public function it_filters_blades_by_search(): void
    {
        $series = \App\Models\Series::factory()->create(['name' => 'X']);
        Blade::factory()->create(['name' => 'Dran Sword', 'series_id' => $series->id]);
        Blade::factory()->create(['name' => 'Hells Scythe', 'series_id' => $series->id]);

        Livewire::test(BladeManager::class)
            ->set('search', 'Dran')
            ->assertSee('Dran Sword')
            ->assertDontSee('Hells Scythe');
    }
}

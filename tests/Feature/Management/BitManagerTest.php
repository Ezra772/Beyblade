<?php

namespace Tests\Feature\Management;

use App\Livewire\Management\BitManager;
use App\Models\Bit;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class BitManagerTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->actingAs(User::factory()->create());
    }

    #[Test]
    public function it_renders_bit_manager_page(): void
    {
        $response = $this->get(route('management.bits'));

        $response->assertStatus(200);
        $response->assertSeeLivewire(BitManager::class);
    }

    #[Test]
    public function it_lists_all_bits(): void
    {
        Bit::factory()->create(['name' => 'Flat']);

        Livewire::test(BitManager::class)
            ->assertSee('Flat');
    }

    #[Test]
    public function it_can_create_a_new_bit(): void
    {
        Livewire::test(BitManager::class)
            ->call('openCreateForm')
            ->set('name', 'Test Bit')
            ->set('speed', 80)
            ->set('stamina', 60)
            ->set('grip', 50)
            ->set('control', 70)
            ->set('movement', 55)
            ->set('dash', 65)
            ->call('save');

        $this->assertDatabaseHas('bits', ['name' => 'Test Bit']);
    }

    #[Test]
    public function it_rejects_duplicate_bit_name(): void
    {
        Bit::factory()->create(['name' => 'Flat']);

        Livewire::test(BitManager::class)
            ->call('openCreateForm')
            ->set('name', 'Flat')
            ->set('speed', 80)
            ->set('stamina', 60)
            ->set('grip', 50)
            ->set('control', 70)
            ->set('movement', 55)
            ->set('dash', 65)
            ->call('save')
            ->assertHasErrors(['name']);

        $this->assertDatabaseCount('bits', 1);
    }

    #[Test]
    public function it_validates_speed_range(): void
    {
        Livewire::test(BitManager::class)
            ->call('openCreateForm')
            ->set('name', 'Test Bit')
            ->set('speed', 150)
            ->set('stamina', 60)
            ->set('grip', 50)
            ->set('control', 70)
            ->set('movement', 55)
            ->set('dash', 65)
            ->call('save')
            ->assertHasErrors(['speed']);

        $this->assertDatabaseCount('bits', 0);
    }

    #[Test]
    public function it_can_edit_an_existing_bit(): void
    {
        $bit = Bit::factory()->create([
            'name' => 'Original Bit',
            'speed' => 80,
            'stamina' => 60,
            'grip' => 50,
            'control' => 70,
            'movement' => 55,
            'dash' => 65,
        ]);

        Livewire::test(BitManager::class)
            ->call('openEditForm', $bit->id)
            ->assertSet('editingId', $bit->id)
            ->set('name', 'Updated Bit')
            ->call('save');

        $this->assertDatabaseHas('bits', ['id' => $bit->id, 'name' => 'Updated Bit']);
    }

    #[Test]
    public function it_can_delete_a_bit(): void
    {
        $bit = Bit::factory()->create();

        Livewire::test(BitManager::class)
            ->call('confirmDelete', $bit->id)
            ->call('delete');

        $this->assertDatabaseMissing('bits', ['id' => $bit->id]);
    }

    #[Test]
    public function it_filters_bits_by_search(): void
    {
        Bit::factory()->create(['name' => 'Flat']);
        Bit::factory()->create(['name' => 'Needle']);

        Livewire::test(BitManager::class)
            ->set('search', 'Flat')
            ->assertSee('Flat')
            ->assertDontSee('Needle');
    }
}

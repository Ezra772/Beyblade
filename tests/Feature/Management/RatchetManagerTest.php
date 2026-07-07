<?php

namespace Tests\Feature\Management;

use App\Livewire\Management\RatchetManager;
use App\Models\Ratchet;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class RatchetManagerTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->actingAs(User::factory()->create());
    }

    #[Test]
    public function it_renders_ratchet_manager_page(): void
    {
        $response = $this->get(route('management.ratchets'));

        $response->assertStatus(200);
        $response->assertSeeLivewire(RatchetManager::class);
    }

    #[Test]
    public function it_lists_all_ratchets(): void
    {
        Ratchet::factory()->create(['name' => '3-60']);

        Livewire::test(RatchetManager::class)
            ->assertSee('3-60');
    }

    #[Test]
    public function it_can_create_a_new_ratchet(): void
    {
        Livewire::test(RatchetManager::class)
            ->call('openCreateForm')
            ->set('name', 'Test Ratchet')
            ->set('height', 6.0)
            ->set('weight', 5.5)
            ->set('stability', 70)
            ->set('burstResistance', 60)
            ->call('save');

        $this->assertDatabaseHas('ratchets', ['name' => 'Test Ratchet']);
    }

    #[Test]
    public function it_rejects_duplicate_ratchet_name(): void
    {
        Ratchet::factory()->create(['name' => '3-60']);

        Livewire::test(RatchetManager::class)
            ->call('openCreateForm')
            ->set('name', '3-60')
            ->set('height', 6.0)
            ->set('weight', 5.5)
            ->set('stability', 70)
            ->set('burstResistance', 60)
            ->call('save')
            ->assertHasErrors(['name']);

        $this->assertDatabaseCount('ratchets', 1);
    }

    #[Test]
    public function it_validates_stability_range(): void
    {
        Livewire::test(RatchetManager::class)
            ->call('openCreateForm')
            ->set('name', 'Test Ratchet')
            ->set('height', 6.0)
            ->set('weight', 5.5)
            ->set('stability', 150)
            ->set('burstResistance', 60)
            ->call('save')
            ->assertHasErrors(['stability']);

        $this->assertDatabaseCount('ratchets', 0);
    }

    #[Test]
    public function it_can_edit_an_existing_ratchet(): void
    {
        $ratchet = Ratchet::factory()->create([
            'name' => 'Original Ratchet',
            'height' => 6.0,
            'weight' => 5.5,
            'stability' => 70,
            'burst_resistance' => 60,
        ]);

        Livewire::test(RatchetManager::class)
            ->call('openEditForm', $ratchet->id)
            ->assertSet('editingId', $ratchet->id)
            ->set('name', 'Updated Ratchet')
            ->call('save');

        $this->assertDatabaseHas('ratchets', ['id' => $ratchet->id, 'name' => 'Updated Ratchet']);
    }

    #[Test]
    public function it_can_delete_a_ratchet(): void
    {
        $ratchet = Ratchet::factory()->create();

        Livewire::test(RatchetManager::class)
            ->call('confirmDelete', $ratchet->id)
            ->call('delete');

        $this->assertDatabaseMissing('ratchets', ['id' => $ratchet->id]);
    }

    #[Test]
    public function it_filters_ratchets_by_search(): void
    {
        Ratchet::factory()->create(['name' => '3-60']);
        Ratchet::factory()->create(['name' => '4-80']);

        Livewire::test(RatchetManager::class)
            ->set('search', '3-60')
            ->assertSee('3-60')
            ->assertDontSee('4-80');
    }
}

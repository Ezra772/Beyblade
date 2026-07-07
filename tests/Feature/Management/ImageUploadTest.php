<?php

namespace Tests\Feature\Management;

use App\Livewire\Management\BladeManager;
use App\Models\Blade;
use App\Models\Image;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class ImageUploadTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->actingAs(User::factory()->create());
        Storage::fake('public');
    }

    #[Test]
    public function it_stores_image_on_save_and_records_path_in_db(): void
    {
        Storage::fake('public');
        $file = UploadedFile::fake()->image('blade.jpg');

        $series = \App\Models\Series::factory()->create(['name' => 'X']);
        Livewire::test(BladeManager::class)
            ->call('openCreateForm')
            ->set('name', 'Test Blade With Image')
            ->set('seriesId', $series->id)
            ->set('weight', 34.5)
            ->set('attack', 80)
            ->set('defense', 60)
            ->set('stamina', 50)
            ->set('smash', 70)
            ->set('upper', 55)
            ->set('recoil', 40)
            ->set('burstResistance', 65)
            ->set('photo', $file)
            ->call('save')
            ->assertHasNoErrors();

        $blade = Blade::where('name', 'Test Blade With Image')->first();
        $this->assertNotNull($blade);

        $image = Image::where('owner_id', $blade->id)->where('owner_type', 'blade')->first();
        $this->assertNotNull($image);
        $this->assertTrue((bool) $image->is_primary);

        // owner_type must be the short morph key, NOT the full class name
        $this->assertEquals('blade', $image->owner_type);
        Storage::disk('public')->assertExists($image->path);
    }

    #[Test]
    public function replacing_image_removes_old_file_from_storage(): void
    {
        $series = \App\Models\Series::factory()->create(['name' => 'X']);
        $blade = Blade::factory()->create([
            'name' => 'Replace Me',
            'series_id' => $series->id,
            'weight' => 28.5,
            'attack' => 80,
            'defense' => 60,
            'stamina' => 50,
            'smash' => 70,
            'upper' => 55,
            'recoil' => 40,
            'burst_resistance' => 65,
        ]);

        // Upload original image
        $first = UploadedFile::fake()->image('first.jpg');
        Livewire::test(BladeManager::class)
            ->call('openEditForm', $blade->id)
            ->set('photo', $first)
            ->call('save');

        $firstImage = Image::where('owner_id', $blade->id)->first();
        $this->assertNotNull($firstImage);
        Storage::disk('public')->assertExists($firstImage->path);

        // Replace with second image
        $second = UploadedFile::fake()->image('second.jpg');
        Livewire::test(BladeManager::class)
            ->call('openEditForm', $blade->id)
            ->set('photo', $second)
            ->call('save');

        // Old file must be gone
        Storage::disk('public')->assertMissing($firstImage->path);

        // Only one image record remains
        $this->assertEquals(1, Image::where('owner_id', $blade->id)->count());
    }

    #[Test]
    public function remove_photo_deletes_file_and_record_without_deleting_blade(): void
    {
        $series = \App\Models\Series::factory()->create(['name' => 'X']);
        $blade = Blade::factory()->create([
            'name' => 'Has Image',
            'series_id' => $series->id,
            'weight' => 28.5,
            'attack' => 80,
            'defense' => 60,
            'stamina' => 50,
            'smash' => 70,
            'upper' => 55,
            'recoil' => 40,
            'burst_resistance' => 65,
        ]);

        $file = UploadedFile::fake()->image('removable.jpg');
        Livewire::test(BladeManager::class)
            ->call('openEditForm', $blade->id)
            ->set('photo', $file)
            ->call('save');

        $image = Image::where('owner_id', $blade->id)->first();
        $imagePath = $image->path;

        Livewire::test(BladeManager::class)
            ->call('openEditForm', $blade->id)
            ->call('removePhoto', $blade->id);

        Storage::disk('public')->assertMissing($imagePath);
        $this->assertEquals(0, Image::where('owner_id', $blade->id)->count());
        $this->assertDatabaseHas('blades', ['id' => $blade->id]);
    }
}

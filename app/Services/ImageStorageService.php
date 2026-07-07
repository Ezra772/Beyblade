<?php

namespace App\Services;

use App\Models\Image;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

class ImageStorageService
{
    /**
     * Store a new primary image for an owner, replacing any existing one.
     */
    public function store(UploadedFile $file, string $ownerType, int $ownerId): Image
    {
        // Delete any existing image first — one part = one primary image
        $this->deleteByOwner($ownerType, $ownerId);

        $path = $file->store("images/{$ownerType}/{$ownerId}", 'public');

        return Image::create([
            'owner_type' => $ownerType,
            'owner_id' => $ownerId,
            'path' => $path,
            'is_primary' => true,
        ]);
    }

    /**
     * Delete the primary image for an owner from both storage and database.
     */
    public function deleteByOwner(string $ownerType, int $ownerId): void
    {
        $existing = Image::where('owner_type', $ownerType)
            ->where('owner_id', $ownerId)
            ->first();

        if ($existing) {
            Storage::disk('public')->delete($existing->path);
            $existing->delete();
        }
    }
}

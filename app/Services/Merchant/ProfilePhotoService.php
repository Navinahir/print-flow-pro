<?php

declare(strict_types=1);

namespace App\Services\Merchant;

use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class ProfilePhotoService
{
    public function store(User $user, UploadedFile $photo): string
    {
        $this->deleteExisting($user);

        $directory = 'profile-photos';
        $filename = sprintf('%s-%s.jpg', $user->id, Str::uuid());
        $path = $photo->storeAs($directory, $filename, 'public');

        $user->forceFill(['profile_photo_path' => $path])->save();

        return $path;
    }

    public function delete(User $user): void
    {
        $this->deleteExisting($user);
        $user->forceFill(['profile_photo_path' => null])->save();
    }

    private function deleteExisting(User $user): void
    {
        $existing = $user->getAttributes()['profile_photo_path'] ?? null;

        if ($existing !== null && $existing !== '' && Storage::disk('public')->exists($existing)) {
            Storage::disk('public')->delete($existing);
        }
    }
}

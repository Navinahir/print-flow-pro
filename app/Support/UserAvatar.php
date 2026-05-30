<?php

declare(strict_types=1);

namespace App\Support;

use App\Models\User;
use Illuminate\Support\Facades\Storage;

final class UserAvatar
{
    /**
     * Build two-character initials from a display name.
     *
     * Multiple words: first character of the first two words (e.g. "Demo Merchant" → "DM").
     * Single word: first two characters (e.g. "Alice" → "AL").
     */
    public static function initials(string $name): string
    {
        $normalized = trim(preg_replace('/\s+/u', ' ', $name) ?? '');

        if ($normalized === '') {
            return '?';
        }

        $words = preg_split('/\s+/u', $normalized) ?: [];

        if (count($words) >= 2) {
            $first = mb_substr($words[0], 0, 1);
            $second = mb_substr($words[1], 0, 1);

            return mb_strtoupper($first.$second);
        }

        return mb_strtoupper(mb_substr($words[0], 0, 2));
    }

    public static function photoPath(?User $user): ?string
    {
        if ($user === null) {
            return null;
        }

        $path = $user->getAttributes()['profile_photo_path'] ?? null;

        if (! is_string($path) || $path === '') {
            return null;
        }

        return $path;
    }

    public static function hasPhoto(?User $user): bool
    {
        return self::photoUrl($user) !== null;
    }

    public static function photoUrl(?User $user): ?string
    {
        $path = self::photoPath($user);

        if ($path === null) {
            return null;
        }

        $disk = Storage::disk('public');

        if (! $disk->exists($path)) {
            return null;
        }

        $version = (string) $disk->lastModified($path);

        return route('profile.photo.show', ['user' => $user->id], absolute: false).'?v='.$version;
    }
}

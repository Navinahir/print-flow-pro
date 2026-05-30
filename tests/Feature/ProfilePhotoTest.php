<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ProfilePhotoTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_upload_profile_photo(): void
    {
        Storage::fake('public');

        $user = User::factory()->create();

        $response = $this
            ->actingAs($user)
            ->post('/profile/photo', [
                'photo' => UploadedFile::fake()->image('avatar.jpg', 600, 600),
            ]);

        $response
            ->assertSessionHasNoErrors()
            ->assertRedirect(route('profile.edit'));

        $user->refresh();

        $path = $user->getAttributes()['profile_photo_path'] ?? null;

        $this->assertNotNull($path);
        Storage::disk('public')->assertExists($path);
    }

    public function test_user_can_remove_profile_photo(): void
    {
        Storage::fake('public');

        $user = User::factory()->create();
        $path = UploadedFile::fake()->image('avatar.jpg')->store('profile-photos', 'public');
        $user->forceFill(['profile_photo_path' => $path])->save();

        $response = $this
            ->actingAs($user)
            ->delete('/profile/photo');

        $response
            ->assertSessionHasNoErrors()
            ->assertRedirect(route('profile.edit'));

        $user->refresh();

        $this->assertNull($user->getAttributes()['profile_photo_path'] ?? null);
        Storage::disk('public')->assertMissing($path);
    }

    public function test_authenticated_user_can_view_own_profile_photo(): void
    {
        Storage::fake('public');

        $user = User::factory()->create();
        $path = UploadedFile::fake()->image('avatar.jpg')->store('profile-photos', 'public');
        $user->forceFill(['profile_photo_path' => $path])->save();

        $response = $this
            ->actingAs($user)
            ->get(route('profile.photo.show', ['user' => $user->id]));

        $response->assertOk();
    }

    public function test_user_cannot_view_another_users_profile_photo(): void
    {
        Storage::fake('public');

        $owner = User::factory()->create();
        $other = User::factory()->create();
        $path = UploadedFile::fake()->image('avatar.jpg')->store('profile-photos', 'public');
        $owner->forceFill(['profile_photo_path' => $path])->save();

        $response = $this
            ->actingAs($other)
            ->get(route('profile.photo.show', ['user' => $owner->id]));

        $response->assertForbidden();
    }

    public function test_profile_page_renders_photo_upload_section(): void
    {
        $user = User::factory()->create();

        $response = $this
            ->actingAs($user)
            ->get('/profile');

        $response
            ->assertOk()
            ->assertSee(__('merchant.profile.photo.title'), false)
            ->assertSee('merchant-profile-photo-root', false);
    }
}

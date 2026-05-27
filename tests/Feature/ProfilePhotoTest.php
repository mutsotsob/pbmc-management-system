<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ProfilePhotoTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_upload_profile_picture(): void
    {
        $user = User::factory()->create([
            'user_status' => true,
        ]);

        $response = $this->actingAs($user)
            ->patch(route('profile.update'), [
                'profile_photo' => UploadedFile::fake()->image('profile.jpg', 300, 300),
            ]);

        $response->assertRedirect(route('profile.edit'));
        $response->assertSessionHas('status', 'profile-photo-updated');

        $user->refresh();

        $this->assertNotNull($user->profile_photo_path);
        Storage::disk('public')->assertExists($user->profile_photo_path);
        Storage::disk('public')->delete($user->profile_photo_path);
    }

    public function test_profile_picture_must_be_an_image(): void
    {
        Storage::fake('public');

        $user = User::factory()->create([
            'user_status' => true,
        ]);

        $response = $this->actingAs($user)
            ->from(route('profile.edit'))
            ->patch(route('profile.update'), [
                'profile_photo' => UploadedFile::fake()->create('profile.txt', 10, 'text/plain'),
            ]);

        $response->assertRedirect(route('profile.edit'));
        $response->assertSessionHasErrors('profile_photo');

        $this->assertNull($user->refresh()->profile_photo_path);
    }
}

<?php

use App\Models\User;
use App\Services\CloudinaryMediaService;
use Illuminate\Http\UploadedFile;
use Mockery\MockInterface;

test('users can upload a profile photo', function () {
    $user = User::factory()->create([
        'profile_photo_url' => 'https://example.com/old-photo.jpg',
        'profile_photo_public_id' => 'profile_photos/1/profile-photo-old',
    ]);

    $this->mock(CloudinaryMediaService::class, function (MockInterface $mock): void {
        $mock->shouldReceive('uploadProfilePhoto')
            ->once()
            ->andReturn([
                'public_id' => 'profile_photos/1/profile-photo-new',
                'url' => 'https://res.cloudinary.com/demo/image/upload/profile-photo-new.jpg',
            ]);

        $mock->shouldReceive('destroy')
            ->once()
            ->with('profile_photos/1/profile-photo-old');
    });

    $response = $this
        ->actingAs($user)
        ->post(route('profile-photo.update'), [
            'photo' => UploadedFile::fake()->image('avatar.png'),
        ]);

    $response
        ->assertSessionHasNoErrors()
        ->assertRedirect(route('profile.edit'));

    expect($user->refresh()->profile_photo_public_id)->toBe('profile_photos/1/profile-photo-new');
    expect($user->profile_photo_url)->toBe('https://res.cloudinary.com/demo/image/upload/profile-photo-new.jpg');
});

test('users can delete their profile photo', function () {
    $user = User::factory()->create([
        'profile_photo_url' => 'https://example.com/photo.jpg',
        'profile_photo_public_id' => 'profile_photos/1/profile-photo-old',
    ]);

    $this->mock(CloudinaryMediaService::class, function (MockInterface $mock): void {
        $mock->shouldReceive('destroy')
            ->once()
            ->with('profile_photos/1/profile-photo-old');
    });

    $response = $this
        ->actingAs($user)
        ->delete(route('profile-photo.destroy'));

    $response
        ->assertSessionHasNoErrors()
        ->assertRedirect(route('profile.edit'));

    expect($user->refresh()->profile_photo_public_id)->toBeNull();
    expect($user->profile_photo_url)->toBeNull();
});

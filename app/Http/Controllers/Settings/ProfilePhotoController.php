<?php

namespace App\Http\Controllers\Settings;

use App\Http\Controllers\Controller;
use App\Http\Requests\Settings\ProfilePhotoUpdateRequest;
use App\Services\CloudinaryMediaService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class ProfilePhotoController extends Controller
{
    /**
     * Create a new controller instance.
     */
    public function __construct(
        public CloudinaryMediaService $cloudinaryMediaService,
    ) {}

    /**
     * Update the user's profile photo.
     */
    public function update(ProfilePhotoUpdateRequest $request): RedirectResponse
    {
        $user = $request->user();
        $previousPhotoPublicId = $user->profile_photo_public_id;
        $photo = $this->cloudinaryMediaService->uploadProfilePhoto($request->file('photo'), $user);

        try {
            $user->forceFill([
                'profile_photo_url' => $photo['url'],
                'profile_photo_public_id' => $photo['public_id'],
            ])->save();
        } catch (\Throwable $exception) {
            $this->cloudinaryMediaService->destroy($photo['public_id']);

            throw $exception;
        }

        $this->cloudinaryMediaService->destroy($previousPhotoPublicId);

        return to_route('profile.edit')->with('success', 'Profile photo updated.');
    }

    /**
     * Remove the user's profile photo.
     */
    public function destroy(Request $request): RedirectResponse
    {
        $user = $request->user();
        $previousPhotoPublicId = $user->profile_photo_public_id;

        $user->forceFill([
            'profile_photo_url' => null,
            'profile_photo_public_id' => null,
        ])->save();

        $this->cloudinaryMediaService->destroy($previousPhotoPublicId);

        return to_route('profile.edit')->with('success', 'Profile photo removed.');
    }
}

<?php

namespace App\Services;

use App\Models\Project;
use App\Models\User;
use Cloudinary\Cloudinary;
use Cloudinary\Transformation\Fill;
use Cloudinary\Transformation\Format;
use Cloudinary\Transformation\Gravity;
use Cloudinary\Transformation\Quality;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Str;

class CloudinaryMediaService
{
    /**
     * Upload and optimize a profile photo.
     *
     * @return array{public_id: string, url: string}
     */
    public function uploadProfilePhoto(UploadedFile $file, User $user): array
    {
        $publicId = sprintf('profile_photos/%d/profile-photo-%s', $user->id, $this->uniqueMediaId());

        $upload = $this->cloudinary()->uploadApi()->upload($file->getRealPath(), [
            'public_id' => $publicId,
            'resource_type' => 'image',
            'overwrite' => true,
            'invalidate' => true,
            'transformation' => [
                'width' => 720,
                'height' => 720,
                'crop' => 'fill',
                'gravity' => 'auto',
                'fetch_format' => 'auto',
                'quality' => 'auto:good',
            ],
        ]);

        return [
            'public_id' => $upload['public_id'],
            'url' => $upload['secure_url'],
        ];
    }

    /**
     * Upload a project image and its thumbnail.
     *
     * @return array{image_public_id: string, image_url: string, thumbnail_public_id: string, thumbnail_url: string}
     */
    public function uploadProjectAssets(UploadedFile $file, User $user): array
    {
        $projectId = $this->uniqueMediaId();
        $imagePublicId = sprintf('project_images/%d/project-%s', $user->id, $projectId);
        $thumbnailPublicId = sprintf('project_thumbnails/%d/project-%s', $user->id, $projectId);

        $imageUpload = $this->cloudinary()->uploadApi()->upload($file->getRealPath(), [
            'public_id' => $imagePublicId,
            'resource_type' => 'image',
            'overwrite' => true,
            'invalidate' => true,
        ]);

        $thumbnailUpload = $this->cloudinary()->uploadApi()->upload($file->getRealPath(), [
            'public_id' => $thumbnailPublicId,
            'resource_type' => 'image',
            'overwrite' => true,
            'invalidate' => true,
            'transformation' => [
                'width' => 960,
                'height' => 600,
                'crop' => 'fill',
                'gravity' => 'auto',
                'fetch_format' => 'auto',
                'quality' => 'auto:good',
            ],
        ]);

        return [
            'image_public_id' => $imageUpload['public_id'],
            'image_url' => $imageUpload['secure_url'],
            'thumbnail_public_id' => $thumbnailUpload['public_id'],
            'thumbnail_url' => $thumbnailUpload['secure_url'],
        ];
    }

    /**
     * Build responsive card URLs for a project screenshot.
     *
     * @return array{mobile: string, tablet: string, desktop: string}
     */
    public function projectCardSources(Project $project): array
    {
        return [
            'mobile' => $this->responsiveProjectUrl($project->image_public_id, 720, 450),
            'tablet' => $this->responsiveProjectUrl($project->image_public_id, 960, 600),
            'desktop' => $this->responsiveProjectUrl($project->image_public_id, 1280, 800),
        ];
    }

    /**
     * Build a transformed project image URL.
     */
    public function responsiveProjectUrl(string $publicId, int $width, int $height): string
    {
        return (string) $this->cloudinary()
            ->image($publicId)
            ->resize(Fill::fill($width, $height, Gravity::auto()))
            ->format(Format::auto())
            ->quality(Quality::autoGood());
    }

    /**
     * Delete an uploaded Cloudinary asset if it exists.
     */
    public function destroy(?string $publicId): void
    {
        if ($publicId === null || $publicId === '') {
            return;
        }

        $this->cloudinary()->uploadApi()->destroy($publicId, [
            'resource_type' => 'image',
            'invalidate' => true,
        ]);
    }

    /**
     * Resolve the Cloudinary client.
     */
    protected function cloudinary(): Cloudinary
    {
        return new Cloudinary(config('cloudinary.cloud_url') ?: 'cloudinary://key:secret@test');
    }

    /**
     * Generate a lowercase ULID string for Cloudinary public IDs.
     */
    protected function uniqueMediaId(): string
    {
        return strtolower((string) Str::ulid());
    }
}

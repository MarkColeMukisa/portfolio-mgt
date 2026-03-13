<?php

use App\Models\Project;
use App\Models\Tag;
use App\Models\User;
use App\Services\CloudinaryMediaService;
use Illuminate\Http\UploadedFile;
use Inertia\Testing\AssertableInertia as Assert;
use Mockery\MockInterface;

test('guests can view the public portfolio grid', function () {
    $project = Project::factory()->for(User::factory()->create())->create([
        'title' => 'Launch metrics board',
    ]);

    $tag = Tag::factory()->create(['name' => 'analytics', 'slug' => 'analytics']);
    $project->tags()->attach($tag);

    $response = $this->get(route('home'));

    $response->assertOk()->assertInertia(fn (Assert $page) => $page
        ->component('Welcome')
        ->where('projects.data.0.title', 'Launch metrics board')
        ->where('featuredTags.0.name', 'analytics'));
});

test('verified users can create portfolio projects', function () {
    $user = User::factory()->create();

    $this->mock(CloudinaryMediaService::class, function (MockInterface $mock): void {
        $mock->shouldReceive('uploadProjectAssets')
            ->once()
            ->andReturn([
                'image_public_id' => 'project_images/1/project-abc',
                'image_url' => 'https://res.cloudinary.com/demo/image/upload/project.jpg',
                'thumbnail_public_id' => 'project_thumbnails/1/project-abc',
                'thumbnail_url' => 'https://res.cloudinary.com/demo/image/upload/project-thumb.jpg',
            ]);
    });

    $response = $this
        ->actingAs($user)
        ->post(route('projects.store'), [
            'title' => 'Realtime board',
            'description' => 'A screenshot showing system health and live alerts.',
            'tags' => 'Vue, SaaS, vue',
            'image' => UploadedFile::fake()->image('dashboard.png'),
        ]);

    $response
        ->assertSessionHasNoErrors()
        ->assertRedirect(route('dashboard'));

    $project = Project::query()->with('tags')->sole();

    expect($project->title)->toBe('Realtime board');
    expect($project->tags->pluck('name')->all())->toBe(['vue', 'saas']);
});

test('project owners can delete their projects', function () {
    $owner = User::factory()->create();
    $project = Project::factory()->for($owner)->create();

    $this->mock(CloudinaryMediaService::class, function (MockInterface $mock) use ($project): void {
        $mock->shouldReceive('destroy')->once()->with($project->image_public_id);
        $mock->shouldReceive('destroy')->once()->with($project->thumbnail_public_id);
    });

    $response = $this
        ->actingAs($owner)
        ->delete(route('projects.destroy', $project));

    $response
        ->assertSessionHasNoErrors()
        ->assertRedirect(route('dashboard'));

    expect($project->fresh())->toBeNull();
});

test('users cannot delete projects they do not own', function () {
    $owner = User::factory()->create();
    $otherUser = User::factory()->create();
    $project = Project::factory()->for($owner)->create();

    $this->mock(CloudinaryMediaService::class, function (MockInterface $mock): void {
        $mock->shouldNotReceive('destroy');
    });

    $response = $this
        ->actingAs($otherUser)
        ->delete(route('projects.destroy', $project));

    $response->assertForbidden();

    expect($project->fresh())->not->toBeNull();
});

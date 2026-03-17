<?php

use App\Models\Project;
use App\Models\Tag;
use App\Models\User;
use App\Services\CloudinaryMediaService;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Carbon;
use Inertia\Testing\AssertableInertia as Assert;
use Mockery\MockInterface;

test('guests can view the public portfolio grid', function () {
    $createdAt = Carbon::parse('2026-03-16 08:00:00');

    $project = Project::factory()->for(User::factory()->create())->create([
        'title' => 'Launch metrics board',
        'created_at' => $createdAt,
        'updated_at' => $createdAt,
    ]);

    $tag = Tag::factory()->create(['name' => 'analytics', 'slug' => 'analytics']);
    $project->tags()->attach($tag);

    $response = $this->get(route('home'));

    $response->assertOk()->assertInertia(fn (Assert $page) => $page
        ->component('Welcome')
        ->where('projects.data.0.title', 'Launch metrics board')
        ->where('projects.data.0.created_at', $createdAt->toIso8601String())
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

test('project owners can update their projects without replacing the screenshot', function () {
    $owner = User::factory()->create();
    $project = Project::factory()->for($owner)->create([
        'title' => 'Initial title',
        'description' => 'Initial description',
    ]);
    $existingTag = Tag::factory()->create(['name' => 'legacy', 'slug' => 'legacy']);
    $project->tags()->attach($existingTag);

    $this->mock(CloudinaryMediaService::class, function (MockInterface $mock): void {
        $mock->shouldNotReceive('uploadProjectAssets');
        $mock->shouldNotReceive('destroy');
    });

    $response = $this
        ->actingAs($owner)
        ->patch(route('projects.update', $project), [
            'title' => 'Updated title',
            'description' => 'Updated description',
            'tags' => 'Laravel, Vue, laravel',
        ]);

    $response
        ->assertSessionHasNoErrors()
        ->assertRedirect(route('dashboard'));

    $project->refresh()->load('tags');

    expect($project->title)->toBe('Updated title');
    expect($project->description)->toBe('Updated description');
    expect($project->tags->pluck('name')->all())->toBe(['laravel', 'vue']);
});

test('project owners can replace their project screenshot while editing', function () {
    $owner = User::factory()->create();
    $project = Project::factory()->for($owner)->create([
        'image_public_id' => 'project_images/1/project-old',
        'image_url' => 'https://res.cloudinary.com/demo/image/upload/project-old.jpg',
        'thumbnail_public_id' => 'project_thumbnails/1/project-old',
        'thumbnail_url' => 'https://res.cloudinary.com/demo/image/upload/project-old-thumb.jpg',
    ]);

    $this->mock(CloudinaryMediaService::class, function (MockInterface $mock) use ($project): void {
        $mock->shouldReceive('uploadProjectAssets')
            ->once()
            ->andReturn([
                'image_public_id' => 'project_images/1/project-new',
                'image_url' => 'https://res.cloudinary.com/demo/image/upload/project-new.jpg',
                'thumbnail_public_id' => 'project_thumbnails/1/project-new',
                'thumbnail_url' => 'https://res.cloudinary.com/demo/image/upload/project-new-thumb.jpg',
            ]);
        $mock->shouldReceive('destroy')->once()->with($project->image_public_id);
        $mock->shouldReceive('destroy')->once()->with($project->thumbnail_public_id);
    });

    $response = $this
        ->actingAs($owner)
        ->patch(route('projects.update', $project), [
            'title' => 'Refreshed card',
            'description' => 'Updated screenshot and polished copy.',
            'tags' => ['design', 'portfolio'],
            'image' => UploadedFile::fake()->image('refreshed-card.png'),
        ]);

    $response
        ->assertSessionHasNoErrors()
        ->assertRedirect(route('dashboard'));

    $project->refresh()->load('tags');

    expect($project->image_public_id)->toBe('project_images/1/project-new');
    expect($project->thumbnail_public_id)->toBe('project_thumbnails/1/project-new');
    expect($project->tags->pluck('name')->all())->toBe(['design', 'portfolio']);
});

test('users cannot delete projects they do not own', function () {
    $owner = User::factory()->create();
    $otherUser = User::factory()->create();
    $project = Project::factory()->for($owner)->create();

    $this->mock(CloudinaryMediaService::class, function (MockInterface $mock): void {
        $mock->shouldNotReceive('destroy');
    });

    $this->withoutExceptionHandling();

    try {
        $this
            ->actingAs($otherUser)
            ->delete(route('projects.destroy', $project));
    } catch (AuthorizationException) {
        expect($project->fresh())->not->toBeNull();

        return;
    }

    $this->fail('Expected an authorization exception.');
});

test('users cannot update projects they do not own', function () {
    $owner = User::factory()->create();
    $otherUser = User::factory()->create();
    $project = Project::factory()->for($owner)->create();

    $this->mock(CloudinaryMediaService::class, function (MockInterface $mock): void {
        $mock->shouldNotReceive('uploadProjectAssets');
        $mock->shouldNotReceive('destroy');
    });

    $this->withoutExceptionHandling();

    try {
        $this
            ->actingAs($otherUser)
            ->patch(route('projects.update', $project), [
                'title' => 'Nope',
                'description' => 'Still not allowed.',
                'tags' => ['blocked', 'change'],
            ]);
    } catch (AuthorizationException) {
        expect($project->fresh()->title)->not->toBe('Nope');

        return;
    }

    $this->fail('Expected an authorization exception.');
});

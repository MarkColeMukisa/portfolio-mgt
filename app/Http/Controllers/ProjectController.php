<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreProjectRequest;
use App\Models\Project;
use App\Models\Tag;
use App\Models\User;
use App\Services\CloudinaryMediaService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Inertia\Inertia;
use Inertia\Response;
use Laravel\Fortify\Features;

class ProjectController extends Controller
{
    /**
     * Create a new controller instance.
     */
    public function __construct(
        public CloudinaryMediaService $cloudinaryMediaService,
    ) {}

    /**
     * Display the public portfolio grid.
     */
    public function index(Request $request): Response
    {
        return Inertia::render('Welcome', [
            'canRegister' => Features::enabled(Features::registration()),
            'heroStats' => [
                'projects' => Project::query()->count(),
                'builders' => Project::query()->distinct()->count('user_id'),
                'tags' => Tag::query()->count(),
            ],
            'featuredTags' => Tag::query()
                ->withCount('projects')
                ->orderByDesc('projects_count')
                ->limit(8)
                ->get()
                ->map(fn (Tag $tag): array => [
                    'id' => $tag->id,
                    'name' => $tag->name,
                    'project_count' => $tag->projects_count,
                ]),
            'projects' => Inertia::scroll(fn () => Project::query()
                ->with(['tags:id,name', 'user:id,name,profile_photo_url'])
                ->latest()
                ->paginate(8)
                ->through(fn (Project $project): array => $this->presentProject($project, $request->user()))),
        ]);
    }

    /**
     * Display the authenticated creator dashboard.
     */
    public function dashboard(Request $request): Response
    {
        $projects = $request->user()
            ->projects()
            ->with(['tags:id,name', 'user:id,name,profile_photo_url'])
            ->latest()
            ->get()
            ->map(fn (Project $project): array => $this->presentProject($project, $request->user()));

        return Inertia::render('Dashboard', [
            'stats' => [
                'projects' => $projects->count(),
                'tags' => $request->user()->projects()->withCount('tags')->get()->sum('tags_count'),
            ],
            'projects' => $projects,
        ]);
    }

    /**
     * Store a new project.
     */
    public function store(StoreProjectRequest $request): RedirectResponse
    {
        $assets = $this->cloudinaryMediaService->uploadProjectAssets($request->file('image'), $request->user());

        try {
            DB::transaction(function () use ($request, $assets): void {
                $project = Project::query()->create([
                    'user_id' => $request->user()->id,
                    'title' => $request->string('title')->trim()->value(),
                    'description' => $request->string('description')->trim()->value(),
                    ...$assets,
                ]);

                $project->tags()->sync($this->resolveTagIds($request->validated('tags')));
            });
        } catch (\Throwable $exception) {
            $this->cloudinaryMediaService->destroy($assets['image_public_id']);
            $this->cloudinaryMediaService->destroy($assets['thumbnail_public_id']);

            throw $exception;
        }

        return to_route('dashboard')->with('success', 'Project added to your portfolio.');
    }

    /**
     * Delete the selected project.
     */
    public function destroy(Project $project): RedirectResponse
    {
        $this->authorize('delete', $project);

        DB::transaction(function () use ($project): void {
            $this->cloudinaryMediaService->destroy($project->image_public_id);
            $this->cloudinaryMediaService->destroy($project->thumbnail_public_id);

            $project->tags()->detach();
            $project->delete();
        });

        return to_route('dashboard')->with('success', 'Project deleted.');
    }

    /**
     * Resolve tag IDs for the provided values.
     *
     * @param  array<int, string>  $tags
     * @return array<int, int>
     */
    protected function resolveTagIds(array $tags): array
    {
        return collect($tags)
            ->map(function (string $tag): int {
                $slug = Str::slug($tag);

                return Tag::query()->firstOrCreate(
                    ['slug' => $slug],
                    ['name' => $tag],
                )->id;
            })
            ->all();
    }

    /**
     * Transform a project for Inertia props.
     *
     * @return array<string, mixed>
     */
    protected function presentProject(Project $project, ?User $viewer): array
    {
        return [
            'id' => $project->id,
            'title' => $project->title,
            'created_at' => $project->created_at?->toIso8601String(),
            'description' => $project->description,
            'description_preview' => Str::limit($project->description, 110),
            'image_url' => $project->image_url,
            'thumbnail_url' => $project->thumbnail_url,
            'responsive_images' => $this->cloudinaryMediaService->projectCardSources($project),
            'tags' => $project->tags
                ->map(fn (Tag $tag): array => [
                    'id' => $tag->id,
                    'name' => $tag->name,
                ])
                ->all(),
            'author' => [
                'id' => $project->user->id,
                'name' => $project->user->name,
                'avatar' => $project->user->profile_photo_url,
            ],
            'can' => [
                'delete' => $viewer?->can('delete', $project) ?? false,
            ],
        ];
    }
}

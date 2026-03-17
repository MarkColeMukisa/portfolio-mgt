# X Feed Integration for This App

## Goal

Add a social section to the existing home page instead of building a separate feed page.

This repository already uses:

- Laravel 12
- Inertia.js v2 with Vue 3
- Wayfinder
- Pest
- a public portfolio landing page rendered from `ProjectController@index`

Because of that, the integration should follow this shape:

`X API -> service -> cache -> database -> Inertia props -> Vue section on the existing index page`

No Blade views.
No standalone `/x-feed` route.
No refresh on every public request.

## What We Are Building

We will:

- fetch recent posts from one X account
- cache the upstream response briefly
- store normalized posts in the local database
- render those stored posts as a section on the current home page
- keep the section visually consistent with the current `Welcome.vue` design

This keeps the app in control of presentation and avoids embedding external widgets.

## Why This Fits the Current Codebase

The public page already comes from `ProjectController@index` and renders the Inertia page `Welcome`.

That means the X section should be added by:

1. extending the existing controller response
2. passing a new prop such as `socialPosts`
3. rendering a new section inside `resources/js/pages/Welcome.vue`

Do not introduce:

- a Blade template
- a separate feed controller just to render a view
- a public route whose only job is showing the feed

## Required Credentials

Before coding, confirm you have:

- an X developer app
- a bearer token with access to the endpoint you plan to use
- the target account user ID

X access rules can change by endpoint and plan, so verify your account can call the intended endpoint before building around it.

## Environment and Config

Add these to `.env`:

```env
X_BEARER_TOKEN=your_x_bearer_token_here
X_USER_ID=your_target_user_id_here
X_ENABLED=true
```

Add this to `config/services.php`:

```php
'x' => [
    'enabled' => (bool) env('X_ENABLED', false),
    'bearer_token' => env('X_BEARER_TOKEN'),
    'user_id' => env('X_USER_ID'),
],
```

The `enabled` flag gives the UI and sync layer a clean off switch.

## Data Model

Create a local `x_posts` table.

Suggested columns:

- `id`
- `x_id` unique
- `text` nullable text
- `author_id` nullable string
- `posted_at` nullable timestamp
- `permalink` nullable string
- `like_count` default 0
- `reply_count` default 0
- `repost_count` default 0
- `quote_count` default 0
- `raw_payload` nullable json
- timestamps

Keep the first version small. Media can come later once the base sync works.

## Model

Create `App\Models\XPost`.

Use:

- explicit `$fillable`
- casts for `posted_at` and `raw_payload`
- a small presenter method only if it improves reuse

For consistency with the rest of the app, prefer a `casts(): array` method if you see that convention used in nearby models during implementation.

## Service Layer

Create a dedicated service such as `App\Services\XPostService`.

Responsibilities:

- call the X API through Laravel's HTTP client
- cache the raw API payload for a short time
- normalize and upsert posts into `x_posts`
- expose a read method for already stored posts

Recommended public methods:

- `syncRecentPosts(?string $userId = null, int $limit = 6): void`
- `getStoredPosts(int $limit = 6): Collection`
- `clearCache(?string $userId = null, int $limit = 6): void`

Implementation notes:

- fail fast if the feature is disabled or credentials are missing
- use `Http::withToken(...)->acceptJson()->timeout(20)`
- cap `max_results` defensively
- use `updateOrCreate()` keyed by `x_id`
- store a local `permalink` like `https://x.com/i/web/status/{id}` so the frontend does not build URLs manually

## Rendering Strategy

Do not fetch from X inside the home page request.

The public `index()` action should read from the database only, exactly like a normal content section. That keeps the page stable, fast, and independent of X outages or rate limits.

The existing `ProjectController@index` should be extended with another prop, for example:

```php
'socialPosts' => XPost::query()
    ->latest('posted_at')
    ->limit(6)
    ->get()
    ->map(fn (XPost $post): array => [
        'id' => $post->id,
        'x_id' => $post->x_id,
        'text' => $post->text,
        'posted_at' => $post->posted_at?->toIso8601String(),
        'permalink' => $post->permalink,
        'metrics' => [
            'likes' => $post->like_count,
            'replies' => $post->reply_count,
            'reposts' => $post->repost_count,
            'quotes' => $post->quote_count,
        ],
    ]),
```

You can keep that mapping in the controller at first. If it grows, move it to a presenter or resource-like transformer.

## Frontend Integration

The section belongs in `resources/js/pages/Welcome.vue`.

Match the current visual language:

- rounded white panels
- mint and green borders
- soft gradients
- concise section headings
- the same spacing rhythm as the portfolio sections already on the page

Recommended section shape:

- small eyebrow label
- heading such as "Latest from X"
- short supporting text
- a responsive grid or vertical stack of post cards
- a "View on X" link per card

Suggested prop type addition in `resources/js/types/portfolio.ts` or another nearby type file:

```ts
export type SocialPost = {
    id: number;
    x_id: string;
    text: string | null;
    posted_at: string | null;
    permalink: string | null;
    metrics: {
        likes: number;
        replies: number;
        reposts: number;
        quotes: number;
    };
};
```

Then pass `socialPosts: SocialPost[]` into `Welcome.vue`.

## Routes

No new public page route is needed.

Keep the section on the existing home route:

- `GET /` -> `ProjectController@index`

If you want a manual refresh action for admins later, add a protected route and use Wayfinder on the frontend where applicable. That route should not be required for phase 1.

## Sync Strategy

The right default for this app is scheduled sync.

Create an Artisan command such as:

```bash
php artisan make:command SyncXPosts --no-interaction
```

Responsibilities:

- clear the cache for the current user and limit if needed
- fetch recent posts
- upsert into the database
- output a short success or failure message

Then register scheduling using the Laravel 12 approach, not the old kernel-based approach.

In this project, that means defining the schedule in the current console configuration flow, not in a removed `app/Console/Kernel.php`.

Recommended cadence:

- every 15 minutes for a public marketing section

That is enough for freshness without making the homepage depend on live API calls.

## Manual Refresh

Optional for phase 2:

- add a small authenticated refresh action in the dashboard
- post to a protected route
- dispatch a sync job or invoke the sync service

If you expose this in the frontend, use Wayfinder-generated route helpers instead of hardcoded URLs.

## Error Handling

Treat the X section as optional content.

If sync fails:

- the homepage should still render normally
- the stored posts remain visible
- logs should capture the failure

If there are no stored posts:

- render an empty state or omit the section entirely

Do not let missing X data degrade the rest of the portfolio page.

## Testing Approach

This repo uses Pest, so the implementation should be tested that way.

Minimum useful coverage:

1. service sync test using `Http::fake()` and asserting rows are written to `x_posts`
2. feature test asserting the home page Inertia response includes the expected `socialPosts`
3. failure-path test proving API errors do not break homepage rendering if stored data already exists

Useful Laravel testing helpers:

- `Http::fake()`
- `Http::preventStrayRequests()`
- Inertia response assertions

Do not rely on live X requests in tests.

## Suggested Execution Order

1. Add config values in `.env.example` and `config/services.php`
2. Create migration and model for `x_posts`
3. Build `XPostService`
4. Add `SyncXPosts` command
5. Extend `ProjectController@index` to include stored social post props
6. Add the new section to `Welcome.vue`
7. Add TypeScript types
8. Write Pest tests for sync and rendering
9. Schedule the command

## Good Phase 1 Scope

Phase 1 should only cover:

- one configured X account
- one sync command
- one stored post table
- one homepage section
- one simple card design matching the current landing page

Do not add in phase 1:

- user-connected accounts
- OAuth login with X
- live client polling
- media gallery expansions
- a second standalone social page

## Suggested Enhancements Later

Once the base version works, the clean next steps are:

- support media expansions and store image URLs
- truncate long posts and add a "View on X" action
- expose a dashboard refresh control
- queue the sync work if API calls or media handling grow
- add a small cache freshness label in the UI

## Final Recommendation

For this repository, the correct implementation is not "build an X feed page."

It is:

- sync X posts into local storage
- surface them as a curated section on the existing Inertia home page
- preserve the current portfolio design language
- keep the homepage database-backed and fast

That is the version most aligned with the current architecture, packages, and UI direction.

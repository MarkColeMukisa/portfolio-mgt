# Portfolio Mgt

Portfolio Mgt is a Laravel 12 + Inertia + Vue application for showcasing project screenshots in a public portfolio gallery.

Guests can browse published work on the homepage. Authenticated and verified users can manage their own portfolio from the dashboard by uploading Cloudinary-hosted screenshots, adding tags and descriptions, and maintaining a profile photo.

## Features

- Public portfolio homepage with a responsive screenshot grid
- Creator dashboard for uploading and deleting projects
- Tag support with normalized `projects`, `tags`, and `project_tag` tables
- Cloudinary-managed project images and generated thumbnails
- Profile photo upload and removal
- Email verification required before publishing projects
- SMTP-based email delivery
- Load More pagination on the public gallery

## Stack

- PHP 8.5
- Laravel 12
- Laravel Fortify
- Inertia.js v2
- Vue 3
- Tailwind CSS v4
- SQLite by default
- Cloudinary for media storage and delivery

## Requirements

- PHP 8.5+
- Composer
- Node.js 20+
- pnpm or npm
- SQLite or another supported database
- A Cloudinary account with a valid `CLOUDINARY_URL`
- SMTP credentials for mail delivery

## Local Setup

1. Install PHP dependencies:

```bash
composer install
```

2. Install frontend dependencies:

```bash
pnpm install
```

3. Create your environment file if it does not already exist:

```bash
copy .env.example .env
```

4. Configure the important environment values in `.env`:

```env
APP_URL=http://portfolio-mgt.test

DB_CONNECTION=sqlite

MAIL_MAILER=smtp
MAIL_HOST=your-smtp-host
MAIL_PORT=587
MAIL_USERNAME=your-smtp-username
MAIL_PASSWORD=your-smtp-password
MAIL_SCHEME=tls
MAIL_FROM_ADDRESS=your-email@example.com
MAIL_FROM_NAME="${APP_NAME}"

CLOUDINARY_URL=cloudinary://API_KEY:API_SECRET@CLOUD_NAME
```

5. Create the SQLite database file if you are using SQLite:

```bash
type nul > database\\database.sqlite
```

6. Generate the app key and run migrations:

```bash
php artisan key:generate
php artisan migrate
```

7. Generate Wayfinder route types:

```bash
php artisan wayfinder:generate --with-form
```

## Running The App

If you are using Herd, serve the site through Herd and run the frontend and queue processes separately:

```bash
pnpm dev
php artisan queue:listen --tries=1
```

If you prefer the built-in Laravel workflow instead:

```bash
composer run dev
```

## Default Workflow

1. Register a user account.
2. Verify the email address from the verification link.
3. Open the dashboard.
4. Upload a project screenshot with a title, description, and comma-separated tags.
5. View the published project on the public homepage.

## Testing

Run the test suite:

```bash
php artisan test
```

Run frontend type checks:

```bash
pnpm run types:check
```

Format PHP files:

```bash
vendor/bin/pint --dirty --format agent
```

## Notes

- Project publishing requires a verified email address.
- Project images are not stored locally. They are uploaded to Cloudinary and delivered from Cloudinary URLs.
- The project currently uses SMTP for email delivery.
- When using Herd locally, `APP_URL` should match the Herd domain you open in the browser.

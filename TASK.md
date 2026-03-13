# Portfolio Project – Implementation Task

## Project Goal

Build a **portfolio-style Laravel Vue application** where users can showcase their projects through screenshots/images.

The home page should display a **responsive grid of project screenshots** uploaded by users detailing which user.

Images must be **managed entirely through Cloudinary**, meaning:

* Images are uploaded to Cloudinary
* Cloudinary returns URLs
* The application stores and uses those URLs
* Images are delivered directly from Cloudinary (CDN-ready)

Cloudinary should also automatically **generate thumbnails and optimized versions** of uploaded images.

---

# IMPORTANT EXECUTION RULES

1. **Do not assume missing information.**
2. **Ask clarifying questions whenever something is unclear.**
3. **Pause implementation if a decision is required.**
4. Follow **Laravel best practices**.
5. Avoid unnecessary complexity.

---

# Current Environment

The project already has:

* Laravel installed
* Laravel built-in authentication enabled
* Cloudinary Laravel package installed
* `.env` will include `CLOUDINARY_URL`
* Users table already exists

Do **not recreate authentication**.

---

# Cloudinary Requirements

Cloudinary must fully manage media:

Uploads should include:

1. **Profile Photos**

   * Folder: `profile_photos`

2. **Project Screenshots**

   * Folder: `project_images`

3. **Generated Thumbnails**

   * Folder: `project_thumbnails`

Requirements:

* Thumbnails should be **auto-generated**
* Use Cloudinary transformations
* Provide sizes optimized for:

  * mobile
  * tablet
  * desktop
* Maintain good aspect ratios for screenshots.

Ask before deciding the exact ratios.

---

# Database

There are **no project tables yet**.

You will design and create the necessary tables.

Expected entities:

Users already exist.

You will likely need something like:

* projects
* project_tags (optional if tagging is normalized)

However **confirm schema decisions with me first**.

Projects must allow:

* screenshot image
* generated thumbnail
* description
* tags
* ownership (user_id)

Users must be able to:

* upload projects
* delete their own projects
* update profile photo

---

# Homepage Layout

The home page should display **project screenshots in a responsive grid**.

Layout rules:

Desktop

* 4 columns

Tablet

* 2 columns

Mobile

* 1 column

Requirements:

* Grid must be **perfectly centered horizontally**
* Avoid layout shifts
* Avoid margin whitespace issues
* Follow **Mac-style layout guidelines**
* Clean spacing
* No overlapping containers

Use a **Load More button** to simulate infinite scrolling.

Do not implement full infinite scroll unless requested.

---

# UI Design System

Color palette:

Primary Green
#22C55E

Dark Green
#15803D

Yellow Accent
#FDE047

Background
#F0FDF4

Text
#14532D

Gradient

linear-gradient(135deg, #22C55E 0%, #86EFAC 60%, #FDE047 100%)

Usage guidelines:

Hero / banner

* gradient background

Buttons

* gradient CTA

Accents

* yellow only for highlights

Overall feel:

* energetic
* startup aesthetic
* SaaS-like dashboard
* clean modern layout

---

# User Features

Users should be able to:

1. Upload profile photo
2. Upload project screenshots
3. Add description to projects
4. Add tags to projects
5. Delete their own projects
6. Delete their own profile photos

---

# Cloudinary Integration Requirements

When a project image is uploaded:

1. Upload original image to Cloudinary
2. Generate thumbnail automatically
3. Save returned URLs in the database
4. Serve images directly from Cloudinary URLs

Do **not store images locally**.

---

Avoid unnecessary controllers unless required.

---

# Questions You Should Ask During Implementation

Before proceeding, ask questions about:

* project table structure
* tagging strategy
* thumbnail aspect ratios
* Cloudinary folder structure
* grid card design
* pagination vs load-more behavior -> load more
* whether guests can view projects or login required -> Guests can view

---
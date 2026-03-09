# Archivo de Chile — Progress Log

## Build Status: All 11 Slices Implemented

### V1: Photo Upload, Storage & Display
- **Status**: Complete
- **What**: Upload flow, S3 storage, ProcessPhotoUpload job (GD variants: thumb/medium/original, pHash)
- **Key files**: PhotoController, StorePhotoRequest, ProcessPhotoUpload job, PhotoResource
- **Tests**: 17 tests, 124 assertions

### V2: Enhanced Places & Tags
- **Status**: Complete
- **What**: Places/Tags browsing, search endpoints, Google Places proxy
- **Key files**: PlaceController, TagController, GooglePlacesController
- **Tests**: 8 tests, 82 assertions
- **Frontend**: places/Index, places/Show, tags/Index, tags/Show pages

### V3: Batch Upload Wizard
- **Status**: Complete (backend), Vue page exists
- **What**: Multi-file upload, duplicate detection via pHash
- **Key files**: BatchUploadController, BatchUploadRequest, DuplicateCheckController

### V4: Collaboration — Comments, Votes, Reports, Revisions
- **Status**: Complete
- **What**: Domain events (PhotoUploaded, MetadataEdited, CommentCreated, PhotoVoted, PersonTagged, ComparisonUploaded), comment/vote/report/revision CRUD
- **Key files**: CommentController, VoteController, ReportController, PhotoMetadataController
- **Tests**: Full collaboration test coverage

### V5: Person Tagging
- **Status**: Complete
- **What**: Tag people in photos with coordinates, person profiles
- **Key files**: PersonController, PersonTagController, StorePersonTagRequest
- **Tests**: 8 tests, 53 assertions

### V6: Gamification (Points, Levels, Badges)
- **Status**: Complete
- **What**: AwardPoints listener, EvaluateBadges listener, Leaderboard
- **Key files**: AwardPoints, EvaluateBadges listeners, LeaderboardController, RecalculatePoints command
- **Seeders**: PointActionSeeder, LevelSeeder, BadgeSeeder
- **Tests**: 15 tests, 60 assertions

### V7: Notifications & User Profiles
- **Status**: Complete
- **What**: In-app notifications (database channel), NotifyPhotoOwner listener, public user profiles
- **Key files**: NotifyPhotoOwner, NotificationController, UserProfileController
- **Tests**: 12 tests, 78 assertions

### V8: Search & Curated Home
- **Status**: Complete
- **What**: Laravel Scout + Meilisearch, curated home page (featured, latest, popular, needs help)
- **Key files**: SearchController, HomeController, Scout config
- **Tests**: 11 tests

### V9: Interactive Map
- **Status**: Complete
- **What**: Leaflet.js map with bounding box queries, temporal range filter
- **Key files**: MapController, Map.vue with Leaflet
- **Tests**: 6 tests

### V10: REST API, Admin & Security
- **Status**: Complete
- **What**: Sanctum API (v1), rate limiting, EnsureNotBanned + EnsureAdmin middleware, AdminController
- **Key files**: Api/V1/ controllers, ApiTokenController, AdminController
- **Tests**: 16 tests

### V11: Photo Comparison ("Foto del Ahora")
- **Status**: Complete
- **What**: Upload comparison photos, side-by-side display, ProcessComparisonUpload job
- **Key files**: ComparisonPhotoController, ProcessComparisonUpload job
- **Tests**: 5 tests

---

## Production Infrastructure

### Deployment
- **CI/CD**: GitHub Actions → build Docker image → push to registry.freshwork.dev → webhook deploys to k8s
- **K8s**: 3 deployments (web, worker, scheduler CronJob) + Meilisearch
- **DB**: MariaDB via operator CRs in mariadb namespace
- **Storage**: Minio at storage.freshwork.dev (S3-compatible, bucket: chiledeayer, public read)
- **Search**: Meilisearch v1.13 with PVC for data persistence

### Production Issues Resolved
1. **S3 bucket not configured** — Added SealedSecret for AWS credentials, env vars to all deployments
2. **year_to NOT NULL** — MariaDB didn't respect nullable() from migration; fixed with ALTER TABLE
3. **GD extension missing** — Docker image lacked php-gd; added to Dockerfile
4. **ProcessPhotoUpload not idempotent** — Changed create() to firstOrCreate() to handle retries
5. **Nginx 413 error** — Added proxy-body-size: 20m annotation to ingress
6. **Photos 403** — Set Minio bucket policy to public read via mc

### Branding & UX
- Replaced all "Laravel Starter Kit" references with "Archivo de Chile"
- Added OG meta tags (og:site_name, og:locale, og:image on photo pages)
- Improved Home page with hero section and navigation
- Improved Dashboard with welcome message and quick actions
- Fixed sidebar navigation: Inicio, Fotos, Lugares, Mapa, Tabla de Honor

---

## Architecture Decisions

### Shape B: Domain Events
All controllers dispatch events; listeners handle side effects (points, badges, notifications). This keeps controllers thin and makes the system extensible.

### Eloquent API Resources
All data from controllers to Inertia frontend goes through Eloquent API Resources (PhotoResource, PlaceResource, etc.). Consistent serialization.

### GD over Imagick
Used GD for image processing (thumbnails, pHash) — simpler to install in Alpine Docker, sufficient for resize operations.

### Meilisearch over Algolia
Self-hosted Meilisearch on k8s — no external dependency, fast full-text search with filtering.

### Database Queue
Using database queue driver instead of Redis for simplicity (one less service to manage in k8s).

---

## Test Suite Summary
- **Total**: 159 tests, 804 assertions
- **Duration**: ~4 seconds
- **CI**: All tests pass on GitHub Actions (SQLite in-memory with global Storage::fake('s3'))

# Spatie Laravel Media Library Integration Plan

This document outlines how we will migrate our existing post image handling to [spatie/laravel-medialibrary](https://github.com/spatie/laravel-medialibrary). The goal is to gain consistent media storage, conversions, and cleanup while keeping our Action/Service architecture intact.

## 1. Objectives

-   Replace manual image uploads in `PostService` with Media Library collections.
-   Store original uploads plus responsive conversions while exposing public URLs via API resources.
-   Maintain backwards compatibility for existing API consumers (same request payload, compatible response shape).
-   Cover the entire flow: validation, persistence, cleanup, seeding, and automated tests.

## 2. Prerequisites & Dependencies

1. **Package install**
    - `composer require spatie/laravel-medialibrary:^11.7`
    - Confirm PHP 8.2 compatibility (supported as of ML v11).
2. **Publish config & migration**
    - `php artisan vendor:publish --tag="media-library-migrations"`
    - `php artisan vendor:publish --tag="media-library-config"`
    - Run migrations.
3. **Filesystem**
    - Ensure `public` disk is configured (already used). Media Library defaults to `public` disk.
    - Confirm `storage:link` exists (part of setup script).

## 3. Domain Model Changes

### Post model

-   Implement `Spatie\MediaLibrary\HasMedia` and use `InteractsWithMedia` trait.
-   Define a `const` for the collection name, e.g., `self::COVER_IMAGE_COLLECTION = 'cover';`.
-   Register conversions (e.g., `thumb_300`, `large_1200`) inside `registerMediaConversions()`.
-   Add helper accessors:
    -   `getCoverImageUrlAttribute()` returning `getFirstMediaUrl()` fallback placeholder.
    -   `getCoverImageThumbUrlAttribute()` for thumbnail conversions.
-   Optionally eager-load media for show/index endpoints via `load('media')` or `with('media')`.

### Other models

-   No changes for `Comment` or `User` unless we add avatars later.

## 4. Service & Action Updates

### PostService

-   Remove manual `Storage` calls.
-   Accept the uploaded `UploadedFile` as today, but:
    -   On create: call `$post->addMedia($image)->toMediaCollection(Post::COVER_IMAGE_COLLECTION);`
    -   On update: delete existing media before attaching new.
    -   On delete: rely on Media Library's cascading deletes (happens automatically when model is deleted). Validate in tests.
-   Ensure service returns fresh model with media loaded for API resources.

### Actions

-   Method signatures stay the same (still accept `?UploadedFile`).
-   No DTOs needed per current approach.

## 5. HTTP Layer & Validation

-   Requests already validate `image` field. Keep same rules; consider limiting mime types and size per Media Library docs (2 MB currently?).
-   Controllers remain unchanged except for eager-loading media when returning resources.

## 6. API Resources

-   `PostResource` should append media URLs:
    -   `image_url` => `resource->cover_image_url`
    -   `image_thumb_url` => `resource->cover_image_thumb_url`
-   Remove references to raw `image` column (we will drop the column after migration if desired; for now keep until data migration completes).

## 7. Database Migration Strategy

1. **New tables**: run vendor migration to create `media` table.
2. **Legacy column**: keep `posts.image` temporarily for backwards compatibility and to allow data migration script to copy existing files.
3. After verifying new system, add follow-up migration to drop `image` column and delete old files (optional, future step).

## 8. Data Migration (Optional follow-up)

-   Artisan command to iterate existing posts with `image` value, import file into Media Library, then clear column + delete old file.
-   Not part of initial rollout but plan for it.

## 9. Testing Plan

-   **Feature tests**
    -   Update `PostTest::test_authenticated_user_can_create_post_with_image` to assert media record and JSON URLs.
    -   Add test verifying image replacement on update.
    -   Verify delete removes media (`assertDatabaseMissing('media', ...)`).
-   **Unit tests**
    -   For Post model conversions: use `Storage::fake('public')` + `addMedia` to assert conversions exist.
-   **Integration**
    -   Run `php artisan test` to ensure all suites pass.

## 10. Implementation Steps Summary

1. Install package + publish config/migrations.
2. Run migrations.
3. Update `Post` model (interfaces, traits, conversions, accessors).
4. Refactor `PostService` to use Media Library.
5. Adjust `PostResource` to expose URLs.
6. Update factories/seeders to attach media when needed (e.g., use media library or rely on existing placeholder?).
7. Update tests and fixtures.
8. Remove obsolete storage logic & config entries.
9. Document new workflow in README (setup + how to regenerate conversions).

## 11. Risks & Mitigations

-   **Existing images**: ensure we do not lose references; keep legacy column until migration.
-   **Storage disk**: conversions require Imagick/GD; confirm environment has at least GD.
-   **Queue usage**: conversions default to synchronous; optionally offload to queue later.
-   **Performance**: consider `->preservingOriginal()` if future requirements need it.

## 12. Deliverables Checklist

-   [ ] Package installed & configured
-   [ ] Post model updated
-   [ ] PostService refactored
-   [ ] API responses expose URLs
-   [ ] Tests updated and passing
-   [ ] Documentation updated (README + maybe API guide)
-   [ ] Optional migration script for legacy data (future)

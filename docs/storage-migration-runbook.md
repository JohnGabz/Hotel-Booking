# Persistent Upload Storage Runbook

## Root Cause

Render rebuilds create a fresh application filesystem. Images uploaded to Laravel's local `public` disk live under `storage/app/public`; they survive only when that path is backed by durable storage. Without object storage or a Render persistent disk, uploaded room images, site-content images, landing-section images, and payment proofs are lost after rebuild/redeploy.

## Preferred Production Setup: S3-Compatible Storage

Set these Render environment variables:

```text
FILESYSTEM_UPLOADS_DISK=s3
AWS_ACCESS_KEY_ID=...
AWS_SECRET_ACCESS_KEY=...
AWS_DEFAULT_REGION=auto
AWS_BUCKET=...
AWS_URL=https://<public-bucket-or-custom-domain>
AWS_ENDPOINT=https://<account-id>.r2.cloudflarestorage.com
AWS_USE_PATH_STYLE_ENDPOINT=true
```

Notes:
- For AWS S3, use the real region, bucket, and optional `AWS_URL`.
- For Cloudflare R2, `AWS_DEFAULT_REGION=auto` and `AWS_USE_PATH_STYLE_ENDPOINT=true` are typical.
- For Backblaze B2 S3 API, use the B2 S3 endpoint, bucket, key, secret, and region.
- Keep `FILESYSTEM_DISK` as `public` or `local`; user uploads are controlled by `FILESYSTEM_UPLOADS_DISK`.

## Fallback: Render Persistent Disk

If object storage is not available, attach a Render persistent disk mounted at:

```text
/var/www/html/storage
```

Then set:

```text
FILESYSTEM_UPLOADS_DISK=public
```

This keeps Laravel's `storage/app/public` durable. Do not rely on this fallback on plans that do not support persistent disks.

## Migration / Backfill

Dry run first:

```bash
php artisan images:migrate-upload-storage --from=public --to=s3 --dry-run
```

Run migration:

```bash
php artisan images:migrate-upload-storage --from=public --to=s3
```

The command copies referenced files to the upload disk and normalizes legacy `storage/...` DB paths. It reports migrated, skipped external URLs, already-present files, missing files, failed files, and normalized records.

## Rollback

1. Set `FILESYSTEM_UPLOADS_DISK=public`.
2. Redeploy or run `php artisan optimize:clear`.
3. If DB paths were normalized, they remain valid on the public disk because they are stored as relative keys.
4. If object-storage migration failed midway, rerun the command after fixing credentials; already-present files are skipped.

## QA Checklist

- Upload a room image in Admin > Rooms, redeploy, confirm it still loads on room index and room detail pages.
- Upload a landing Hero/About/Gallery image, redeploy, confirm it still loads on the homepage and Settings card thumbnail.
- Upload payment proof from the guest dashboard, confirm admin verification still sees the booking state.
- Run `php artisan images:migrate-upload-storage --from=public --to=s3 --dry-run` and confirm no unexpected failures.
- Check browser console for broken image errors on `/`, `/rooms`, `/admin/rooms`, and `/admin/settings?tab=landing`.

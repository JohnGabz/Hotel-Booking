<?php

namespace App\Console\Commands;

use App\Models\Booking;
use App\Models\Room;
use App\Models\SiteContent;
use App\Support\ImageStorage;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;
use Throwable;

class MigrateUploadedImages extends Command
{
    protected $signature = 'images:migrate-upload-storage
        {--from=public : Source filesystem disk containing existing local uploads}
        {--to= : Destination filesystem disk; defaults to FILESYSTEM_UPLOADS_DISK}
        {--dry-run : Report what would be copied without writing files or updating records}';

    protected $description = 'Copy locally uploaded images to the configured durable upload disk and normalize legacy storage paths.';

    public function handle(): int
    {
        $fromDisk = (string) $this->option('from');
        $toDisk = (string) ($this->option('to') ?: ImageStorage::disk());
        $dryRun = (bool) $this->option('dry-run');

        $this->info("Migrating uploaded image references from [{$fromDisk}] to [{$toDisk}]" . ($dryRun ? ' (dry run)' : ''));

        $stats = [
            'migrated' => 0,
            'already_present' => 0,
            'external' => 0,
            'missing' => 0,
            'failed' => 0,
            'normalized' => 0,
        ];

        foreach ($this->references() as $reference) {
            $value = trim((string) $reference['value']);

            if ($value === '') {
                continue;
            }

            if (ImageStorage::isExternal($value)) {
                $stats['external']++;
                $this->line("external: {$reference['label']} -> {$value}");
                continue;
            }

            $path = ImageStorage::normalizePath($value);

            try {
                if (! Storage::disk($fromDisk)->exists($path)) {
                    if (Storage::disk($toDisk)->exists($path)) {
                        $stats['already_present']++;
                        $this->line("already-present: {$reference['label']} -> {$path}");
                        $this->normalizeReference($reference, $path, $dryRun, $stats);
                        continue;
                    }

                    $stats['missing']++;
                    $this->warn("missing: {$reference['label']} -> {$path}");
                    continue;
                }

                if (Storage::disk($toDisk)->exists($path)) {
                    $stats['already_present']++;
                    $this->line("already-present: {$reference['label']} -> {$path}");
                    $this->normalizeReference($reference, $path, $dryRun, $stats);
                    continue;
                }

                if (! $dryRun) {
                    $stream = Storage::disk($fromDisk)->readStream($path);

                    if ($stream === false) {
                        throw new \RuntimeException('Could not open source file stream.');
                    }

                    Storage::disk($toDisk)->put($path, $stream, ['visibility' => 'public']);

                    if (is_resource($stream)) {
                        fclose($stream);
                    }
                }

                $stats['migrated']++;
                $this->info("migrated: {$reference['label']} -> {$path}");
                $this->normalizeReference($reference, $path, $dryRun, $stats);
            } catch (Throwable $exception) {
                $stats['failed']++;
                $this->error("failed: {$reference['label']} -> {$path} ({$exception->getMessage()})");
            }
        }

        $this->table(['status', 'count'], collect($stats)->map(fn (int $count, string $key) => [$key, $count])->all());

        return $stats['failed'] > 0 ? self::FAILURE : self::SUCCESS;
    }

    protected function references(): array
    {
        $references = [];

        Room::query()->select(['id', 'images'])->chunkById(100, function ($rooms) use (&$references) {
            foreach ($rooms as $room) {
                foreach (($room->images ?? []) as $index => $image) {
                    $references[] = [
                        'type' => 'room',
                        'model' => $room,
                        'index' => $index,
                        'value' => $image,
                        'label' => "room:{$room->id}:images:{$index}",
                    ];
                }
            }
        });

        $siteImageKeys = collect(array_keys(SiteContent::landingPageDefaults()))
            ->filter(fn (string $key) => str_contains($key, 'image'));

        SiteContent::query()->whereIn('key', $siteImageKeys)->get()->each(function (SiteContent $content) use (&$references) {
            $references[] = [
                'type' => 'site_content',
                'model' => $content,
                'value' => $content->value,
                'label' => "site_content:{$content->key}",
            ];
        });

        Booking::query()->whereNotNull('payment_proof_path')->select(['id', 'payment_proof_path'])->chunkById(100, function ($bookings) use (&$references) {
            foreach ($bookings as $booking) {
                $references[] = [
                    'type' => 'booking',
                    'model' => $booking,
                    'value' => $booking->payment_proof_path,
                    'label' => "booking:{$booking->id}:payment_proof_path",
                ];
            }
        });

        return $references;
    }

    protected function normalizeReference(array $reference, string $normalizedPath, bool $dryRun, array &$stats): void
    {
        if ($reference['value'] === $normalizedPath || ImageStorage::isExternal($reference['value'])) {
            return;
        }

        if ($dryRun) {
            $stats['normalized']++;
            $this->line("would-normalize: {$reference['label']} -> {$normalizedPath}");
            return;
        }

        match ($reference['type']) {
            'room' => $this->normalizeRoomImage($reference, $normalizedPath),
            'site_content' => $reference['model']->update(['value' => $normalizedPath]),
            'booking' => $reference['model']->update(['payment_proof_path' => $normalizedPath]),
            default => null,
        };

        $stats['normalized']++;
        $this->line("normalized: {$reference['label']} -> {$normalizedPath}");
    }

    protected function normalizeRoomImage(array $reference, string $normalizedPath): void
    {
        $room = $reference['model'];
        $images = $room->images ?? [];
        $images[$reference['index']] = $normalizedPath;

        $room->update(['images' => array_values($images)]);
    }
}

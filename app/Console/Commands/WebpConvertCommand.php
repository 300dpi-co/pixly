<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Console\Command;
use App\Services\ImageProcessor;

/**
 * WebP Conversion Command
 *
 * Converts existing images to WebP format for faster loading.
 *
 * Usage:
 *   php cli.php webp:convert           # Convert all images without WebP
 *   php cli.php webp:convert --all     # Reconvert all images (overwrite)
 *   php cli.php webp:convert --limit=100  # Limit to 100 images
 *   php cli.php webp:convert --dry-run    # Show what would be converted
 */
class WebpConvertCommand extends Command
{
    protected string $name = 'webp:convert';
    protected string $description = 'Convert images to WebP format';

    public function run(array $args): int
    {
        $options = $this->parseOptions($args);
        $limit = isset($options['limit']) ? (int) $options['limit'] : 0;
        $all = isset($options['all']);
        $dryRun = isset($options['dry-run']);
        $verbose = isset($options['v']) || isset($options['verbose']);

        // Check WebP support
        if (!ImageProcessor::webpSupported()) {
            $this->error('WebP is not supported on this server. Please install GD with WebP support.');
            return 1;
        }

        $this->info('WebP Conversion Tool');
        $this->line('====================');

        if ($dryRun) {
            $this->warn('DRY RUN - No files will be modified');
        }

        $db = $this->db();
        $processor = new ImageProcessor();

        // Build query based on options
        if ($all) {
            $sql = "SELECT id, storage_path, thumbnail_path, webp_path, thumbnail_webp_path
                    FROM images
                    WHERE storage_path IS NOT NULL";
        } else {
            // Only images missing WebP versions
            $sql = "SELECT id, storage_path, thumbnail_path, webp_path, thumbnail_webp_path
                    FROM images
                    WHERE storage_path IS NOT NULL
                    AND (webp_path IS NULL OR thumbnail_webp_path IS NULL)";
        }

        if ($limit > 0) {
            $sql .= " LIMIT {$limit}";
        }

        $images = $db->fetchAll($sql);
        $total = count($images);

        if ($total === 0) {
            $this->info('No images to convert.');
            return 0;
        }

        $this->line("Found {$total} images to process...\n");

        $converted = 0;
        $failed = 0;
        $skipped = 0;

        foreach ($images as $index => $image) {
            $num = $index + 1;
            $id = $image['id'];

            if ($verbose) {
                $this->line("[{$num}/{$total}] Processing image #{$id}...");
            }

            $updates = [];

            // Convert original to WebP
            if (!empty($image['storage_path']) && (empty($image['webp_path']) || $all)) {
                if ($dryRun) {
                    $this->line("  Would convert: {$image['storage_path']}");
                } else {
                    try {
                        $webpPath = $processor->convertToWebP($image['storage_path']);
                        if ($webpPath) {
                            $updates['webp_path'] = $webpPath;
                            if ($verbose) {
                                $this->info("  Created: {$webpPath}");
                            }
                        }
                    } catch (\Exception $e) {
                        if ($verbose) {
                            $this->error("  Failed: " . $e->getMessage());
                        }
                    }
                }
            }

            // Convert thumbnail to WebP
            if (!empty($image['thumbnail_path']) && (empty($image['thumbnail_webp_path']) || $all)) {
                if ($dryRun) {
                    $this->line("  Would convert thumbnail: {$image['thumbnail_path']}");
                } else {
                    try {
                        $thumbWebpPath = $processor->convertToWebP($image['thumbnail_path']);
                        if ($thumbWebpPath) {
                            $updates['thumbnail_webp_path'] = $thumbWebpPath;
                            if ($verbose) {
                                $this->info("  Created thumbnail: {$thumbWebpPath}");
                            }
                        }
                    } catch (\Exception $e) {
                        if ($verbose) {
                            $this->error("  Failed thumbnail: " . $e->getMessage());
                        }
                    }
                }
            }

            // Update database
            if (!$dryRun && !empty($updates)) {
                $setClauses = [];
                $params = ['id' => $id];

                foreach ($updates as $column => $value) {
                    $setClauses[] = "{$column} = :{$column}";
                    $params[$column] = $value;
                }

                $db->execute(
                    "UPDATE images SET " . implode(', ', $setClauses) . " WHERE id = :id",
                    $params
                );

                $converted++;
            } elseif (empty($updates)) {
                $skipped++;
            }

            // Progress indicator for non-verbose mode
            if (!$verbose && $num % 10 === 0) {
                echo ".";
                if ($num % 100 === 0) {
                    echo " [{$num}/{$total}]\n";
                }
            }
        }

        if (!$verbose) {
            echo "\n";
        }

        $this->line('');
        $this->info('Conversion complete!');
        $this->line("  Converted: {$converted}");
        $this->line("  Skipped:   {$skipped}");
        $this->line("  Failed:    {$failed}");

        return 0;
    }
}

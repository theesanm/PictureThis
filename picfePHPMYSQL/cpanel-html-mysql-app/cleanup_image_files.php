<?php
/**
 * Image File Cleanup Script
 * Run this script to physically delete image files that have been marked as soft deleted
 *
 * Usage: php cleanup_image_files.php [--dry-run]
 */

require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/src/lib/db.php';

$dryRun = in_array('--dry-run', $argv);

echo "🗑️  Image File Cleanup Script\n";
echo "=============================\n";
if ($dryRun) {
    echo "🔍 DRY RUN MODE - No files will be deleted\n\n";
} else {
    echo "⚠️  PRODUCTION MODE - Files will be permanently deleted!\n\n";
}

try {
    $pdo = get_db();

    // Get all soft deleted images
    $stmt = $pdo->query("
        SELECT id, image_url, user_id
        FROM images
        WHERE has_usage_permission = -1
        ORDER BY created_at ASC
    ");
    $deletedImages = $stmt->fetchAll(PDO::FETCH_ASSOC);

    if (empty($deletedImages)) {
        echo "ℹ️  No soft deleted images found.\n";
        exit(0);
    }

    echo "Found " . count($deletedImages) . " soft deleted images to process:\n\n";

    $filesDeleted = 0;
    $filesNotFound = 0;
    $errors = 0;

    foreach ($deletedImages as $image) {
        $imageUrl = $image['image_url'];
        $userId = $image['user_id'];

        // Extract filename from URL
        $filename = basename(parse_url($imageUrl, PHP_URL_PATH));

        // Check common upload directories
        $possiblePaths = [
            __DIR__ . '/uploads/' . $filename,
            __DIR__ . '/public/uploads/' . $filename,
            __DIR__ . '/src/uploads/' . $filename,
        ];

        $fileFound = false;
        $filePath = '';

        foreach ($possiblePaths as $path) {
            if (file_exists($path)) {
                $fileFound = true;
                $filePath = $path;
                break;
            }
        }

        if (!$fileFound) {
            echo "❌ File not found: {$filename} (User: {$userId})\n";
            $filesNotFound++;
            continue;
        }

        if ($dryRun) {
            echo "🔍 Would delete: {$filePath}\n";
            $filesDeleted++;
        } else {
            if (unlink($filePath)) {
                echo "✅ Deleted: {$filePath}\n";
                $filesDeleted++;
            } else {
                echo "❌ Failed to delete: {$filePath}\n";
                $errors++;
            }
        }
    }

    echo "\nSummary:\n";
    echo "========\n";
    echo "Files processed: " . count($deletedImages) . "\n";
    echo "Files " . ($dryRun ? "would be " : "") . "deleted: {$filesDeleted}\n";
    echo "Files not found: {$filesNotFound}\n";
    if (!$dryRun) {
        echo "Errors: {$errors}\n";
    }

    if ($filesDeleted > 0) {
        if ($dryRun) {
            echo "\n🔍 Dry run completed. Run without --dry-run to actually delete files.\n";
        } else {
            echo "\n✅ Cleanup completed successfully!\n";
            echo "Note: Database records are preserved for transaction history.\n";
        }
    }

} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
    exit(1);
}

echo "\nDone!\n";
?>
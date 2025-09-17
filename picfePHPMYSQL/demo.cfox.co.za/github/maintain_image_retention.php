<?php
/**
 * Image Retention Maintenance Script
 * Run this script periodically to mark old images as soft deleted
 *
 * Usage: php maintain_image_retention.php
 */

require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/src/lib/db.php';

echo "🧹 Image Retention Maintenance Script\n";
echo "=====================================\n\n";

try {
    $pdo = get_db();

    // Get configuration from environment
    $retentionDays = getenv('IMAGE_RETENTION_DAYS') ?: 7;
    $minImages = getenv('MIN_IMAGES_PER_USER') ?: 3;

    echo "Configuration:\n";
    echo "- Retention period: {$retentionDays} days\n";
    echo "- Minimum images per user: {$minImages}\n\n";

    // Get all users with images
    $users = $pdo->query("
        SELECT DISTINCT u.id, u.email,
               COUNT(i.id) as total_images,
               COUNT(CASE WHEN i.has_usage_permission != -1 OR i.has_usage_permission IS NULL THEN 1 END) as active_images
        FROM users u
        LEFT JOIN images i ON u.id = i.user_id
        GROUP BY u.id, u.email
        HAVING total_images > 0
        ORDER BY u.id
    ")->fetchAll(PDO::FETCH_ASSOC);

    echo "Processing " . count($users) . " users with images...\n\n";

    $totalMarkedDeleted = 0;
    $totalUsersProcessed = 0;

    foreach ($users as $user) {
        $userId = $user['id'];
        $email = $user['email'];
        $totalImages = $user['total_images'];
        $activeImages = $user['active_images'];

        echo "User: {$email} (ID: {$userId})\n";
        echo "- Total images: {$totalImages}\n";
        echo "- Active images: {$activeImages}\n";

        // Skip if user already has minimum or fewer active images
        if ($activeImages <= $minImages) {
            echo "- Skipping (has minimum images)\n\n";
            continue;
        }

        // Count images older than retention period
        $stmt = $pdo->prepare("
            SELECT COUNT(*) as old_count
            FROM images
            WHERE user_id = ?
            AND created_at < DATE_SUB(NOW(), INTERVAL ? DAY)
            AND (has_usage_permission IS NULL OR has_usage_permission != -1)
        ");
        $stmt->execute([$userId, $retentionDays]);
        $oldCount = $stmt->fetch()['old_count'];

        if ($oldCount === 0) {
            echo "- No old images to process\n\n";
            continue;
        }

        // Calculate how many to mark as deleted (keep minimum + some buffer)
        $imagesToKeep = max($minImages, $activeImages - 10);
        $imagesToDelete = min($oldCount, $activeImages - $imagesToKeep);

        if ($imagesToDelete <= 0) {
            echo "- No images to mark as deleted (keeping minimum)\n\n";
            continue;
        }

        // Mark old images as soft deleted
        $stmt = $pdo->prepare("
            UPDATE images
            SET has_usage_permission = -1
            WHERE user_id = ?
            AND created_at < DATE_SUB(NOW(), INTERVAL ? DAY)
            AND (has_usage_permission IS NULL OR has_usage_permission != -1)
            ORDER BY created_at ASC
            LIMIT ?
        ");
        $stmt->execute([$userId, $retentionDays, $imagesToDelete]);

        $markedCount = $stmt->rowCount();
        $totalMarkedDeleted += $markedCount;
        $totalUsersProcessed++;

        echo "- Marked {$markedCount} old images as soft deleted\n";
        echo "- Keeping " . ($activeImages - $markedCount) . " active images\n\n";
    }

    echo "Summary:\n";
    echo "========\n";
    echo "Users processed: {$totalUsersProcessed}\n";
    echo "Images marked as deleted: {$totalMarkedDeleted}\n";
    echo "Retention period: {$retentionDays} days\n";
    echo "Minimum images per user: {$minImages}\n\n";

    if ($totalMarkedDeleted > 0) {
        echo "✅ Maintenance completed successfully!\n";
        echo "Note: Images are marked as deleted but files are not physically removed.\n";
        echo "You can now safely delete the physical files of images with has_usage_permission = -1\n";
    } else {
        echo "ℹ️  No images needed to be marked as deleted.\n";
    }

} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
    exit(1);
}

echo "\nDone!\n";
?>
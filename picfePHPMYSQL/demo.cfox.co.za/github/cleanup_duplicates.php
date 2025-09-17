<?php
require_once '../config/database.php';

try {
    // Connect to database
    $pdo = new PDO($dsn, $username, $password, $options);

    // Find and remove duplicate images, keeping only the most recent for each user+image_url combination
    $stmt = $pdo->prepare("
        DELETE t1 FROM images t1
        INNER JOIN images t2
        WHERE t1.id < t2.id
        AND t1.user_id = t2.user_id
        AND t1.image_url = t2.image_url
    ");

    $stmt->execute();
    $deletedCount = $stmt->rowCount();

    echo "Cleaned up $deletedCount duplicate image records.\n";

    // Also clean up duplicates by prompt (same user, same prompt within last hour)
    $stmt = $pdo->prepare("
        DELETE t1 FROM images t1
        INNER JOIN images t2
        WHERE t1.id < t2.id
        AND t1.user_id = t2.user_id
        AND t1.prompt = t2.prompt
        AND t1.created_at > DATE_SUB(NOW(), INTERVAL 1 HOUR)
        AND t2.created_at > DATE_SUB(NOW(), INTERVAL 1 HOUR)
    ");

    $stmt->execute();
    $deletedPromptCount = $stmt->rowCount();

    echo "Cleaned up $deletedPromptCount duplicate prompt records.\n";

    echo "Database cleanup completed successfully.\n";

} catch (PDOException $e) {
    echo "Database error: " . $e->getMessage() . "\n";
}
?>

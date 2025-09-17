<?php
require_once __DIR__ . '/../../lib/db.php';

if (empty($_POST['prompt'])) {
    $_SESSION['gen_error'] = 'Prompt is required';
    header('Location: /generate');
    exit;
}

$prompt = trim($_POST['prompt']);

// For now, just store a placeholder generated image and redirect to gallery
try {
    $pdo = get_db();
    $stmt = $pdo->prepare('INSERT INTO images (user_id, prompt, image_url, created_at) VALUES (?, ?, ?, NOW())');
    $userId = $_SESSION['user']['id'] ?? null;
    $imageUrl = '/generated-placeholder.png';
    $stmt->execute([$userId, $prompt, $imageUrl]);

    $_SESSION['gen_success'] = 'Image generation queued (placeholder).';
    header('Location: /gallery');
    exit;
} catch (Exception $e) {
    error_log($e->getMessage());
    $_SESSION['gen_error'] = 'Failed to queue generation';
    header('Location: /generate');
    exit;
}

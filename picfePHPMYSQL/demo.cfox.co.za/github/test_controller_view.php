<?php
// Simulate the controller logic
require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/src/lib/db.php';

try {
    $pdo = get_db();

    // Load settings (same as controller)
    $settings = [];
    $cols = $pdo->query("SHOW COLUMNS FROM settings")->fetchAll(PDO::FETCH_COLUMN,0);
    if (in_array('k',$cols)) {
        $rows = $pdo->query('SELECT k,v FROM settings')->fetchAll(PDO::FETCH_ASSOC);
        foreach ($rows as $r) $settings[$r['k']] = $r['v'];
    } else {
        $row = $pdo->query('SELECT * FROM settings LIMIT 1')->fetch(PDO::FETCH_ASSOC);
        if ($row) {
            $settings['credit_cost_per_image'] = $row['credit_cost_per_image'] ?? null;
            $settings['enhance_prompt_cost'] = $row['enhanced_prompt_cost'] ?? null;
            $settings['enable_enhance'] = $row['enhanced_prompt_enabled'] ?? null;
            $settings['ai_provider'] = $row['ai_provider'] ?? null;
        }
    }

    echo "Controller loaded settings:\n";
    print_r($settings);
    echo "\n";

    // Simulate extract
    $user = null;
    $recentImages = [];

    // Set global variables (like controller does)
    $GLOBALS['view_settings'] = $settings;
    $GLOBALS['view_user'] = $user;
    $GLOBALS['view_recentImages'] = $recentImages;

    echo "Global variables set:\n";
    echo "view_settings: " . print_r($GLOBALS['view_settings'], true);
    echo "view_user: " . print_r($GLOBALS['view_user'], true);
    echo "view_recentImages: " . print_r($GLOBALS['view_recentImages'], true);
    echo "\n";

    // Simulate view logic
    $user_view = $GLOBALS['view_user'] ?? null;
    $settings_view = $GLOBALS['view_settings'] ?? [];
    $recentImages_view = $GLOBALS['view_recentImages'] ?? [];

    echo "View received variables:\n";
    echo "settings: " . print_r($settings_view, true);
    echo "enable_enhance: " . ($settings_view['enable_enhance'] ?? 'NOT SET') . "\n";

    // Test the condition
    $condition = (!isset($settings_view['enable_enhance']) || $settings_view['enable_enhance']);
    echo "Condition result: " . ($condition ? 'TRUE (button should show)' : 'FALSE (button hidden)') . "\n";

} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
?>

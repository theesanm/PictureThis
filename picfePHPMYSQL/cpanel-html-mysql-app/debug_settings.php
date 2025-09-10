<?php
require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/src/lib/db.php';

try {
    $pdo = get_db();

    // Test the exact query used in the controller
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

    echo "Final settings:\n";
    print_r($settings);
    echo "\n";

    // Test the condition
    $condition = (!isset($settings['enable_enhance']) || $settings['enable_enhance']);
    echo "Condition result: " . ($condition ? 'TRUE (button should show)' : 'FALSE (button hidden)') . "\n";

} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
?>

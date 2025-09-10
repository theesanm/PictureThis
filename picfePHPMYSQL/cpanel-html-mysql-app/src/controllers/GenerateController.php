<?php
class GenerateController {
    public function index() {
    require_once __DIR__ . '/../lib/db.php';
    $pdo = get_db();
        $settings = [];
        // detect schema shape
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

    include __DIR__ . '/../views/header.php';
    include __DIR__ . '/../views/generate.php';
    include __DIR__ . '/../views/footer.php';
    }
}

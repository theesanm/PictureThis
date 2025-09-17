<?php
// Simple web installer for cPanel deployments
// - Place this file in the DocumentRoot (demo.cfox.co.za)
// - Put your git checkout under a `github/` folder next to this file
// - Visit https://demo.cfox.co.za/web_install.php to run
// - This installer will offer to copy files from `github/*` into the site root,
//   write `config/config.php`, import SQL and create `uploads/`.
// - Remove this file after successful install

// Small helper
function esc($s){ return htmlspecialchars($s, ENT_QUOTES); }

$messages = [];
$errors = [];
$canWriteConfig = is_writable(__DIR__ . '/config') || !file_exists(__DIR__ . '/config/config.php');

// Helper: recursively copy directory contents
function copy_dir($src, $dst, &$copied = 0) {
    $exclude = ['.git', '.github', '.gitignore', '.gitmodules'];
    $preserve = ['tests/diagnostics.php']; // Files to preserve if they exist
    $src = rtrim($src, "/");
    $dst = rtrim($dst, "/");
    if (!is_dir($src)) return false;
    $it = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($src, RecursiveDirectoryIterator::SKIP_DOTS), RecursiveIteratorIterator::SELF_FIRST);
    foreach ($it as $item) {
        $subPath = substr($item->getPathname(), strlen($src) + 1);
        // skip excluded top-level and their children
        foreach ($exclude as $ex) {
            if (strpos($subPath, $ex) === 0) {
                continue 2;
            }
        }
        $target = $dst . '/' . $subPath;
        if ($item->isDir()) {
            if (!is_dir($target)) @mkdir($target, 0755, true);
        } else {
            // Check if this file should be preserved
            $shouldPreserve = false;
            foreach ($preserve as $preserveFile) {
                if ($subPath === $preserveFile && file_exists($target)) {
                    $shouldPreserve = true;
                    break;
                }
            }
            
            if ($shouldPreserve) {
                // Skip copying this file, preserve the existing one
                continue;
            }
            
            // ensure target dir exists
            $dir = dirname($target);
            if (!is_dir($dir)) @mkdir($dir, 0755, true);
            if (!@copy($item->getPathname(), $target)) {
                return false;
            }
            $copied++;
        }
    }
    return true;
}

// Detect possible install sources under ./github
$githubDir = __DIR__ . '/github';
$sourceCandidates = [];
if (is_dir($githubDir)) {
    // look for common app folder patterns (search depth 3)
    $rii = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($githubDir, RecursiveDirectoryIterator::SKIP_DOTS), RecursiveIteratorIterator::SELF_FIRST);
    foreach ($rii as $f) {
        if ($f->isDir()) {
            $name = $f->getBasename();
            // prefer directories that contain index.php or a cpanel-html-mysql-app folder
            if (file_exists($f->getPathname() . '/index.php') || file_exists($f->getPathname() . '/setup_database.sql') || strtolower($name) === 'cpanel-html-mysql-app') {
                $rel = substr($f->getPathname(), strlen($githubDir) + 1);
                if (!in_array($f->getPathname(), $sourceCandidates)) {
                    $sourceCandidates[$rel] = $f->getPathname();
                }
            }
        }
    }
    // if no specific candidate found, allow using the top-level checkout
    if (empty($sourceCandidates)) {
        $sourceCandidates['.'] = $githubDir;
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $db_host = trim($_POST['db_host'] ?? '127.0.0.1');
    $db_user = trim($_POST['db_user'] ?? '');
    $db_pass = trim($_POST['db_pass'] ?? '');
    $db_name = trim($_POST['db_name'] ?? '');
    $app_url = trim($_POST['app_url'] ?? 'https://' . ($_SERVER['HTTP_HOST'] ?? ''));
    $app_name = trim($_POST['app_name'] ?? 'PictureThis');
    $import_sql = isset($_POST['import_sql']);
    $write_config = isset($_POST['write_config']);
    $selected_source = trim($_POST['selected_source'] ?? '');

    // Test DB connection using mysqli
    $messages[] = "Testing database connection...";
    $mysqli = @new mysqli($db_host, $db_user, $db_pass, $db_name);
    if ($mysqli->connect_errno) {
        $errors[] = "DB connection failed: (" . $mysqli->connect_errno . ") " . $mysqli->connect_error;
    } else {
        $messages[] = "DB Connected OK. MySQL server version: " . $mysqli->server_info;
    }

    // Copy files from github/source into document root if requested (or if source chosen)
    $didCopy = 0;
    if (!empty($selected_source) && is_dir(__DIR__ . '/github/' . $selected_source)) {
        $srcReal = realpath(__DIR__ . '/github/' . $selected_source);
        $dstReal = realpath(__DIR__);
        if ($srcReal && $dstReal && $srcReal !== $dstReal) {
            $messages[] = "Copying files from github/".$selected_source." into site root...";
            $copied = 0;
            $ok = copy_dir($srcReal, $dstReal, $copied);
            if ($ok) {
                $messages[] = "Copied $copied files from github/$selected_source to site root.";
                
                // Check if diagnostics.php was preserved
                if (file_exists(__DIR__ . '/tests/diagnostics.php')) {
                    $messages[] = "✓ Preserved existing diagnostics.php file (not overwritten)";
                }
                
                $didCopy = $copied;
            } else {
                $errors[] = "Failed to copy files from github/$selected_source to site root. Check permissions.";
            }
        } else {
            $errors[] = "Invalid source or destination for copy.";
        }
    }

    // Write config file if requested
    if (empty($errors) && $write_config) {
        $configDir = __DIR__ . '/config';
        if (!is_dir($configDir)) {
            if (!mkdir($configDir, 0755, true)) {
                $errors[] = "Failed to create config directory: $configDir";
            }
        }
        if (empty($errors)) {
            $configPath = $configDir . '/config.php';
            $configContent = "<?php\n";
            $configContent .= "// Generated by web_install.php - remove this file after setup\n\n";
            // DB
            $configContent .= "define('DB_HOST', '" . addslashes($db_host) . "');\n";
            $configContent .= "define('DB_USER', '" . addslashes($db_user) . "');\n";
            $configContent .= "define('DB_PASS', '" . addslashes($db_pass) . "');\n";
            $configContent .= "define('DB_NAME', '" . addslashes($db_name) . "');\n\n";
            // App
            $configContent .= "define('APP_NAME', '" . addslashes($app_name) . "');\n";
            $configContent .= "define('APP_URL', '" . addslashes($app_url) . "');\n\n";
            // Session settings (keep conservative)
            $configContent .= "if (!headers_sent()) {\n";
            $configContent .= "    ini_set('session.cookie_domain', '');\n";
            $configContent .= "    ini_set('session.cookie_secure', 1);\n";
            $configContent .= "    ini_set('session.cookie_httponly', 1);\n";
            $configContent .= "    ini_set('session.use_only_cookies', 1);\n";
            $configContent .= "}\n\n";
            // Minimal placeholders for keys (do not overwrite keys if file existed)
            $configContent .= "define('PAYFAST_MERCHANT_ID', '');\n";
            $configContent .= "define('PAYFAST_MERCHANT_KEY', '');\n";
            $configContent .= "define('PAYFAST_PASSPHRASE', '');\n\n";
            $configContent .= "// Put any other production defaults you need here\n";
            $configContent .= "?>\n";

            if (file_put_contents($configPath, $configContent) === false) {
                $errors[] = "Failed to write config file to $configPath. Check directory permissions.";
            } else {
                $messages[] = "Wrote config file to $configPath";
                // Tighten permissions
                @chmod($configPath, 0640);
            }
        }
    }

    // Import SQL if requested
    if (empty($errors) && $import_sql) {
        // prefer setup_database.sql in site root (copied), but also accept one in github source
        $sqlPath = __DIR__ . '/setup_database.sql';
        if (!file_exists($sqlPath) && !empty($selected_source)) {
            $maybe = __DIR__ . '/github/' . $selected_source . '/setup_database.sql';
            if (file_exists($maybe)) $sqlPath = $maybe;
        }
        if (!file_exists($sqlPath)) {
            $errors[] = "SQL file not found at $sqlPath";
        } else {
            $messages[] = "Importing SQL from $sqlPath (this can take a while)...";
            $sql = file_get_contents($sqlPath);
            if ($sql === false) {
                $errors[] = "Failed to read SQL file";
            } else {
                // Use mysqli multi_query to run the SQL dump
                $mysqli->multi_query("SET @@session.sql_mode='';"); // try to avoid mode issues
                if ($mysqli->multi_query($sql)) {
                    do {
                        if ($res = $mysqli->store_result()) {
                            $res->free();
                        }
                    } while ($mysqli->more_results() && $mysqli->next_result());
                    $messages[] = "SQL import completed.";
                } else {
                    $errors[] = "SQL import failed: " . $mysqli->error;
                }
            }
        }
    }

    // Create uploads dir
    if (empty($errors)) {
        $uploads = __DIR__ . '/uploads';
        if (!is_dir($uploads)) {
            if (!mkdir($uploads, 0755, true)) {
                $errors[] = "Failed to create uploads directory: $uploads";
            } else {
                $messages[] = "Created uploads directory";
            }
        }
    }

    // Close DB connection if open
    if (!empty($mysqli) && $mysqli instanceof mysqli) {
        $mysqli->close();
    }
}

?><!doctype html>
<html>
<head>
<meta charset="utf-8">
<title>PictureThis web installer</title>
<style>body{font-family:system-ui,Segoe UI,Roboto,Arial;margin:20px;background:#f6f9fb;color:#111}label{display:block;margin:8px 0 2px}input[type=text],input[type=password]{width:100%;padding:8px;border:1px solid #ddd;border-radius:6px}button{padding:10px 16px;border-radius:6px;background:#2563eb;color:white;border:none}</style>
</head>
<body>
<h1>PictureThis - Web installer</h1>
<p>Place this file in the site root and run it once to create <code>config/config.php</code>, import the SQL and create uploads folder. <strong>Important:</strong> remove this file after a successful install to avoid exposing DB setup UI.</p>
<div style="background:#fff3cd;border:1px solid #ffeeba;padding:12px;margin:12px 0;color:#856404">
    <strong>After install:</strong>
    <ul>
        <li>Delete <code>web_install.php</code> from the server.</li>
        <li>Make sure <code>config/config.php</code> is present and not world-readable.</li>
        <li>Visit <code>/?__debug=1</code> to confirm runtime errors are visible, then disable debug.</li>
    </ul>
</div>

<?php if (!empty($errors)): ?>
    <div style="background:#fee;border:1px solid #fbb;padding:12px;margin:12px 0;color:#700">
    <strong>Errors:</strong>
    <ul><?php foreach($errors as $e) echo '<li>'.esc($e).'</li>'; ?></ul>
    </div>
<?php endif; ?>

<?php if (!empty($messages)): ?>
    <div style="background:#efe;border:1px solid #bfb;padding:12px;margin:12px 0;color:#070">
    <strong>Messages:</strong>
    <ul><?php foreach($messages as $m) echo '<li>'.esc($m).'</li>'; ?></ul>
    </div>
<?php endif; ?>

<form method="post">
    <label>DB Host (use 127.0.0.1 or localhost:3306 if needed)</label>
    <input type="text" name="db_host" value="<?php echo esc($_POST['db_host'] ?? '127.0.0.1'); ?>">

    <label>DB User</label>
    <input type="text" name="db_user" value="<?php echo esc($_POST['db_user'] ?? ''); ?>">

    <label>DB Password</label>
    <input type="password" name="db_pass" value="<?php echo esc($_POST['db_pass'] ?? ''); ?>">

    <label>DB Name</label>
    <input type="text" name="db_name" value="<?php echo esc($_POST['db_name'] ?? ''); ?>">

    <label>App URL</label>
    <input type="text" name="app_url" value="<?php echo esc($_POST['app_url'] ?? ('https://' . ($_SERVER['HTTP_HOST'] ?? 'demo.cfox.co.za'))); ?>">

    <label>Application name</label>
    <input type="text" name="app_name" value="<?php echo esc($_POST['app_name'] ?? 'PictureThis'); ?>">

    <label style="margin-top:12px"><input type="checkbox" name="write_config" <?php echo isset($_POST['write_config']) ? 'checked' : ''; ?>> Write config/config.php</label>
    <label><input type="checkbox" name="import_sql" <?php echo isset($_POST['import_sql']) ? 'checked' : ''; ?>> Import SQL from <code>setup_database.sql</code> (if present)</label>

    <?php if (!empty($sourceCandidates)): ?>
        <label style="margin-top:12px">Copy files from `github/` checkout (choose source)</label>
        <select name="selected_source">
        <?php foreach($sourceCandidates as $rel => $abs): ?>
            <option value="<?php echo esc($rel); ?>" <?php echo (isset($_POST['selected_source']) && $_POST['selected_source']===$rel)?'selected':''; ?>><?php echo esc($rel); ?></option>
        <?php endforeach; ?>
        </select>
        <p style="font-size:90%">The installer will copy files from the chosen folder under <code>github/</code> into the site root. VCS files are skipped.</p>
    <?php else: ?>
        <p style="font-size:90%">No <code>github/</code> folder detected next to this installer. If you plan to copy files manually, skip this.</p>
    <?php endif; ?>

    <div style="margin-top:12px"><button type="submit">Run installer</button></div>
</form>

<hr>
<h3>Notes</h3>
<ul>
<li>Remove <code>web_install.php</code> after use to avoid exposing your database setup UI publicly.</li>
<li>If import fails due to large SQL file, import via phpMyAdmin in cPanel.</li>
<li>After install, visit <code>/?__debug=1</code> to check for runtime errors and verify the app.</li>
<li><strong>Direct Updates:</strong> The <code>tests/diagnostics.php</code> file is preserved during redeployment, allowing you to update it directly on the server without losing your changes.</li>
</ul>
</body>
</html>

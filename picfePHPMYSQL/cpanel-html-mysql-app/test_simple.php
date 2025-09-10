<?php
// Simplified home page without Tailwind to test content rendering
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>PictureThis - Simplified Test</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background: #f0f0f0;
            color: #333;
            margin: 0;
            padding: 20px;
        }
        .container {
            max-width: 1200px;
            margin: 0 auto;
            background: white;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        h1 {
            color: #8b5cf6;
            text-align: center;
        }
        .hero {
            text-align: center;
            padding: 40px 0;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            margin: -20px -20px 20px -20px;
            border-radius: 8px 8px 0 0;
        }
        .stats {
            display: flex;
            justify-content: space-around;
            margin: 20px 0;
        }
        .stat {
            text-align: center;
        }
        .stat-number {
            font-size: 2em;
            font-weight: bold;
        }
    </style>
</head>
<body>
    <div class="container">
        <header style="background: #333; color: white; padding: 10px; margin: -20px -20px 20px -20px; border-radius: 8px 8px 0 0;">
            <h1 style="margin: 0; color: white;">PictureThis</h1>
            <nav style="margin-top: 10px;">
                <a href="/" style="color: white; margin: 0 10px;">Home</a>
                <a href="/login" style="color: white; margin: 0 10px;">Login</a>
                <a href="/register" style="color: white; margin: 0 10px;">Register</a>
            </nav>
        </header>

        <div class="hero">
            <h1>Transform Your Ideas Into Stunning Images</h1>
            <p>PictureThis harnesses the power of advanced AI to turn your creative prompts into beautiful, high-quality images.</p>
            <div style="margin-top: 20px;">
                <a href="/register" style="background: #ff69b4; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px; margin: 0 10px;">Start Creating Free</a>
                <a href="/generate" style="background: rgba(255,255,255,0.2); color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px; margin: 0 10px;">Try Generator</a>
            </div>
        </div>

        <div class="stats">
            <div class="stat">
                <div class="stat-number"><?php
                    try {
                        require_once __DIR__ . '/src/lib/db.php';
                        $pdo = get_db();
                        $imageCount = $pdo->query("SELECT COUNT(*) as count FROM images")->fetch(PDO::FETCH_ASSOC);
                        echo $imageCount['count'] ?? 0;
                    } catch (Exception $e) {
                        echo "0";
                    }
                ?>+</div>
                <div>Images Generated</div>
            </div>
            <div class="stat">
                <div class="stat-number"><?php
                    try {
                        require_once __DIR__ . '/src/lib/db.php';
                        $pdo = get_db();
                        $userCount = $pdo->query("SELECT COUNT(*) as count FROM users")->fetch(PDO::FETCH_ASSOC);
                        echo $userCount['count'] ?? 0;
                    } catch (Exception $e) {
                        echo "0";
                    }
                ?>+</div>
                <div>Creative Users</div>
            </div>
            <div class="stat">
                <div class="stat-number">∞</div>
                <div>Possibilities</div>
            </div>
        </div>

        <h2>Features</h2>
        <p>This is a simplified test page to check if content is rendering properly.</p>
        <ul>
            <li>AI-Powered Image Generation</li>
            <li>High-Quality Results</li>
            <li>Easy to Use</li>
        </ul>

        <footer style="background: #333; color: white; padding: 20px; margin: 20px -20px -20px -20px; border-radius: 0 0 8px 8px; text-align: center;">
            <p>&copy; <?php echo date('Y'); ?> PictureThis. All rights reserved.</p>
        </footer>
    </div>
</body>
</html>

<?php
/**
 * Simple Home Page - Test basic functionality
 */

echo "<!DOCTYPE html>";
echo "<html lang='en'>";
echo "<head>";
echo "<meta charset='UTF-8'>";
echo "<meta name='viewport' content='width=device-width, initial-scale=1.0'>";
echo "<title>PictureThis - Simple Home</title>";
echo "<style>";
echo "body { font-family: Arial, sans-serif; margin: 0; padding: 20px; background: #f8f9fa; color: #333; }";
echo ".container { max-width: 1200px; margin: 0 auto; }";
echo ".hero { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; padding: 3rem 1rem; border-radius: 8px; text-align: center; margin-bottom: 2rem; }";
echo ".hero h1 { font-size: 2.5rem; margin-bottom: 1rem; }";
echo ".hero p { font-size: 1.125rem; margin-bottom: 2rem; max-width: 600px; margin-left: auto; margin-right: auto; }";
echo ".btn { display: inline-block; padding: 0.75rem 1.5rem; background: #3498db; color: white; text-decoration: none; border-radius: 6px; margin: 0.5rem; }";
echo ".btn:hover { background: #2980b9; }";
echo ".stats { display: flex; justify-content: space-around; margin: 2rem 0; flex-wrap: wrap; }";
echo ".stat { text-align: center; margin: 1rem; }";
echo ".stat-number { font-size: 2rem; font-weight: bold; color: #f39c12; }";
echo "header { background: #2c3e50; color: white; padding: 1rem; margin-bottom: 2rem; }";
echo ".nav { margin-top: 0.5rem; }";
echo ".nav a { color: white; text-decoration: none; margin: 0 1rem; }";
echo "footer { background: #2c3e50; color: white; text-align: center; padding: 2rem 0; margin-top: 3rem; }";
echo "@media (max-width: 768px) { .stats { flex-direction: column; } }";
echo "</style>";
echo "</head>";
echo "<body>";

echo "<header>";
echo "<div class='container'>";
echo "<h1>PictureThis</h1>";
echo "<nav class='nav'>";
echo "<a href='/'>Home</a>";
echo "<a href='/register'>Register</a>";
echo "<a href='/login'>Login</a>";
echo "<a href='/debug.php'>Debug</a>";
echo "</nav>";
echo "</div>";
echo "</header>";

echo "<main class='container'>";

echo "<section class='hero'>";
echo "<h1>Transform Your Ideas Into Stunning Images</h1>";
echo "<p>PictureThis harnesses the power of advanced AI to turn your creative prompts into beautiful, high-quality images. From concept to creation in seconds.</p>";
echo "<a href='/register' class='btn'>Start Creating Free</a>";
echo "<a href='/login' class='btn'>Login</a>";
echo "</section>";

echo "<section class='stats'>";
echo "<div class='stat'>";
echo "<div class='stat-number'>AI</div>";
echo "<div>Powered Generation</div>";
echo "</div>";
echo "<div class='stat'>";
echo "<div class='stat-number'>Fast</div>";
echo "<div>Results in Seconds</div>";
echo "</div>";
echo "<div class='stat'>";
echo "<div class='stat-number'>Free</div>";
echo "<div>Start Creating Now</div>";
echo "</div>";
echo "</section>";

echo "</main>";

echo "<footer>";
echo "<div class='container'>";
echo "<p>&copy; 2024 PictureThis. All rights reserved.</p>";
echo "</div>";
echo "</footer>";

echo "</body>";
echo "</html>";
?>

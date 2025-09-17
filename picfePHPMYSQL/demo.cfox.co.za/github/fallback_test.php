<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Fallback Test</title>
    <script>
      // Test fallback CSS loading
      (function() {
        var script = document.createElement('script');
        script.src = 'https://cdn.tailwindcss.com';
        script.onload = function() {
          console.log('Tailwind loaded successfully');
          tailwind.config = { darkMode: 'class' };
          document.body.classList.add('tailwind-loaded');
        };
        script.onerror = function() {
          console.log('Tailwind failed, using fallback');
          var fallbackCSS = document.createElement('style');
          fallbackCSS.textContent = `
            body { font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif; margin: 0; padding: 20px; background: #1a202c; color: #e2e8f0; }
            .hero { text-align: center; padding: 3rem 1rem; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; border-radius: 8px; margin: 1rem 0; }
            .btn { display: inline-block; padding: 0.75rem 1.5rem; background: #3182ce; color: white; text-decoration: none; border-radius: 6px; margin: 0.5rem; }
            .stats { display: flex; justify-content: space-around; margin: 2rem 0; }
            .stat { text-align: center; }
            .stat-number { font-size: 2rem; font-weight: bold; }
          `;
          document.head.appendChild(fallbackCSS);
          document.body.classList.add('fallback-loaded');
        };
        document.head.appendChild(script);
      })();
    </script>
</head>
<body>
    <div class="hero">
        <h1>Fallback Test</h1>
        <p>If you see this styled page, the fallback CSS is working!</p>
        <a href="/" class="btn">Home</a>
    </div>

    <div class="stats">
        <div class="stat">
            <div class="stat-number">100+</div>
            <div>Images</div>
        </div>
        <div class="stat">
            <div class="stat-number">50+</div>
            <div>Users</div>
        </div>
    </div>

    <script>
      setTimeout(function() {
        console.log('Body classes:', document.body.className);
      }, 2000);
    </script>
</body>
</html>

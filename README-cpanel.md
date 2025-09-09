# PictureThis cPanel deployment

This scaffold provides a minimal PHP + MySQL version of the app that can be deployed to cPanel + Apache.

Files added
- `public/` - public assets: `index.php`, `css/`, `js/`
- `src/api/search.php` - example PHP API endpoint
- `config/config.php` - DB config (uses env vars if set)
- `sql/schema.sql` - example schema for `pictures` table
- `.htaccess` - routes requests to `public/` and `src/api`

Quick deploy steps (cPanel)
1. Create a MySQL database and user in cPanel > MySQL Databases.
2. Edit `config/config.php` or set environment variables in cPanel:
   - `DB_HOST`, `DB_USER`, `DB_PASS`, `DB_NAME`.
3. Import `sql/schema.sql` using phpMyAdmin.
4. Upload repository files into your `public_html` (or a subfolder). Ensure `.htaccess` is present.
5. Adjust file paths in `public/index.php` if you place files under a subfolder.

Notes
- Replace `public/css/style.css` and `public/js/main.js` with your existing frontend assets to preserve look-and-feel.
- This is intentionally minimal. Add authentication, input validation, prepared statements, and CSRF protections before production.

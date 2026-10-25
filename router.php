<?php
// router.php - Clean URL Router for PHP built-in server (php -S localhost:8000 router.php)
require_once __DIR__ . '/sitemap-generator.php';
// Automatically refresh sitemap.xml and image-sitemap.xml if any image or post was modified
autoUpdateSitemapsIfModified(30);

$uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);

// If the requested URI exists as a physical static file (CSS, JS, images, etc.), serve directly
if ($uri !== '/' && file_exists(__DIR__ . $uri)) {
    return false;
}

$trimmedUri = trim($uri, '/');

// Root / Home
if ($trimmedUri === '' || $trimmedUri === 'index' || $trimmedUri === 'index.html' || $trimmedUri === 'index.php') {
    require __DIR__ . '/index.php';
    exit;
}

// Blog Catalog: /blog or /blog/
if ($trimmedUri === 'blog') {
    require __DIR__ . '/blog.php';
    exit;
}

// Photo & Video Gallery: /gallery or /gallery/
if ($trimmedUri === 'gallery') {
    require __DIR__ . '/gallery.php';
    exit;
}

// Expedition Tours: /tours or /tours/
if ($trimmedUri === 'tours') {
    require __DIR__ . '/tours.php';
    exit;
}

// Single Blog Post: /blog/{slug}
if (preg_match('#^blog/([a-zA-Z0-9_-]+)$#', $trimmedUri, $matches)) {
    $_GET['slug'] = $matches[1];
    require __DIR__ . '/post.php';
    exit;
}

// Admin: /admin or /admin/
if ($trimmedUri === 'admin') {
    require __DIR__ . '/admin.php';
    exit;
}

// Fallback: Check if matching PHP file exists
if (file_exists(__DIR__ . '/' . $trimmedUri . '.php')) {
    require __DIR__ . '/' . $trimmedUri . '.php';
    exit;
}

// 404 Not Found
http_response_code(404);
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>404 - Page Not Found | Badagry Visitors Guide</title>
  <link rel="stylesheet" href="/style.css">
</head>
<body style="display:flex;align-items:center;justify-content:center;height:100vh;font-family:'Plus Jakarta Sans',sans-serif;text-align:center;background:#FAF8F5;">
  <div>
    <h1 style="font-size:3rem;margin-bottom:12px;color:#0F172A;">404</h1>
    <p style="font-size:1.2rem;color:#526071;margin-bottom:24px;">The page you are looking for does not exist.</p>
    <a href="/blog/" class="btn btn-primary" style="display:inline-block;padding:12px 24px;background:#0D5C3A;color:#fff;text-decoration:none;border-radius:10px;font-weight:600;">Explore Heritage Chronicle</a>
  </div>
</body>
</html>

<?php
// router.php - Clean URL Router for PHP built-in server (php -S localhost:8000 router.php)

$uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);

// If the requested URI exists as a physical static file (CSS, JS, images, etc.), serve directly
if ($uri !== '/' && file_exists(__DIR__ . $uri)) {
    return false;
}

$trimmedUri = trim($uri, '/');

// Root / Home
if ($trimmedUri === '' || $trimmedUri === 'index' || $trimmedUri === 'index.html') {
    require __DIR__ . '/index.html';
    exit;
}

// Blog Catalog: /blog or /blog/
if ($trimmedUri === 'blog') {
    require __DIR__ . '/blog.php';
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
<body style="display:flex;align-items:center;justify-content:center;height:100vh;font-family:'Inter',sans-serif;text-align:center;background:#f8fafc;">
  <div>
    <h1 style="font-size:3rem;margin-bottom:12px;color:#1e293b;">404</h1>
    <p style="font-size:1.2rem;color:#64748b;margin-bottom:24px;">The page you are looking for does not exist.</p>
    <a href="/blog/" class="btn btn-primary" style="display:inline-block;padding:12px 24px;background:#238843;color:#fff;text-decoration:none;border-radius:8px;">Go to Travel Journal</a>
  </div>
</body>
</html>

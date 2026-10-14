<?php
session_start();
require_once __DIR__ . '/db.php';
require_once __DIR__ . '/sitemap-generator.php';
$db = getDB();

// Admin credentials: default PIN
define('ADMIN_PIN', 'badagry2026');

$action = $_GET['action'] ?? 'list';
$error = '';
$success = '';

// Handle Login
if (isset($_POST['login_pin'])) {
    if ($_POST['login_pin'] === ADMIN_PIN) {
        $_SESSION['admin_logged_in'] = true;
        header('Location: /admin');
        exit;
    } else {
        $error = 'Incorrect Security PIN. Please try again.';
    }
}

// Handle Logout
if (isset($_GET['logout'])) {
    unset($_SESSION['admin_logged_in']);
    header('Location: /admin');
    exit;
}

$isLoggedIn = !empty($_SESSION['admin_logged_in']);

// Actions requiring authentication
if ($isLoggedIn) {
    // Helper: Normalize $_FILES array for single or multiple uploads
    if (!function_exists('normalizeFilesArray')) {
        function normalizeFilesArray($files): array {
            $normalized = [];
            if (empty($files) || !isset($files['name'])) {
                return $normalized;
            }
            if (is_array($files['name'])) {
                $count = count($files['name']);
                for ($i = 0; $i < $count; $i++) {
                    if (empty($files['name'][$i])) continue;
                    $normalized[] = [
                        'name' => $files['name'][$i],
                        'type' => $files['type'][$i] ?? '',
                        'tmp_name' => $files['tmp_name'][$i] ?? '',
                        'error' => $files['error'][$i] ?? UPLOAD_ERR_NO_FILE,
                        'size' => $files['size'][$i] ?? 0
                    ];
                }
            } else {
                if (!empty($files['name'])) {
                    $normalized[] = [
                        'name' => $files['name'],
                        'type' => $files['type'] ?? '',
                        'tmp_name' => $files['tmp_name'] ?? '',
                        'error' => $files['error'] ?? UPLOAD_ERR_NO_FILE,
                        'size' => $files['size'] ?? 0
                    ];
                }
            }
            return $normalized;
        }
    }

    // Helper: Retrieve all uploaded videos from assets/videos
    if (!function_exists('getAllProjectVideos')) {
        function getAllProjectVideos(): array {
            $videos = [];
            $videosDir = __DIR__ . '/assets/videos';
            $allowed = ['mp4', 'webm', 'ogg', 'mov', 'm4v'];
            if (is_dir($videosDir)) {
                $files = scandir($videosDir);
                foreach ($files as $file) {
                    if ($file === '.' || $file === '..' || $file === '.gitkeep') continue;
                    $ext = strtolower(pathinfo($file, PATHINFO_EXTENSION));
                    if (!in_array($ext, $allowed)) continue;
                    $filePath = $videosDir . '/' . $file;
                    $title = function_exists('humanizeImageFilename') ? humanizeImageFilename($file) : $file;
                    $videos[] = [
                        'type' => 'video',
                        'filename' => $file,
                        'ext' => $ext,
                        'url' => '/assets/videos/' . $file,
                        'fullUrl' => defined('SITE_BASE_URL') ? SITE_BASE_URL . '/assets/videos/' . $file : '/assets/videos/' . $file,
                        'relPath' => 'assets/videos/' . $file,
                        'title' => $title,
                        'caption' => $title . ' - Video highlight from Badagry.',
                        'geo' => 'Badagry, Lagos State, Nigeria',
                        'size' => file_exists($filePath) ? filesize($filePath) : 0,
                        'mtime' => file_exists($filePath) ? filemtime($filePath) : 0
                    ];
                }
            }
            return $videos;
        }
    }

    // API: Return JSON list of all images and videos for the Media Library
    if ($action === 'get_images_json' || $action === 'get_media_json') {
        header('Content-Type: application/json');
        $imgs = getAllProjectImages();
        $list = [];
        $imgCount = 0;
        foreach ($imgs as $k => $item) {
            $isLogo = ($item['filename'] === 'logo.jpg');
            $imgCount++;
            $list[] = [
                'type' => 'image',
                'filename' => $item['filename'],
                'ext' => strtolower(pathinfo($item['filename'], PATHINFO_EXTENSION)),
                'url' => $isLogo ? '/assets/logo.jpg' : '/assets/images/' . $item['filename'],
                'fullUrl' => $item['url'],
                'relPath' => $isLogo ? 'assets/logo.jpg' : 'assets/images/' . $item['filename'],
                'title' => $item['title'],
                'caption' => $item['caption'],
                'geo' => $item['geo'],
                'size' => ($isLogo && file_exists(__DIR__ . '/assets/logo.jpg')) ? filesize(__DIR__ . '/assets/logo.jpg') : (file_exists(__DIR__ . '/assets/images/' . $item['filename']) ? filesize(__DIR__ . '/assets/images/' . $item['filename']) : 0),
                'mtime' => $item['mtime'] ?? 0
            ];
        }

        $vids = getAllProjectVideos();
        $vidCount = count($vids);
        foreach ($vids as $v) {
            $list[] = $v;
        }

        usort($list, function($a, $b) {
            return ($b['mtime'] ?? 0) <=> ($a['mtime'] ?? 0);
        });

        echo json_encode([
            'success' => true,
            'media' => $list,
            'images' => $list,
            'counts' => [
                'total' => count($list),
                'images' => $imgCount,
                'videos' => $vidCount
            ]
        ]);
        exit;
    }

    // API: Ajax Upload Media (Images & Videos, Single or Multiple)
    if (($action === 'ajax_upload_image' || $action === 'ajax_upload_media') && $_SERVER['REQUEST_METHOD'] === 'POST') {
        header('Content-Type: application/json');

        $filesToProcess = [];
        $candidateKeys = ['media_files', 'image_files', 'image_file', 'media_file', 'files'];
        foreach ($candidateKeys as $key) {
            if (isset($_FILES[$key])) {
                $filesToProcess = array_merge($filesToProcess, normalizeFilesArray($_FILES[$key]));
            }
        }

        if (empty($filesToProcess)) {
            echo json_encode(['success' => false, 'error' => 'No files were uploaded.']);
            exit;
        }

        $allowedImages = ['jpg', 'jpeg', 'png', 'webp', 'gif'];
        $allowedVideos = ['mp4', 'webm', 'ogg', 'mov', 'm4v'];

        $uploadErrors = [
            UPLOAD_ERR_INI_SIZE => 'File exceeds upload_max_filesize limit in php.ini.',
            UPLOAD_ERR_FORM_SIZE => 'File exceeds MAX_FILE_SIZE specified in the form.',
            UPLOAD_ERR_PARTIAL => 'File was only partially uploaded.',
            UPLOAD_ERR_NO_FILE => 'No file was uploaded.',
            UPLOAD_ERR_NO_TMP_DIR => 'Missing temporary upload directory.',
            UPLOAD_ERR_CANT_WRITE => 'Failed to write file to disk.',
            UPLOAD_ERR_EXTENSION => 'A PHP extension blocked the upload.'
        ];

        $uploaded = [];
        $errors = [];
        $hasNewImages = false;

        foreach ($filesToProcess as $file) {
            if ($file['error'] !== UPLOAD_ERR_OK) {
                $msg = $uploadErrors[$file['error']] ?? ('Upload error code: ' . $file['error']);
                $errors[] = "'{$file['name']}': {$msg}";
                continue;
            }

            $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
            if (in_array($ext, $allowedImages)) {
                $mediaType = 'image';
                $targetDir = __DIR__ . '/assets/images';
                $relPrefix = 'assets/images/';
            } elseif (in_array($ext, $allowedVideos)) {
                $mediaType = 'video';
                $targetDir = __DIR__ . '/assets/videos';
                $relPrefix = 'assets/videos/';
            } else {
                $errors[] = "'{$file['name']}': Unsupported format (.{$ext}). Allowed: JPG, PNG, WebP, GIF, MP4, WebM, OGG, MOV.";
                continue;
            }

            if (!is_dir($targetDir)) {
                mkdir($targetDir, 0755, true);
            }

            $baseName = pathinfo($file['name'], PATHINFO_FILENAME);
            $safeName = preg_replace('/[^A-Za-z0-9_-]+/', '-', $baseName);
            if (empty($safeName) || $safeName === '-') {
                $safeName = $mediaType . '-' . time();
            }
            $finalName = $safeName . '.' . $ext;
            $targetFile = $targetDir . '/' . $finalName;

            if (file_exists($targetFile)) {
                $finalName = $safeName . '-' . date('Ymd-His') . '-' . substr(uniqid(), -4) . '.' . $ext;
                $targetFile = $targetDir . '/' . $finalName;
            }

            if (move_uploaded_file($file['tmp_name'], $targetFile)) {
                $title = humanizeImageFilename($finalName);
                $uploaded[] = [
                    'filename' => $finalName,
                    'type' => $mediaType,
                    'ext' => $ext,
                    'relPath' => $relPrefix . $finalName,
                    'url' => '/' . $relPrefix . $finalName,
                    'title' => $title,
                    'caption' => $title . ($mediaType === 'video' ? ' - Video highlight from Badagry.' : ' - Authentic Badagry tour experience.'),
                    'geo' => 'Badagry, Lagos State, Nigeria',
                    'size' => filesize($targetFile),
                    'mtime' => filemtime($targetFile)
                ];
                if ($mediaType === 'image') {
                    $hasNewImages = true;
                }
            } else {
                $errors[] = "'{$file['name']}': Failed to save uploaded file to {$relPrefix}.";
            }
        }

        $sitemapCount = 0;
        if ($hasNewImages) {
            $sitemapRes = generateSitemaps();
            $sitemapCount = $sitemapRes['images_count'] ?? 0;
        }

        if (!empty($uploaded)) {
            $last = end($uploaded);
            echo json_encode([
                'success' => true,
                'uploaded' => $uploaded,
                'errors' => $errors,
                'total_uploaded' => count($uploaded),
                'total_errors' => count($errors),
                // Compatibility fields for single-file handlers:
                'filename' => $last['filename'],
                'type' => $last['type'],
                'relPath' => $last['relPath'],
                'url' => $last['url'],
                'title' => $last['title'],
                'caption' => $last['caption'],
                'geo' => $last['geo'],
                'sitemap_images' => $sitemapCount
            ]);
        } else {
            echo json_encode([
                'success' => false,
                'error' => !empty($errors) ? implode(' ', $errors) : 'Failed to upload files.'
            ]);
        }
        exit;
    }

    // Manually Trigger Sitemap Regeneration
    if ($action === 'update_sitemaps') {
        $sitemapRes = generateSitemaps();
        header('Location: /admin?msg=sitemap_updated&imgs=' . $sitemapRes['images_count'] . '&posts=' . $sitemapRes['posts_count']);
        exit;
    }

    // Direct Media Upload Handler (Supports multiple images & videos)
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && (isset($_POST['upload_image']) || isset($_POST['upload_media']))) {
        $filesToProcess = [];
        $candidateKeys = ['media_files', 'image_files', 'image_file', 'media_file'];
        foreach ($candidateKeys as $key) {
            if (isset($_FILES[$key])) {
                $filesToProcess = array_merge($filesToProcess, normalizeFilesArray($_FILES[$key]));
            }
        }

        if (!empty($filesToProcess)) {
            $allowedImages = ['jpg', 'jpeg', 'png', 'webp', 'gif'];
            $allowedVideos = ['mp4', 'webm', 'ogg', 'mov', 'm4v'];
            $imgSuccess = 0;
            $vidSuccess = 0;
            $errList = [];

            foreach ($filesToProcess as $file) {
                if ($file['error'] !== UPLOAD_ERR_OK) {
                    $errList[] = "'{$file['name']}': Upload error code " . $file['error'];
                    continue;
                }
                $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
                if (in_array($ext, $allowedImages)) {
                    $mediaType = 'image';
                    $targetDir = __DIR__ . '/assets/images';
                    $relPrefix = 'assets/images/';
                } elseif (in_array($ext, $allowedVideos)) {
                    $mediaType = 'video';
                    $targetDir = __DIR__ . '/assets/videos';
                    $relPrefix = 'assets/videos/';
                } else {
                    $errList[] = "'{$file['name']}': Unsupported format (.{$ext})";
                    continue;
                }

                if (!is_dir($targetDir)) mkdir($targetDir, 0755, true);

                $baseName = pathinfo($file['name'], PATHINFO_FILENAME);
                $safeName = preg_replace('/[^A-Za-z0-9_-]+/', '-', $baseName);
                if (empty($safeName) || $safeName === '-') $safeName = $mediaType . '-' . time();
                $finalName = $safeName . '.' . $ext;
                $targetFile = $targetDir . '/' . $finalName;

                if (file_exists($targetFile)) {
                    $finalName = $safeName . '-' . date('Ymd-His') . '-' . substr(uniqid(), -4) . '.' . $ext;
                    $targetFile = $targetDir . '/' . $finalName;
                }

                if (move_uploaded_file($file['tmp_name'], $targetFile)) {
                    if ($mediaType === 'image') $imgSuccess++;
                    else $vidSuccess++;
                } else {
                    $errList[] = "'{$file['name']}': Failed to save uploaded file.";
                }
            }

            if ($imgSuccess > 0) {
                $sitemapRes = generateSitemaps();
                $sitemapInfo = " ({$sitemapRes['images_count']} total images indexed in sitemap)";
            } else {
                $sitemapInfo = "";
            }

            $totalUploaded = $imgSuccess + $vidSuccess;
            if ($totalUploaded > 0) {
                $parts = [];
                if ($imgSuccess > 0) $parts[] = "{$imgSuccess} image" . ($imgSuccess > 1 ? 's' : '');
                if ($vidSuccess > 0) $parts[] = "{$vidSuccess} video" . ($vidSuccess > 1 ? 's' : '');
                $success = "Successfully uploaded " . implode(' and ', $parts) . "!{$sitemapInfo}";
            }
            if (!empty($errList)) {
                $error = "Some files could not be uploaded: " . implode('; ', $errList);
            }
        } else {
            $error = 'Please select at least one image or video to upload.';
        }
    }

    // Delete Post
    if ($action === 'delete' && isset($_GET['id'])) {
        $id = (int)$_GET['id'];
        $stmt = $db->prepare("DELETE FROM posts WHERE id = :id");
        $stmt->execute([':id' => $id]);
        // Automatically regenerate sitemaps
        generateSitemaps();
        header('Location: /admin?msg=deleted');
        exit;
    }

    // Save Post (Create / Update)
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['save_post'])) {
        $id = (int)($_POST['id'] ?? 0);
        $title = trim($_POST['title'] ?? '');
        $slug = trim($_POST['slug'] ?? '');
        $excerpt = trim($_POST['excerpt'] ?? '');
        $content = trim($_POST['content'] ?? '');
        $featured_image = trim($_POST['featured_image'] ?? '');
        $category = trim($_POST['category'] ?? 'Travel Guide');
        $read_time = trim($_POST['read_time'] ?? '5 min read');
        $status = trim($_POST['status'] ?? 'published');

        if ($slug === '') {
            // Auto-generate slug
            $slug = strtolower(trim(preg_replace('/[^A-Za-z0-9-]+/', '-', $title), '-'));
        }

        if ($title === '' || $content === '') {
            $error = 'Title and content cannot be empty.';
        } else {
            try {
                if ($id > 0) {
                    $stmt = $db->prepare("
                        UPDATE posts 
                        SET title = :title, slug = :slug, excerpt = :excerpt, content = :content, 
                            featured_image = :featured_image, category = :category, read_time = :read_time, status = :status
                        WHERE id = :id
                    ");
                    $stmt->execute([
                        ':title' => $title,
                        ':slug' => $slug,
                        ':excerpt' => $excerpt,
                        ':content' => $content,
                        ':featured_image' => $featured_image,
                        ':category' => $category,
                        ':read_time' => $read_time,
                        ':status' => $status,
                        ':id' => $id
                    ]);
                    // Automatically update sitemaps
                    $sitemapRes = generateSitemaps();
                    $success = "Article updated successfully! Sitemaps updated automatically ({$sitemapRes['posts_count']} posts, {$sitemapRes['images_count']} images).";
                } else {
                    $stmt = $db->prepare("
                        INSERT INTO posts (title, slug, excerpt, content, featured_image, category, read_time, status)
                        VALUES (:title, :slug, :excerpt, :content, :featured_image, :category, :read_time, :status)
                    ");
                    $stmt->execute([
                        ':title' => $title,
                        ':slug' => $slug,
                        ':excerpt' => $excerpt,
                        ':content' => $content,
                        ':featured_image' => $featured_image,
                        ':category' => $category,
                        ':read_time' => $read_time,
                        ':status' => $status
                    ]);
                    // Automatically update sitemaps
                    $sitemapRes = generateSitemaps();
                    $success = "New article published successfully! Sitemaps updated automatically ({$sitemapRes['posts_count']} posts, {$sitemapRes['images_count']} images).";
                }
                $action = 'list';
            } catch (PDOException $e) {
                if (str_contains($e->getMessage(), 'UNIQUE')) {
                    $error = 'An article with this URL slug already exists. Please choose a unique slug.';
                } else {
                    $error = 'Database error: ' . $e->getMessage();
                }
            }
        }
    }
}

// Fetch single post for editing
$editPost = null;
if ($isLoggedIn && $action === 'edit' && isset($_GET['id'])) {
    $stmt = $db->prepare("SELECT * FROM posts WHERE id = :id LIMIT 1");
    $stmt->execute([':id' => (int)$_GET['id']]);
    $editPost = $stmt->fetch();
}

// Fetch all posts for listing
$allPosts = [];
if ($isLoggedIn) {
    $allPosts = $db->query("SELECT * FROM posts ORDER BY published_at DESC")->fetchAll();
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Blog Administration | Badagry Visitors Guide</title>
  <link rel="icon" type="image/jpeg" href="/assets/logo.jpg" />

  <!-- Fonts & Icons -->
  <link rel="preconnect" href="https://fonts.googleapis.com" />
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
  <link
    href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&family=Jost:wght@400;500;600;700;800&display=swap"
    rel="stylesheet" />
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" />

  <link rel="stylesheet" href="/style.css?v=2" />
</head>

<body class="admin-body">

  <header class="admin-header">
    <div class="site-container admin-header-wrap">
      <a href="/" class="brand-logo">
        <img src="/assets/logo.jpg" alt="Logo" class="brand-logo-img" />
      </a>
      <div class="admin-header-actions">
        <a href="/blog/" class="btn btn-sm btn-outline-white" target="_blank"><i class="fa-solid fa-arrow-up-right-from-square"></i> View Blog</a>
        <?php if ($isLoggedIn): ?>
          <a href="/admin?logout=1" class="btn btn-sm btn-outline-white"><i class="fa-solid fa-right-from-bracket"></i> Logout</a>
        <?php endif; ?>
      </div>
    </div>
  </header>

  <main class="site-container admin-container section-padding">
    <?php if (!$isLoggedIn): ?>
      <!-- Login Box -->
      <div class="admin-login-card">
        <div class="login-header text-center">
          <i class="fa-solid fa-shield-halved login-icon"></i>
          <h2>Author Dashboard</h2>
          <p>Enter your management security PIN to publish and edit articles.</p>
        </div>

        <?php if ($error): ?>
          <div class="admin-alert alert-error"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>

        <form method="POST" action="/admin" class="admin-form">
          <div class="form-group">
            <div class="password-input-wrap">
              <input type="password" id="login_pin" name="login_pin" class="form-input" placeholder="Enter PIN" required autofocus autocomplete="current-password" />
              <button type="button" class="toggle-password-btn" id="togglePinVisibilityBtn" aria-label="Show or hide PIN" title="Show/Hide PIN">
                <i class="fa-regular fa-eye" id="togglePinIcon"></i>
              </button>
            </div>
          </div>
          <button type="submit" class="btn btn-primary w-100">Unlock Dashboard <i class="fa-solid fa-unlock"></i></button>
        </form>

        <script>
          (function() {
            const pinInput = document.getElementById('login_pin');
            const toggleBtn = document.getElementById('togglePinVisibilityBtn');
            const toggleIcon = document.getElementById('togglePinIcon');
            if (toggleBtn && pinInput && toggleIcon) {
              toggleBtn.addEventListener('click', function(e) {
                e.preventDefault();
                const isPassword = pinInput.getAttribute('type') === 'password';
                pinInput.setAttribute('type', isPassword ? 'text' : 'password');
                toggleIcon.classList.toggle('fa-eye', !isPassword);
                toggleIcon.classList.toggle('fa-eye-slash', isPassword);
                pinInput.focus();
              });
            }
          })();
        </script>
      </div>

    <?php else: ?>
      <!-- Logged In Admin Interface -->
      <div class="admin-dashboard-header">
        <div>
          <h2>Articles &amp; Journal Management</h2>
          <p>Total published posts: <strong><?= count($allPosts) ?></strong> in SQLite database (<code>database/blog.db</code>).</p>
        </div>
        <div class="admin-top-btns">
          <?php if ($action === 'list'): ?>
            <a href="/admin?action=update_sitemaps" class="btn btn-outline-dark" title="Regenerate both sitemap.xml and image-sitemap.xml"><i class="fa-solid fa-arrows-rotate"></i> Update Sitemaps</a>
            <a href="/admin?action=new" class="btn btn-primary"><i class="fa-solid fa-plus"></i> Write New Article</a>
          <?php else: ?>
            <button type="button" class="btn btn-outline-dark" id="previewArticleBtnTop" title="Preview article appearance with live formatting"><i class="fa-solid fa-eye"></i> Preview Article</button>
            <?php if ($editPost && !empty($editPost['slug'])): ?>
              <a href="/blog/<?= htmlspecialchars($editPost['slug']) ?>" target="_blank" class="btn btn-outline-dark" title="View published article on live site"><i class="fa-solid fa-arrow-up-right-from-square"></i> View Live</a>
            <?php endif; ?>
            <a href="/admin" class="btn btn-outline-dark"><i class="fa-solid fa-list"></i> Back to All Articles</a>
          <?php endif; ?>
        </div>
      </div>

      <?php if (isset($_GET['msg']) && $_GET['msg'] === 'deleted'): ?>
        <div class="admin-alert alert-success">Article was deleted successfully and sitemaps were updated.</div>
      <?php endif; ?>
      <?php if (isset($_GET['msg']) && $_GET['msg'] === 'sitemap_updated'): ?>
        <div class="admin-alert alert-success">
          <i class="fa-solid fa-circle-check"></i> Sitemaps updated successfully!
          Indexed <strong><?= (int)($_GET['imgs'] ?? 0) ?> images</strong> and <strong><?= (int)($_GET['posts'] ?? 0) ?> blog posts</strong> across <a href="/sitemap.xml" target="_blank">sitemap.xml</a> and <a href="/image-sitemap.xml" target="_blank">image-sitemap.xml</a>.
        </div>
      <?php endif; ?>
      <?php if ($success): ?>
        <div class="admin-alert alert-success"><?= $success ?></div>
      <?php endif; ?>
      <?php if ($error): ?>
        <div class="admin-alert alert-error"><?= htmlspecialchars($error) ?></div>
      <?php endif; ?>

      <!-- Sitemap Status & Quick Media Uploader Widget -->
      <?php if ($action === 'list'): ?>
        <div class="admin-card" style="margin-bottom: 24px; padding: 20px 24px;">
          <div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 16px;">
            <div>
              <h4 style="margin: 0 0 6px 0; font-size: 1.1rem;"><i class="fa-solid fa-sitemap" style="color: var(--primary);"></i> Automated Sitemap &amp; Media Sync</h4>
              <p style="margin: 0; font-size: 0.9rem; color: var(--text-muted);">
                Sitemaps update automatically whenever posts or images are added.
                <a href="/sitemap.xml" target="_blank" style="margin-left: 8px; color: var(--primary); font-weight: 600;"><i class="fa-solid fa-arrow-up-right-from-square"></i> sitemap.xml</a>
                <span style="margin: 0 6px; color: #cbd5e1;">|</span>
                <a href="/image-sitemap.xml" target="_blank" style="color: var(--primary); font-weight: 600;"><i class="fa-solid fa-arrow-up-right-from-square"></i> image-sitemap.xml</a>
              </p>
            </div>
            <form method="POST" action="/admin" enctype="multipart/form-data" style="display: flex; gap: 10px; align-items: center;">
              <input type="hidden" name="upload_media" value="1" />
              <label for="adminImageUpload" class="btn btn-outline-dark" style="cursor: pointer; margin: 0; display: inline-flex; align-items: center; gap: 8px;" title="Upload multiple images and videos">
                <i class="fa-solid fa-cloud-arrow-up"></i> Upload Media (Photos &amp; Videos)
              </label>
              <input type="file" id="adminImageUpload" name="media_files[]" accept=".jpg,.jpeg,.png,.webp,.gif,.mp4,.webm,.ogg,.mov,.m4v" multiple style="display: none;" onchange="this.form.submit()" />
            </form>
          </div>
        </div>
      <?php endif; ?>

      <?php if ($action === 'new' || $action === 'edit'): ?>
        <!-- Post Create / Edit Form -->
        <div class="admin-card">
          <h3 class="admin-card-title"><?= $action === 'edit' ? 'Edit Article' : 'Publish New Article' ?></h3>
          <form method="POST" action="/admin" class="admin-form">
            <input type="hidden" name="save_post" value="1" />
            <input type="hidden" name="id" value="<?= $editPost ? (int)$editPost['id'] : 0 ?>" />

            <div class="form-group">
              <label for="postTitle">Article Title *</label>
              <input type="text" id="postTitle" name="title" class="form-input" required
                value="<?= $editPost ? htmlspecialchars($editPost['title']) : '' ?>"
                placeholder="e.g. 5 Must-See Cultural Landmarks in Badagry" />
            </div>

            <div class="form-row">
              <div class="form-group">
                <label for="postSlug">URL Slug (Leave blank to auto-generate)</label>
                <input type="text" id="postSlug" name="slug" class="form-input"
                  value="<?= $editPost ? htmlspecialchars($editPost['slug']) : '' ?>"
                  placeholder="e.g. 5-must-see-cultural-landmarks-badagry" />
              </div>
              <div class="form-group">
                <label for="postCategory">Category</label>
                <input type="text" id="postCategory" name="category" class="form-input"
                  value="<?= $editPost ? htmlspecialchars($editPost['category']) : 'Travel Guides' ?>"
                  placeholder="e.g. Travel Guides, History &amp; Heritage" />
              </div>
            </div>

            <div class="form-row">
              <div class="form-group" style="flex: 2;">
                <label>Featured Image / Media</label>
                <?php
                  $featVal = $editPost ? htmlspecialchars($editPost['featured_image']) : 'assets/images/Badagry-Heritage-Museum-1-420x347.jpg';
                  $isFeatVideo = preg_match('/\.(mp4|webm|ogg|mov|m4v)$/i', $featVal);
                ?>
                <input type="hidden" id="postImage" name="featured_image" value="<?= $featVal ?>" />
                <div class="featured-image-picker">
                  <div class="featured-thumb-wrap" id="featuredThumbWrap">
                    <img id="featuredThumbImg" src="/<?= ltrim($featVal, '/') ?>" alt="Featured preview" style="<?= $isFeatVideo ? 'display: none;' : '' ?>" />
                    <video id="featuredThumbVideo" src="/<?= ltrim($featVal, '/') ?>" style="<?= $isFeatVideo ? '' : 'display: none;' ?> width: 100%; height: 100%; object-fit: cover;" muted playsinline></video>
                  </div>
                  <div class="featured-picker-actions">
                    <button type="button" class="btn btn-sm btn-primary" id="openFeaturedMediaBtn">
                      <i class="fa-solid fa-photo-film"></i> Select from Library / Upload
                    </button>
                    <span id="featuredImgPathText" style="font-size: 0.8rem; color: var(--text-muted); word-break: break-all;">
                      <?= $featVal ?>
                    </span>
                  </div>
                </div>
              </div>
              <div class="form-group" style="flex: 1;">
                <label for="postReadTime">Estimated Read Time</label>
                <input type="text" id="postReadTime" name="read_time" class="form-input"
                  value="<?= $editPost ? htmlspecialchars($editPost['read_time']) : '6 min read' ?>" />
              </div>
            </div>

            <div class="form-group">
              <label for="postExcerpt">Short Excerpt (Displayed in search results and cards)</label>
              <textarea id="postExcerpt" name="excerpt" class="form-textarea" rows="2"
                placeholder="Brief 1-2 sentence overview of the article..."><?= $editPost ? htmlspecialchars($editPost['excerpt']) : '' ?></textarea>
            </div>

            <div class="form-group">
              <label for="postContent">Article Body (Supports HTML tags) *</label>
              <div class="editor-toolbar">
                <button type="button" class="btn-tool btn-tool-primary" id="insertContentMediaBtn">
                  <i class="fa-solid fa-photo-film"></i> Insert Media (Photo / Video)
                </button>
                <button type="button" class="btn-tool" onclick="insertTag('h2')" title="Insert Subheading">H2</button>
                <button type="button" class="btn-tool" onclick="insertTag('h3')" title="Insert Sub-subheading">H3</button>
                <button type="button" class="btn-tool" onclick="insertTag('strong')" title="Bold text"><b>B</b></button>
                <button type="button" class="btn-tool" onclick="insertTag('p')" title="Paragraph">&lt;p&gt;</button>
                <button type="button" class="btn-tool" onclick="insertTag('callout')" title="Highlighted callout box"><i class="fa-solid fa-quote-left"></i> Callout</button>
              </div>
              <textarea id="postContent" name="content" class="form-textarea has-toolbar" rows="14" required
                placeholder="Write your article content here. Click 'Insert Media' above to insert photos or videos anywhere in the text..."><?= $editPost ? htmlspecialchars($editPost['content']) : '' ?></textarea>
            </div>

            <div class="form-row">
              <div class="form-group">
                <label for="postStatus">Publishing Status</label>
                <select id="postStatus" name="status" class="form-select">
                  <option value="published" <?= ($editPost && $editPost['status'] === 'published') ? 'selected' : '' ?>>Published</option>
                  <option value="draft" <?= ($editPost && $editPost['status'] === 'draft') ? 'selected' : '' ?>>Draft</option>
                </select>
              </div>
            </div>

            <div class="admin-form-actions" style="display: flex; gap: 12px; align-items: center; flex-wrap: wrap;">
              <button type="submit" class="btn btn-primary"><?= $action === 'edit' ? 'Update Article' : 'Publish Article' ?> <i class="fa-solid fa-check"></i></button>
              <button type="button" class="btn btn-outline-dark" id="previewArticleBtnBottom" title="Preview article appearance with live formatting"><i class="fa-solid fa-eye"></i> Preview Article</button>
              <?php if ($editPost && !empty($editPost['slug'])): ?>
                <a href="/blog/<?= htmlspecialchars($editPost['slug']) ?>" target="_blank" class="btn btn-outline-dark" title="View published article on live site"><i class="fa-solid fa-arrow-up-right-from-square"></i> View Live</a>
              <?php endif; ?>
              <a href="/admin" class="btn btn-outline-dark">Cancel</a>
            </div>
          </form>
        </div>

      <?php else: ?>
        <!-- Posts List Cards -->
        <?php if (empty($allPosts)): ?>
          <div class="admin-card text-center" style="padding: 48px 20px;">
            <i class="fa-regular fa-newspaper" style="font-size: 3rem; color: #cbd5e1; margin-bottom: 16px;"></i>
            <h3>No articles published yet</h3>
            <p style="color: var(--text-muted); margin-bottom: 20px;">Start sharing stories, guides, and history about Badagry.</p>
            <a href="/admin?action=new" class="btn btn-primary"><i class="fa-solid fa-plus"></i> Write Your First Article</a>
          </div>
        <?php else: ?>
          <div class="admin-posts-grid">
            <?php foreach ($allPosts as $p): ?>
              <?php
                $postImg = !empty($p['featured_image']) ? '/' . ltrim($p['featured_image'], '/') : '/assets/images/Point-of-No-Return-420x347.jpg';
                $postDate = !empty($p['published_at']) ? date('M d, Y', strtotime($p['published_at'])) : date('M d, Y');
                $postViews = number_format((int)($p['views'] ?? 0));
                $postSlug = urlencode($p['slug'] ?? '');
                $postId = (int)$p['id'];
              ?>
              <div class="postcard" style="
                  background: azure;
                  border-radius: 9px;
                  width: 90%;
                  height: 250px;
                  position: relative;
                  overflow: clip;
                  margin: 0 auto;
              ">
                <div class="cardtext" style="
                  padding: 5px;
                  position: absolute;
                  height: 180px;
                  width: 100%;
                  color: white;
                  font-size: 1.2rem;
                  bottom: 0;
                  font-weight: 800;
                  text-align: center;
                  background: linear-gradient(1deg, black, #0000008c, #00000000);
                  display: flex;
                  align-items: center;
                  justify-content: center;
                ">
                  <a href="/blog/<?= $postSlug ?>" target="_blank" style="color: white; text-decoration: none; padding: 0 10px 30px;">
                    <?= htmlspecialchars($p['title']) ?>
                  </a>
                </div>

                <img src="<?= htmlspecialchars($postImg) ?>" alt="<?= htmlspecialchars($p['title']) ?>" style="
                  width: 100%;
                  height: 100%;
                  object-fit: cover;
                " />

                <div class="mates" style="
                  position: absolute;
                  bottom: 0;
                  left: 0;
                  width: 100%;
                  color: #c0f5c0;
                  display: flex;
                  flex-direction: row;
                  align-content: center;
                  justify-content: center;
                  align-items: flex-start;
                  padding: 10px;
                  gap: 10px;
                ">
                  <span><i class="fa-solid fa-calendar"></i> <?= $postDate ?></span>
                  <span><i class="fa-solid fa-eye"></i> <?= $postViews ?></span>
                </div>

                <span class="badge-tag"><?= htmlspecialchars($p['category']) ?></span>

                <div class="actionbuttons" style="
                  display: flex;
                  flex-direction: row;
                  align-content: center;
                  align-items: center;
                  position: absolute;
                  width: auto;
                  top: 10px;
                  left: 10px;
                  gap: 8px;
                ">
                  <a href="/blog/<?= $postSlug ?>" target="_blank" class="btn-icon" title="View Article"><i class="fa-solid fa-eye"></i></a>
                  <a href="/admin?action=edit&amp;id=<?= $postId ?>" class="btn-icon" title="Edit Article"><i class="fa-solid fa-pen"></i></a>
                  <a href="/admin?action=delete&amp;id=<?= $postId ?>" class="btn-icon delete-btn" title="Delete Article" onclick="return confirm('Are you sure you want to delete this article?');"><i class="fa-solid fa-trash"></i></a>
                </div>
              </div>
            <?php endforeach; ?>
          </div>
        <?php endif; ?>
      <?php endif; ?>

    <?php endif; ?>
  </main>

  <?php if ($isLoggedIn): ?>
  <!-- Live Article Preview Modal -->
  <div class="article-preview-modal-overlay" id="articlePreviewOverlay" role="dialog" aria-modal="true" aria-label="Live Article Preview">
    <div class="article-preview-modal-container">
      <div class="article-preview-modal-header">
        <div class="preview-header-left">
          <span class="preview-badge"><i class="fa-solid fa-eye"></i> Live Article Preview</span>
          <span class="preview-status-pill" id="previewStatusPill">Draft Mode</span>
        </div>
        <div class="preview-header-actions">
          <div class="preview-device-switch">
            <button type="button" class="device-btn active" data-device="desktop" title="Desktop View"><i class="fa-solid fa-desktop"></i></button>
            <button type="button" class="device-btn" data-device="tablet" title="Tablet View"><i class="fa-solid fa-tablet-screen-button"></i></button>
            <button type="button" class="device-btn" data-device="mobile" title="Mobile View"><i class="fa-solid fa-mobile-screen-button"></i></button>
          </div>
          <button type="button" class="btn btn-sm btn-outline-white" id="previewOpenNewTabBtn" title="Open full-page preview in a new browser tab">
            <i class="fa-solid fa-arrow-up-right-from-square"></i> Open in New Tab
          </button>
          <button type="button" class="preview-modal-close" id="articlePreviewCloseBtn" aria-label="Close preview">&times;</button>
        </div>
      </div>

      <div class="article-preview-viewport-wrap" id="previewViewportWrap">
        <div class="article-preview-frame-container" id="previewFrameContainer">
          <!-- Live Rendered Article -->
          <div class="preview-article-body-wrapper">
            <div class="blog-breadcrumb-bar" style="margin-bottom: 20px; padding: 0;">
              <nav class="breadcrumb-nav">
                <a href="/" onclick="return false;">Home</a>
                <span class="sep"><i class="fa-solid fa-chevron-right"></i></span>
                <a href="/blog/" onclick="return false;">Travel Journal</a>
                <span class="sep"><i class="fa-solid fa-chevron-right"></i></span>
                <span class="current" id="previewBreadcrumbTitle">Article Title</span>
              </nav>
            </div>

            <article class="article-detail-section" style="padding: 0;">
              <div class="article-container" style="max-width: 100%; padding: 0;">
                <header class="article-header">
                  <div class="article-category-badge" id="previewCategory">Travel Guides</div>
                  <h1 class="article-title" id="previewTitle">Article Title</h1>

                  <div class="article-meta-row">
                    <div class="article-author">
                      <img src="/assets/logo.jpg" alt="Badagry Visitors Guide" class="author-avatar" />
                      <div>
                        <span class="author-name">Badagry Visitors Guide</span>
                        <span class="author-role">Local Historians &amp; Travel Specialists</span>
                      </div>
                    </div>
                    <div class="article-dates">
                      <span><i class="fa-regular fa-calendar"></i> <?= date('F d, Y') ?></span>
                      <span><i class="fa-regular fa-clock"></i> <span id="previewReadTime">6 min read</span></span>
                      <span><i class="fa-regular fa-eye"></i> Preview Mode</span>
                    </div>
                  </div>
                </header>

                <div class="article-hero-img-wrap" id="previewHeroWrap">
                  <img src="" alt="" id="previewHeroImg" class="article-hero-img" style="display: none; width: 100%; max-height: 480px; object-fit: cover;" />
                  <video src="" controls class="article-hero-img" id="previewHeroVideo" style="display: none; width: 100%; max-height: 480px; object-fit: cover; background: #000; border-radius: var(--radius-lg);"></video>
                </div>

                <div class="article-body" id="previewContent">
                  <!-- Live Content Populated here -->
                </div>

                <div class="article-cta-box" style="margin-top: 40px;">
                  <div class="cta-box-icon"><i class="fa-solid fa-compass"></i></div>
                  <div class="cta-box-text">
                    <h3>Want to visit these historical landmarks with an indigenous guide?</h3>
                    <p>We personalize day trips and multi-day tours across Badagry, Benin Republic, and Togo around your schedule and budget.</p>
                  </div>
                  <a href="#" class="btn btn-primary" onclick="return false;">Plan Your Tour</a>
                </div>
              </div>
            </article>
          </div>
        </div>
      </div>
    </div>
  </div>

  <!-- Interactive Media Library Modal -->
  <div class="media-modal-overlay" id="mediaModalOverlay">
    <div class="media-modal">
      <div class="media-modal-header">
        <h3 class="media-modal-title">
          <i class="fa-solid fa-photo-film" style="color: var(--primary);"></i>
          <span id="mediaModalHeading">Media Library</span>
        </h3>
        <button type="button" class="media-modal-close" id="mediaModalCloseBtn" aria-label="Close modal">&times;</button>
      </div>

      <div class="media-modal-tabs">
        <button type="button" class="media-tab-btn active" id="tabBtnBrowse" data-tab="browse">
          <i class="fa-solid fa-photo-film"></i> <span id="mediaTotalCount">Library (0)</span>
        </button>
        <button type="button" class="media-tab-btn" id="tabBtnUpload" data-tab="upload">
          <i class="fa-solid fa-cloud-arrow-up"></i> Upload Media (Multiple)
        </button>
      </div>

      <div class="media-modal-body">
        <!-- Tab 1: Browse Existing Media (Images & Videos) -->
        <div class="media-tab-content active" id="tabContentBrowse">
          <div class="media-browse-pane">
            <div class="media-grid-wrap">
              <div class="media-grid-filter">
                <i class="fa-solid fa-magnifying-glass" style="color: #94a3b8;"></i>
                <input type="text" id="mediaSearchInput" class="media-search-input" placeholder="Search photos and videos by name..." />
                <div class="media-type-filters" id="mediaTypeFilters">
                  <button type="button" class="media-filter-btn active" data-filter="all" id="filterBtnAll">All</button>
                  <button type="button" class="media-filter-btn" data-filter="image" id="filterBtnImages"><i class="fa-solid fa-image"></i> Photos</button>
                  <button type="button" class="media-filter-btn" data-filter="video" id="filterBtnVideos"><i class="fa-solid fa-video"></i> Videos</button>
                </div>
                <button type="button" class="btn btn-sm btn-outline-dark" id="refreshMediaListBtn" title="Refresh library">
                  <i class="fa-solid fa-arrows-rotate"></i>
                </button>
              </div>
              <div class="media-grid" id="mediaGrid">
                <!-- Media items populated via JS -->
              </div>
            </div>

            <div class="media-details-sidebar" id="mediaSidebar">
              <h4 style="margin: 0 0 12px 0; font-size: 1rem;">Selected Media</h4>
              <img id="mediaSidebarPreview" src="" alt="" class="media-preview-big" style="display: none;" />
              <video id="mediaSidebarVideoPreview" controls playsinline class="media-preview-big" style="display: none; background: #000;"></video>
              
              <div id="mediaSidebarEmptyNotice" style="color: var(--text-muted); font-size: 0.9rem; text-align: center; padding: 36px 0;">
                <i class="fa-regular fa-image" style="font-size: 2.5rem; display: block; margin-bottom: 8px; color: #cbd5e1;"></i>
                Select an image or video from the grid, or upload new files.
              </div>

              <div id="mediaSidebarDetails" style="display: none;">
                <div class="media-meta-text">
                  <div style="display: flex; align-items: center; justify-content: space-between; gap: 8px; margin-bottom: 6px;">
                    <strong id="mediaSidebarFilename" style="color: var(--dark); word-break: break-all;"></strong>
                    <span id="mediaSidebarTypeBadge" class="media-type-badge"></span>
                  </div>
                  <span id="mediaSidebarPath" style="font-size: 0.78rem; color: #64748b; word-break: break-all; display: block;"></span>
                  <div id="mediaSidebarMetaExtra" style="font-size: 0.75rem; color: #94a3b8; margin-top: 4px;"></div>
                </div>

                <div id="mediaFeaturedWarning" style="display: none; padding: 10px 12px; background: #fffbeb; border: 1px solid #fef3c7; border-radius: 6px; font-size: 0.8rem; color: #92400e; margin-bottom: 12px; line-height: 1.4;">
                  <i class="fa-solid fa-circle-info"></i> Featured article cards typically use photos. To embed this video inside the body text, switch to <strong>Insert Media into Article</strong>.
                </div>

                <!-- Fields for Content Insertion Mode -->
                <div id="mediaContentOptions" style="display: none;">
                  <div class="form-group" style="margin-bottom: 12px;">
                    <label id="mediaCaptionLabel" style="font-size: 0.85rem; margin-bottom: 4px; font-weight: 600;">Caption (Displayed under media)</label>
                    <input type="text" id="mediaInputCaption" class="form-input" style="padding: 8px 10px; font-size: 0.88rem;" placeholder="e.g. Authentic Badagry guided experience" />
                  </div>
                  <div class="form-group" id="mediaAltGroup" style="margin-bottom: 12px;">
                    <label style="font-size: 0.85rem; margin-bottom: 4px; font-weight: 600;">Alt Description (SEO)</label>
                    <input type="text" id="mediaInputAlt" class="form-input" style="padding: 8px 10px; font-size: 0.88rem;" placeholder="Describe what is visible" />
                  </div>
                  <div class="form-group" style="margin-bottom: 12px;">
                    <label style="font-size: 0.85rem; margin-bottom: 4px; font-weight: 600;">Display Layout</label>
                    <select id="mediaInputAlign" class="form-select" style="padding: 8px 10px; font-size: 0.88rem;">
                      <option value="center" selected>Centered with Caption</option>
                      <option value="full">Full Width (Banner)</option>
                      <option value="left">Float Left (Wrap text)</option>
                      <option value="right">Float Right (Wrap text)</option>
                    </select>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- Tab 2: Upload New Media (Images & Videos) -->
        <div class="media-tab-content" id="tabContentUpload">
          <div class="media-upload-pane">
            <div class="media-dropzone" id="mediaDropzone">
              <i class="fa-solid fa-cloud-arrow-up"></i>
              <h3 style="margin: 0 0 8px 0; font-size: 1.25rem;">Drag &amp; Drop Images &amp; Videos Here</h3>
              <p style="margin: 0 0 16px 0; color: var(--text-muted); font-size: 0.95rem;">
                Upload multiple files at once<br />
                <span style="font-size: 0.85rem; color: #64748b;">
                  Photos: <strong>JPG, PNG, WebP, GIF</strong> &bull; Videos: <strong>MP4, WebM, OGG, MOV</strong>
                </span>
              </p>
              <input type="file" id="mediaFileInput" accept=".jpg,.jpeg,.png,.webp,.gif,.mp4,.webm,.ogg,.mov,.m4v" multiple style="display: none;" />
              <button type="button" class="btn btn-primary" onclick="document.getElementById('mediaFileInput').click()">
                <i class="fa-solid fa-folder-open"></i> Browse Files (Choose Multiple)
              </button>
            </div>
            <div class="upload-status-box" id="uploadStatusBox"></div>
          </div>
        </div>
      </div>

      <div class="media-modal-footer">
        <button type="button" class="btn btn-outline-dark" id="mediaCancelBtn">Cancel</button>
        <button type="button" class="btn btn-primary" id="mediaConfirmActionBtn" disabled>
          <span id="mediaConfirmActionText">Select Media</span>
        </button>
      </div>
    </div>
  </div>

  <!-- Media Library Interactive Script -->
  <script>
    (function() {
      let currentModalMode = 'featured'; // 'featured' or 'content'
      let allLoadedMedia = [];
      let selectedMedia = null;
      let currentTypeFilter = 'all'; // 'all', 'image', or 'video'

      const modalOverlay = document.getElementById('mediaModalOverlay');
      const modalHeading = document.getElementById('mediaModalHeading');
      const closeBtn = document.getElementById('mediaModalCloseBtn');
      const cancelBtn = document.getElementById('mediaCancelBtn');
      const confirmBtn = document.getElementById('mediaConfirmActionBtn');
      const confirmText = document.getElementById('mediaConfirmActionText');
      const mediaGrid = document.getElementById('mediaGrid');
      const searchInput = document.getElementById('mediaSearchInput');
      const totalCountSpan = document.getElementById('mediaTotalCount');
      const refreshBtn = document.getElementById('refreshMediaListBtn');

      const filterBtns = document.querySelectorAll('.media-filter-btn');
      const filterBtnAll = document.getElementById('filterBtnAll');
      const filterBtnImages = document.getElementById('filterBtnImages');
      const filterBtnVideos = document.getElementById('filterBtnVideos');

      const sidebarPreview = document.getElementById('mediaSidebarPreview');
      const sidebarVideoPreview = document.getElementById('mediaSidebarVideoPreview');
      const sidebarEmptyNotice = document.getElementById('mediaSidebarEmptyNotice');
      const sidebarDetails = document.getElementById('mediaSidebarDetails');
      const sidebarFilename = document.getElementById('mediaSidebarFilename');
      const sidebarPath = document.getElementById('mediaSidebarPath');
      const sidebarTypeBadge = document.getElementById('mediaSidebarTypeBadge');
      const sidebarMetaExtra = document.getElementById('mediaSidebarMetaExtra');
      const featuredWarning = document.getElementById('mediaFeaturedWarning');

      const contentOptions = document.getElementById('mediaContentOptions');
      const captionLabel = document.getElementById('mediaCaptionLabel');
      const altGroup = document.getElementById('mediaAltGroup');
      const inputCaption = document.getElementById('mediaInputCaption');
      const inputAlt = document.getElementById('mediaInputAlt');
      const inputAlign = document.getElementById('mediaInputAlign');

      const dropzone = document.getElementById('mediaDropzone');
      const fileInput = document.getElementById('mediaFileInput');
      const uploadStatusBox = document.getElementById('uploadStatusBox');

      const tabBtns = document.querySelectorAll('.media-tab-btn');
      const tabBrowse = document.getElementById('tabContentBrowse');
      const tabUpload = document.getElementById('tabContentUpload');

      // Open buttons
      const openFeaturedBtn = document.getElementById('openFeaturedMediaBtn');
      const insertContentBtn = document.getElementById('insertContentMediaBtn');

      function formatBytes(bytes) {
        if (!bytes || bytes <= 0) return '';
        const k = 1024;
        const sizes = ['Bytes', 'KB', 'MB', 'GB'];
        const i = Math.floor(Math.log(bytes) / Math.log(k));
        return parseFloat((bytes / Math.pow(k, i)).toFixed(1)) + ' ' + sizes[i];
      }

      function openModal(mode) {
        currentModalMode = mode;
        modalOverlay.classList.remove('modal-mode-featured', 'modal-mode-content');
        modalOverlay.classList.add('modal-mode-' + mode);

        const sidebar = document.getElementById('mediaSidebar');
        if (sidebar) sidebar.classList.remove('is-active');

        selectedMedia = null;
        confirmBtn.disabled = true;

        if (mode === 'featured') {
          modalHeading.textContent = 'Choose Featured Image / Media';
          confirmText.textContent = 'Set as Featured';
          contentOptions.style.display = 'none';
        } else {
          modalHeading.textContent = 'Insert Media into Article';
          confirmText.textContent = 'Insert into Article';
          contentOptions.style.display = 'block';
        }

        modalOverlay.classList.add('active');
        switchTab('browse');
        if (allLoadedMedia.length === 0) {
          fetchImages();
        }
      }

      function closeModal() {
        modalOverlay.classList.remove('active');
        const sidebar = document.getElementById('mediaSidebar');
        if (sidebar) sidebar.classList.remove('is-active');
        if (sidebarVideoPreview) {
          sidebarVideoPreview.pause();
          sidebarVideoPreview.src = '';
        }
      }

      function switchTab(tabName) {
        tabBtns.forEach(btn => {
          btn.classList.toggle('active', btn.dataset.tab === tabName);
        });
        if (tabName === 'browse') {
          tabBrowse.classList.add('active');
          tabUpload.classList.remove('active');
        } else {
          tabBrowse.classList.remove('active');
          tabUpload.classList.add('active');
        }
      }

      tabBtns.forEach(btn => {
        btn.addEventListener('click', () => switchTab(btn.dataset.tab));
      });

      if (openFeaturedBtn) {
        openFeaturedBtn.addEventListener('click', () => openModal('featured'));
      }
      if (insertContentBtn) {
        insertContentBtn.addEventListener('click', () => openModal('content'));
      }
      if (closeBtn) closeBtn.addEventListener('click', closeModal);
      if (cancelBtn) cancelBtn.addEventListener('click', closeModal);

      modalOverlay.addEventListener('click', (e) => {
        if (e.target === modalOverlay) closeModal();
      });

      // Filter pills
      filterBtns.forEach(btn => {
        btn.addEventListener('click', () => {
          filterBtns.forEach(b => b.classList.remove('active'));
          btn.classList.add('active');
          currentTypeFilter = btn.dataset.filter || 'all';
          applyFilters();
        });
      });

      // Fetch media items via JSON API
      function fetchImages(selectFilenameOnDone) {
        mediaGrid.innerHTML = '<div style="padding: 24px; color: #64748b; font-size: 0.95rem; grid-column: 1/-1; text-align: center;"><i class="fa-solid fa-spinner fa-spin"></i> Loading media library...</div>';
        fetch('/admin?action=get_images_json')
          .then(res => res.json())
          .then(data => {
            if (data.success && (data.media || data.images)) {
              allLoadedMedia = data.media || data.images;
              const imgCount = data.counts ? data.counts.images : allLoadedMedia.filter(m => m.type === 'image').length;
              const vidCount = data.counts ? data.counts.videos : allLoadedMedia.filter(m => m.type === 'video').length;
              const totalCount = allLoadedMedia.length;

              totalCountSpan.textContent = `Library (${totalCount})`;
              if (filterBtnAll) filterBtnAll.textContent = `All (${totalCount})`;
              if (filterBtnImages) filterBtnImages.innerHTML = `<i class="fa-solid fa-image"></i> Photos (${imgCount})`;
              if (filterBtnVideos) filterBtnVideos.innerHTML = `<i class="fa-solid fa-video"></i> Videos (${vidCount})`;

              applyFilters();

              if (selectFilenameOnDone) {
                const found = allLoadedMedia.find(m => m.filename === selectFilenameOnDone);
                if (found) selectMedia(found);
              }
            } else {
              mediaGrid.innerHTML = '<div style="color: #ef4444; padding: 20px; grid-column: 1/-1; text-align: center;">Failed to load media library.</div>';
            }
          })
          .catch(err => {
            console.error(err);
            mediaGrid.innerHTML = '<div style="color: #ef4444; padding: 20px; grid-column: 1/-1; text-align: center;">Network error loading media library.</div>';
          });
      }

      if (refreshBtn) {
        refreshBtn.addEventListener('click', () => fetchImages());
      }

      function applyFilters() {
        const q = searchInput ? searchInput.value.toLowerCase().trim() : '';
        let filtered = allLoadedMedia;

        if (currentTypeFilter === 'image') {
          filtered = filtered.filter(item => item.type === 'image');
        } else if (currentTypeFilter === 'video') {
          filtered = filtered.filter(item => item.type === 'video');
        }

        if (q) {
          filtered = filtered.filter(item =>
            item.filename.toLowerCase().includes(q) ||
            (item.title && item.title.toLowerCase().includes(q))
          );
        }

        renderGrid(filtered);
      }

      // Render grid cards
      function renderGrid(items) {
        if (!items || items.length === 0) {
          mediaGrid.innerHTML = '<div style="padding: 30px; color: #64748b; font-size: 0.95rem; grid-column: 1/-1; text-align: center;">No matching media found. Click "Upload Media" above to upload photos or videos.</div>';
          return;
        }

        mediaGrid.innerHTML = '';
        items.forEach(item => {
          const el = document.createElement('div');
          const isVid = (item.type === 'video');
          el.className = 'media-item' + (isVid ? ' is-video' : '') + (selectedMedia && selectedMedia.filename === item.filename ? ' selected' : '');
          el.dataset.filename = item.filename;
          el.dataset.type = item.type;
          el.title = item.filename + (isVid ? ' (Video)' : ' (Photo)');
          const cleanName = escapeHtml(item.filename);

          if (isVid) {
            const extTag = escapeHtml((item.ext || item.filename.split('.').pop() || 'mp4').toUpperCase());
            el.innerHTML = `
              <div class="media-video-thumb">
                <video src="${item.url}#t=0.5" preload="metadata" muted playsinline></video>
                <div class="media-video-overlay">
                  <i class="fa-solid fa-play"></i>
                  <span class="media-video-tag">${extTag}</span>
                </div>
              </div>
              <span class="select-check"><i class="fa-solid fa-check"></i></span>
              <span class="media-item-name">${cleanName}</span>
            `;
          } else {
            el.innerHTML = `
              <img src="${item.url}" alt="${cleanName}" loading="lazy" onerror="this.onerror=null;this.src='/assets/images/' + encodeURIComponent('${item.filename}');" />
              <span class="select-check"><i class="fa-solid fa-check"></i></span>
              <span class="media-item-name">${cleanName}</span>
            `;
          }

          el.addEventListener('click', () => selectMedia(item));
          el.addEventListener('dblclick', () => {
            selectMedia(item);
            confirmBtn.click();
          });
          mediaGrid.appendChild(el);
        });
      }

      function selectMedia(item) {
        selectedMedia = item;
        confirmBtn.disabled = false;

        document.querySelectorAll('.media-item').forEach(el => {
          el.classList.toggle('selected', el.dataset.filename === item.filename);
        });

        const sidebar = document.getElementById('mediaSidebar');
        if (sidebar) sidebar.classList.add('is-active');

        sidebarEmptyNotice.style.display = 'none';
        sidebarDetails.style.display = 'block';

        const isVid = (item.type === 'video');
        const ext = (item.ext || item.filename.split('.').pop() || '').toUpperCase();

        if (isVid) {
          sidebarPreview.style.display = 'none';
          sidebarPreview.src = '';
          if (sidebarVideoPreview) {
            sidebarVideoPreview.style.display = 'block';
            sidebarVideoPreview.src = item.url;
            sidebarVideoPreview.load();
          }
          if (sidebarTypeBadge) {
            sidebarTypeBadge.textContent = 'VIDEO (' + ext + ')';
            sidebarTypeBadge.className = 'media-type-badge badge-video';
          }
          if (captionLabel) captionLabel.textContent = 'Caption (Displayed under video)';
          if (altGroup) altGroup.style.display = 'none';

          if (currentModalMode === 'featured') {
            if (featuredWarning) featuredWarning.style.display = 'block';
            confirmText.textContent = 'Set as Featured Video';
          } else {
            if (featuredWarning) featuredWarning.style.display = 'none';
            confirmText.innerHTML = '<i class="fa-solid fa-film"></i> Insert Video into Article';
          }
        } else {
          if (sidebarVideoPreview) {
            sidebarVideoPreview.pause();
            sidebarVideoPreview.style.display = 'none';
            sidebarVideoPreview.src = '';
          }
          sidebarPreview.style.display = 'block';
          sidebarPreview.src = item.url;
          if (sidebarTypeBadge) {
            sidebarTypeBadge.textContent = 'IMAGE (' + ext + ')';
            sidebarTypeBadge.className = 'media-type-badge badge-image';
          }
          if (captionLabel) captionLabel.textContent = 'Caption (Displayed under photo)';
          if (altGroup) altGroup.style.display = 'block';
          if (featuredWarning) featuredWarning.style.display = 'none';

          if (currentModalMode === 'featured') {
            confirmText.textContent = 'Set as Featured Image';
          } else {
            confirmText.innerHTML = '<i class="fa-solid fa-image"></i> Insert Image into Article';
          }
        }

        sidebarFilename.textContent = item.filename;
        sidebarPath.textContent = item.relPath || item.url;

        if (sidebarMetaExtra) {
          const parts = [];
          if (item.size) parts.push('Size: ' + formatBytes(item.size));
          if (item.geo) parts.push(item.geo);
          sidebarMetaExtra.textContent = parts.join(' • ');
        }

        if (inputAlt) inputAlt.value = item.title || '';
        if (inputCaption) inputCaption.value = item.title || '';
      }

      // Search filter
      if (searchInput) {
        searchInput.addEventListener('input', () => applyFilters());
      }

      // Handle Multiple Files Upload via AJAX
      function handleMultipleFileUploads(files) {
        if (!files || files.length === 0) return;
        const fileArray = Array.from(files);
        const total = fileArray.length;

        uploadStatusBox.innerHTML = `
          <div class="upload-batch-container">
            <div class="upload-batch-header" id="uploadBatchHeader">
              <i class="fa-solid fa-spinner fa-spin" style="color: var(--primary);"></i> Uploading ${total} file${total > 1 ? 's' : ''}...
            </div>
            <div class="upload-batch-list" id="uploadBatchList"></div>
          </div>
        `;

        const batchList = document.getElementById('uploadBatchList');
        const batchHeader = document.getElementById('uploadBatchHeader');
        let completed = 0;
        let successfulUploads = [];
        let errors = [];

        // Upload files sequentially for smooth progress reporting and avoiding PHP post limit issues
        async function processFiles() {
          for (let i = 0; i < fileArray.length; i++) {
            const file = fileArray[i];
            const isVid = /\.(mp4|webm|ogg|mov|m4v)$/i.test(file.name);
            const icon = isVid ? 'fa-film' : 'fa-image';

            const itemRow = document.createElement('div');
            itemRow.className = 'upload-batch-item';
            itemRow.innerHTML = `
              <span class="batch-item-icon"><i class="fa-solid ${icon}"></i></span>
              <span class="batch-item-name" title="${escapeHtml(file.name)}">${escapeHtml(file.name)} (${formatBytes(file.size)})</span>
              <span class="batch-item-status" id="batchItemStatus_${i}"><i class="fa-solid fa-spinner fa-spin" style="color: var(--primary);"></i></span>
            `;
            batchList.appendChild(itemRow);

            const statusEl = document.getElementById(`batchItemStatus_${i}`);
            const formData = new FormData();
            formData.append('media_files[]', file);

            try {
              const res = await fetch('/admin?action=ajax_upload_image', {
                method: 'POST',
                body: formData
              });
              const data = await res.json();
              if (data.success) {
                statusEl.innerHTML = '<span style="color: #166534;"><i class="fa-solid fa-circle-check"></i> Done</span>';
                if (data.uploaded && data.uploaded.length) {
                  successfulUploads.push(...data.uploaded);
                } else if (data.filename) {
                  successfulUploads.push(data);
                }
              } else {
                const msg = data.error || 'Failed';
                errors.push(`${file.name}: ${msg}`);
                statusEl.innerHTML = `<span style="color: #991b1b;" title="${escapeHtml(msg)}"><i class="fa-solid fa-circle-exclamation"></i> Error</span>`;
              }
            } catch (err) {
              errors.push(`${file.name}: Network error`);
              statusEl.innerHTML = '<span style="color: #991b1b;"><i class="fa-solid fa-circle-exclamation"></i> Failed</span>';
            }
            completed++;
          }

          // Complete reporting
          if (successfulUploads.length > 0) {
            const imgCount = successfulUploads.filter(u => u.type === 'image').length;
            const vidCount = successfulUploads.filter(u => u.type === 'video').length;
            const parts = [];
            if (imgCount > 0) parts.push(`${imgCount} image${imgCount > 1 ? 's' : ''}`);
            if (vidCount > 0) parts.push(`${vidCount} video${vidCount > 1 ? 's' : ''}`);

            batchHeader.innerHTML = `
              <div style="color: #166534; font-weight: 600;">
                <i class="fa-solid fa-circle-check"></i> Uploaded ${parts.join(' and ')} successfully!
                ${errors.length > 0 ? ` <span style="color: #991b1b; font-size: 0.85rem;">(${errors.length} failed)</span>` : ''}
              </div>
            `;

            // Auto-switch to Browse tab and select newest uploaded media
            const latest = successfulUploads[successfulUploads.length - 1];
            setTimeout(() => {
              switchTab('browse');
              fetchImages(latest ? latest.filename : null);
            }, 1000);
          } else {
            batchHeader.innerHTML = `
              <div style="color: #991b1b; font-weight: 600;">
                <i class="fa-solid fa-circle-exclamation"></i> Upload failed. Please check file format and file size limits.
              </div>
            `;
          }
        }

        processFiles();
      }

      if (fileInput) {
        fileInput.addEventListener('change', (e) => {
          if (e.target.files && e.target.files.length > 0) {
            handleMultipleFileUploads(e.target.files);
          }
        });
      }

      // Drag and drop events
      if (dropzone) {
        ['dragenter', 'dragover'].forEach(name => {
          dropzone.addEventListener(name, (e) => {
            e.preventDefault();
            dropzone.classList.add('dragover');
          });
        });
        ['dragleave', 'drop'].forEach(name => {
          dropzone.addEventListener(name, (e) => {
            e.preventDefault();
            dropzone.classList.remove('dragover');
          });
        });
        dropzone.addEventListener('drop', (e) => {
          if (e.dataTransfer && e.dataTransfer.files && e.dataTransfer.files.length > 0) {
            handleMultipleFileUploads(e.dataTransfer.files);
          }
        });
      }

      // Confirm Action Button
      confirmBtn.addEventListener('click', () => {
        if (!selectedMedia) return;

        if (currentModalMode === 'featured') {
          // Set Featured Media
          const postImageInput = document.getElementById('postImage');
          const thumbImg = document.getElementById('featuredThumbImg');
          const thumbVideo = document.getElementById('featuredThumbVideo');
          const pathText = document.getElementById('featuredImgPathText');

          const relPath = selectedMedia.relPath || ('assets/' + (selectedMedia.type === 'video' ? 'videos/' : 'images/') + selectedMedia.filename);
          if (postImageInput) postImageInput.value = relPath;
          if (pathText) pathText.textContent = relPath;

          if (selectedMedia.type === 'video') {
            if (thumbImg) thumbImg.style.display = 'none';
            if (thumbVideo) {
              thumbVideo.style.display = 'block';
              thumbVideo.src = selectedMedia.url;
            }
          } else {
            if (thumbVideo) {
              thumbVideo.pause();
              thumbVideo.style.display = 'none';
            }
            if (thumbImg) {
              thumbImg.style.display = 'block';
              thumbImg.src = selectedMedia.url;
            }
          }

          closeModal();
        } else {
          // Insert into Article Content
          const contentTextarea = document.getElementById('postContent');
          if (!contentTextarea) return;

          const captionText = (inputCaption && inputCaption.value.trim()) || '';
          const align = (inputAlign && inputAlign.value) || 'center';

          let snippet = '';
          if (selectedMedia.type === 'video') {
            const ext = selectedMedia.ext || selectedMedia.filename.split('.').pop().toLowerCase();
            const sourceMime = (ext === 'mov') ? 'video/quicktime' : 'video/' + ext;
            if (captionText) {
              snippet = `\n<figure class="video-container video-${align}">\n  <video controls preload="metadata" playsinline>\n    <source src="${selectedMedia.url}" type="${sourceMime}">\n    Your browser does not support HTML5 video.\n  </video>\n  <figcaption>${escapeHtml(captionText)}</figcaption>\n</figure>\n`;
            } else {
              snippet = `\n<figure class="video-container video-${align}">\n  <video controls preload="metadata" playsinline>\n    <source src="${selectedMedia.url}" type="${sourceMime}">\n    Your browser does not support HTML5 video.\n  </video>\n</figure>\n`;
            }
          } else {
            const altText = (inputAlt && inputAlt.value.trim()) || selectedMedia.title || '';
            if (captionText) {
              snippet = `\n<figure class="img-${align}">\n  <img src="${selectedMedia.url}" alt="${escapeHtml(altText)}" loading="lazy" />\n  <figcaption>${escapeHtml(captionText)}</figcaption>\n</figure>\n`;
            } else {
              snippet = `\n<figure class="img-${align}">\n  <img src="${selectedMedia.url}" alt="${escapeHtml(altText)}" loading="lazy" />\n</figure>\n`;
            }
          }

          insertTextAtCursor(contentTextarea, snippet);
          closeModal();
        }
      });

      function escapeHtml(str) {
        if (!str) return '';
        return String(str)
          .replace(/&/g, '&amp;')
          .replace(/</g, '&lt;')
          .replace(/>/g, '&gt;')
          .replace(/"/g, '&quot;');
      }
    })();

    /* ========================================================
       Article Live Preview Controller
    ======================================================== */
    (function initArticlePreview() {
      const btnPreviewTop = document.getElementById('previewArticleBtnTop');
      const btnPreviewBottom = document.getElementById('previewArticleBtnBottom');
      const modalOverlay = document.getElementById('articlePreviewOverlay');
      const closeBtn = document.getElementById('articlePreviewCloseBtn');
      const openNewTabBtn = document.getElementById('previewOpenNewTabBtn');
      const deviceBtns = document.querySelectorAll('.device-btn');
      const frameContainer = document.getElementById('previewFrameContainer');

      const elTitle = document.getElementById('postTitle');
      const elCategory = document.getElementById('postCategory');
      const elReadTime = document.getElementById('postReadTime');
      const elImage = document.getElementById('postImage');
      const elContent = document.getElementById('postContent');
      const elStatus = document.getElementById('postStatus');

      const previewTitle = document.getElementById('previewTitle');
      const previewBreadcrumb = document.getElementById('previewBreadcrumbTitle');
      const previewCategory = document.getElementById('previewCategory');
      const previewReadTime = document.getElementById('previewReadTime');
      const previewHeroWrap = document.getElementById('previewHeroWrap');
      const previewHeroImg = document.getElementById('previewHeroImg');
      const previewHeroVideo = document.getElementById('previewHeroVideo');
      const previewContent = document.getElementById('previewContent');
      const previewStatusPill = document.getElementById('previewStatusPill');

      function openPreview() {
        if (!modalOverlay) return;

        const titleVal = (elTitle && elTitle.value.trim()) || 'Untitled Article (Preview)';
        const catVal = (elCategory && elCategory.value.trim()) || 'Travel Guides';
        const readVal = (elReadTime && elReadTime.value.trim()) || '5 min read';
        const imgVal = (elImage && elImage.value.trim()) || '';
        const contentVal = (elContent && elContent.value.trim()) || '';
        const statusVal = (elStatus && elStatus.value) || 'draft';

        if (previewTitle) previewTitle.textContent = titleVal;
        if (previewBreadcrumb) previewBreadcrumb.textContent = titleVal;
        if (previewCategory) previewCategory.textContent = catVal;
        if (previewReadTime) previewReadTime.textContent = readVal;
        if (previewStatusPill) previewStatusPill.textContent = (statusVal === 'published' ? 'Published Mode' : 'Draft Mode');

        // Hero media
        const isVideo = /\.(mp4|webm|ogg|mov|m4v)$/i.test(imgVal);
        if (imgVal) {
          const mediaSrc = '/' + imgVal.replace(/^\/+/, '');
          if (previewHeroWrap) previewHeroWrap.style.display = 'block';
          if (isVideo) {
            if (previewHeroImg) previewHeroImg.style.display = 'none';
            if (previewHeroVideo) {
              previewHeroVideo.src = mediaSrc;
              previewHeroVideo.style.display = 'block';
            }
          } else {
            if (previewHeroVideo) previewHeroVideo.style.display = 'none';
            if (previewHeroImg) {
              previewHeroImg.src = mediaSrc;
              previewHeroImg.alt = titleVal;
              previewHeroImg.style.display = 'block';
            }
          }
        } else {
          if (previewHeroWrap) previewHeroWrap.style.display = 'none';
        }

        // Body content
        if (previewContent) {
          if (contentVal) {
            previewContent.innerHTML = contentVal;
          } else {
            previewContent.innerHTML = '<div style="padding: 30px; text-align: center; background: #f8fafc; border: 2px dashed #cbd5e1; border-radius: 12px; color: #64748b;"><i class="fa-regular fa-file-lines" style="font-size: 2rem; margin-bottom: 10px; display: block;"></i><em>No article body content entered yet. Start writing in the editor or insert media to see the preview.</em></div>';
          }
        }

        modalOverlay.classList.add('is-open');
        document.body.style.overflow = 'hidden';
      }

      function closePreview() {
        if (!modalOverlay) return;
        modalOverlay.classList.remove('is-open');
        document.body.style.overflow = '';
      }

      if (btnPreviewTop) btnPreviewTop.addEventListener('click', openPreview);
      if (btnPreviewBottom) btnPreviewBottom.addEventListener('click', openPreview);
      if (closeBtn) closeBtn.addEventListener('click', closePreview);

      if (modalOverlay) {
        modalOverlay.addEventListener('click', (e) => {
          if (e.target === modalOverlay) closePreview();
        });
      }

      document.addEventListener('keydown', (e) => {
        if (e.key === 'Escape' && modalOverlay && modalOverlay.classList.contains('is-open')) {
          closePreview();
        }
      });

      // Viewport Switcher (Desktop, Tablet, Mobile)
      deviceBtns.forEach(btn => {
        btn.addEventListener('click', () => {
          deviceBtns.forEach(b => b.classList.remove('active'));
          btn.classList.add('active');
          const dev = btn.dataset.device;
          if (frameContainer) {
            frameContainer.classList.remove('mode-tablet', 'mode-mobile');
            if (dev === 'tablet') frameContainer.classList.add('mode-tablet');
            if (dev === 'mobile') frameContainer.classList.add('mode-mobile');
          }
        });
      });

      // Open standalone preview in new browser tab
      if (openNewTabBtn) {
        openNewTabBtn.addEventListener('click', () => {
          const titleVal = (elTitle && elTitle.value.trim()) || 'Untitled Article (Preview)';
          const catVal = (elCategory && elCategory.value.trim()) || 'Travel Guides';
          const readVal = (elReadTime && elReadTime.value.trim()) || '5 min read';
          const imgVal = (elImage && elImage.value.trim()) || '';
          const contentVal = (elContent && elContent.value.trim()) || '<p><em>No content written yet</em></p>';
          const isVideo = /\.(mp4|webm|ogg|mov|m4v)$/i.test(imgVal);
          const mediaSrc = imgVal ? '/' + imgVal.replace(/^\/+/, '') : '';

          let heroHtml = '';
          if (imgVal) {
            if (isVideo) {
              heroHtml = `<div class="article-hero-img-wrap"><video src="${mediaSrc}" controls class="article-hero-img" style="width: 100%; max-height: 520px; object-fit: cover; background: #000; border-radius: var(--radius-lg);"></video></div>`;
            } else {
              heroHtml = `<div class="article-hero-img-wrap"><img src="${mediaSrc}" alt="${escapeHtml(titleVal)}" class="article-hero-img" /></div>`;
            }
          }

          function escapeHtml(str) {
            if (!str) return '';
            return String(str).replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;').replace(/"/g, '&quot;');
          }

          const standaloneHtml = `<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Preview: ${escapeHtml(titleVal)} | Badagry Visitors Guide</title>
  <link rel="icon" type="image/jpeg" href="/assets/logo.jpg">
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&family=Jost:wght@400;500;600;700;800&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
  <link rel="stylesheet" href="/style.css">
  <style>
    .preview-watermark-bar {
      background: #0f172a;
      color: #86efac;
      padding: 10px 20px;
      text-align: center;
      font-size: 0.88rem;
      font-weight: 600;
      position: sticky;
      top: 0;
      z-index: 9999;
      display: flex;
      justify-content: center;
      align-items: center;
      gap: 12px;
      box-shadow: 0 2px 10px rgba(0,0,0,0.2);
    }
  </style>
</head>
<body>
  <div class="preview-watermark-bar">
    <i class="fa-solid fa-eye"></i> Live Draft Preview &mdash; Badagry Visitors Guide
    <button onclick="window.close()" style="background: rgba(255,255,255,0.15); border: 1px solid rgba(255,255,255,0.3); color: #fff; padding: 4px 14px; border-radius: 20px; cursor: pointer; margin-left: 10px; font-size: 0.78rem;">Close Window</button>
  </div>
  <div class="blog-breadcrumb-bar">
    <div class="site-container">
      <nav class="breadcrumb-nav">
        <a href="/">Home</a>
        <span class="sep"><i class="fa-solid fa-chevron-right"></i></span>
        <a href="/blog/">Travel Journal</a>
        <span class="sep"><i class="fa-solid fa-chevron-right"></i></span>
        <span class="current">${escapeHtml(titleVal)}</span>
      </nav>
    </div>
  </div>
  <article class="article-detail-section section-padding">
    <div class="site-container article-container">
      <header class="article-header">
        <div class="article-category-badge">${escapeHtml(catVal)}</div>
        <h1 class="article-title">${escapeHtml(titleVal)}</h1>
        <div class="article-meta-row">
          <div class="article-author">
            <img src="/assets/logo.jpg" alt="Author" class="author-avatar">
            <div>
              <span class="author-name">Badagry Visitors Guide</span>
              <span class="author-role">Local Historians &amp; Travel Specialists</span>
            </div>
          </div>
          <div class="article-dates">
            <span><i class="fa-regular fa-calendar"></i> Today</span>
            <span><i class="fa-regular fa-clock"></i> ${escapeHtml(readVal)}</span>
            <span><i class="fa-regular fa-eye"></i> Preview Mode</span>
          </div>
        </div>
      </header>
      ${heroHtml}
      <div class="article-body">
        ${contentVal}
      </div>
    </div>
  </article>
</body>
</html>`;

          const blob = new Blob([standaloneHtml], { type: 'text/html' });
          const blobUrl = URL.createObjectURL(blob);
          window.open(blobUrl, '_blank');
        });
      }
    })();

    // Helper to insert text at textarea cursor position
    function insertTextAtCursor(textarea, text) {
      if (!textarea) return;
      const start = textarea.selectionStart;
      const end = textarea.selectionEnd;
      const val = textarea.value;

      textarea.value = val.substring(0, start) + text + val.substring(end);
      textarea.focus();
      textarea.selectionStart = textarea.selectionEnd = start + text.length;
    }

    // Helper for editor toolbar formatting buttons
    function insertTag(tag) {
      const textarea = document.getElementById('postContent');
      if (!textarea) return;
      const start = textarea.selectionStart;
      const end = textarea.selectionEnd;
      const selected = textarea.value.substring(start, end) || 'Text';

      let replacement = '';
      if (tag === 'callout') {
        replacement = `\n<div class="blog-callout">\n  <h4>Important Note</h4>\n  <p>${selected}</p>\n</div>\n`;
      } else {
        replacement = `<${tag}>${selected}</${tag}>`;
      }

      textarea.value = textarea.value.substring(0, start) + replacement + textarea.value.substring(end);
      textarea.focus();
      textarea.selectionStart = start + `<${tag}>`.length;
      textarea.selectionEnd = start + replacement.length - `</${tag}>`.length;
    }
  </script>
  <?php endif; ?>

</body>

</html>

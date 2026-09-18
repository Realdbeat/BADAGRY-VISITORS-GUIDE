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
    // API: Return JSON list of all images for the Media Library
    if ($action === 'get_images_json') {
        header('Content-Type: application/json');
        $imgs = getAllProjectImages();
        $list = [];
        foreach ($imgs as $k => $item) {
            $isLogo = ($item['filename'] === 'logo.jpg');
            $list[] = [
                'filename' => $item['filename'],
                'url' => $isLogo ? '/assets/logo.jpg' : '/assets/images/' . $item['filename'],
                'fullUrl' => $item['url'],
                'relPath' => $isLogo ? 'assets/logo.jpg' : 'assets/images/' . $item['filename'],
                'title' => $item['title'],
                'caption' => $item['caption'],
                'geo' => $item['geo'],
                'mtime' => $item['mtime'] ?? 0
            ];
        }
        usort($list, function($a, $b) {
            return ($b['mtime'] ?? 0) <=> ($a['mtime'] ?? 0);
        });
        echo json_encode(['success' => true, 'images' => $list]);
        exit;
    }

    // API: Ajax Upload Image from Media Library (No page reload)
    if ($action === 'ajax_upload_image' && $_SERVER['REQUEST_METHOD'] === 'POST') {
        header('Content-Type: application/json');
        if (empty($_FILES['image_file']['name'])) {
            echo json_encode(['success' => false, 'error' => 'No image file uploaded.']);
            exit;
        }
        $file = $_FILES['image_file'];
        $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        $allowed = ['jpg', 'jpeg', 'png', 'webp'];
        if (!in_array($ext, $allowed)) {
            echo json_encode(['success' => false, 'error' => 'Only JPG, PNG, and WebP images are supported.']);
            exit;
        }
        if ($file['error'] !== UPLOAD_ERR_OK) {
            echo json_encode(['success' => false, 'error' => 'Upload error code: ' . $file['error']]);
            exit;
        }

        $baseName = pathinfo($file['name'], PATHINFO_FILENAME);
        $safeName = preg_replace('/[^A-Za-z0-9_-]+/', '-', $baseName);
        $finalName = $safeName . '.' . $ext;
        $targetFile = __DIR__ . '/assets/images/' . $finalName;

        if (file_exists($targetFile)) {
            $finalName = $safeName . '-' . date('Ymd-His') . '.' . $ext;
            $targetFile = __DIR__ . '/assets/images/' . $finalName;
        }

        if (move_uploaded_file($file['tmp_name'], $targetFile)) {
            $sitemapRes = generateSitemaps();
            $title = humanizeImageFilename($finalName);
            echo json_encode([
                'success' => true,
                'filename' => $finalName,
                'relPath' => 'assets/images/' . $finalName,
                'url' => '/assets/images/' . $finalName,
                'title' => $title,
                'caption' => $title . ' - Authentic Badagry tour experience.',
                'geo' => 'Badagry, Lagos State, Nigeria',
                'sitemap_images' => $sitemapRes['images_count']
            ]);
        } else {
            echo json_encode(['success' => false, 'error' => 'Failed to move uploaded file to assets/images.']);
        }
        exit;
    }

    // Manually Trigger Sitemap Regeneration
    if ($action === 'update_sitemaps') {
        $sitemapRes = generateSitemaps();
        header('Location: /admin?msg=sitemap_updated&imgs=' . $sitemapRes['images_count'] . '&posts=' . $sitemapRes['posts_count']);
        exit;
    }

    // Direct Image Upload Handler
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['upload_image'])) {
        if (!empty($_FILES['image_file']['name'])) {
            $file = $_FILES['image_file'];
            $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
            $allowed = ['jpg', 'jpeg', 'png', 'webp'];

            if (!in_array($ext, $allowed)) {
                $error = 'Invalid image format. Only JPG, JPEG, PNG, and WebP images are allowed.';
            } elseif ($file['error'] !== UPLOAD_ERR_OK) {
                $error = 'Upload error (code: ' . $file['error'] . '). Please try again.';
            } else {
                $baseName = pathinfo($file['name'], PATHINFO_FILENAME);
                $safeName = preg_replace('/[^A-Za-z0-9_-]+/', '-', $baseName);
                $finalName = $safeName . '.' . $ext;
                $targetFile = __DIR__ . '/assets/images/' . $finalName;

                if (file_exists($targetFile)) {
                    $finalName = $safeName . '-' . date('Ymd-His') . '.' . $ext;
                    $targetFile = __DIR__ . '/assets/images/' . $finalName;
                }

                if (move_uploaded_file($file['tmp_name'], $targetFile)) {
                    // Automatically update sitemaps with the new image
                    $sitemapRes = generateSitemaps();
                    $relPath = 'assets/images/' . $finalName;
                    $success = "Image '{$finalName}' uploaded successfully! Sitemaps automatically updated ({$sitemapRes['images_count']} total images indexed). Path: <code>{$relPath}</code>";
                } else {
                    $error = 'Failed to save uploaded image to assets/images.';
                }
            }
        } else {
            $error = 'Please select an image file to upload.';
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

  <link rel="stylesheet" href="/style.css" />
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

      <!-- Sitemap Status & Quick Image Uploader Widget -->
      <?php if ($action === 'list'): ?>
        <div class="admin-card" style="margin-bottom: 24px; padding: 20px 24px;">
          <div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 16px;">
            <div>
              <h4 style="margin: 0 0 6px 0; font-size: 1.1rem;"><i class="fa-solid fa-sitemap" style="color: var(--primary);"></i> Automated Sitemap &amp; Image Sitemap Sync</h4>
              <p style="margin: 0; font-size: 0.9rem; color: var(--text-muted);">
                Sitemaps update automatically whenever posts or images are added.
                <a href="/sitemap.xml" target="_blank" style="margin-left: 8px; color: var(--primary); font-weight: 600;"><i class="fa-solid fa-arrow-up-right-from-square"></i> sitemap.xml</a>
                <span style="margin: 0 6px; color: #cbd5e1;">|</span>
                <a href="/image-sitemap.xml" target="_blank" style="color: var(--primary); font-weight: 600;"><i class="fa-solid fa-arrow-up-right-from-square"></i> image-sitemap.xml</a>
              </p>
            </div>
            <form method="POST" action="/admin" enctype="multipart/form-data" style="display: flex; gap: 10px; align-items: center;">
              <input type="hidden" name="upload_image" value="1" />
              <label for="adminImageUpload" class="btn btn-outline-dark" style="cursor: pointer; margin: 0; display: inline-flex; align-items: center; gap: 8px;">
                <i class="fa-solid fa-cloud-arrow-up"></i> Upload New Image
              </label>
              <input type="file" id="adminImageUpload" name="image_file" accept=".jpg,.jpeg,.png,.webp" style="display: none;" onchange="this.form.submit()" />
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
                <label>Featured Image</label>
                <input type="hidden" id="postImage" name="featured_image"
                  value="<?= $editPost ? htmlspecialchars($editPost['featured_image']) : 'assets/images/Badagry-Heritage-Museum-1-420x347.jpg' ?>" />
                <div class="featured-image-picker">
                  <div class="featured-thumb-wrap" id="featuredThumbWrap">
                    <img id="featuredThumbImg" src="/<?= ltrim(htmlspecialchars($editPost['featured_image'] ?? 'assets/images/Badagry-Heritage-Museum-1-420x347.jpg'), '/') ?>" alt="Featured preview" />
                  </div>
                  <div class="featured-picker-actions">
                    <button type="button" class="btn btn-sm btn-primary" id="openFeaturedMediaBtn">
                      <i class="fa-solid fa-photo-film"></i> Select from Library / Upload
                    </button>
                    <span id="featuredImgPathText" style="font-size: 0.8rem; color: var(--text-muted); word-break: break-all;">
                      <?= htmlspecialchars($editPost['featured_image'] ?? 'assets/images/Badagry-Heritage-Museum-1-420x347.jpg') ?>
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
                  <i class="fa-solid fa-images"></i> Insert Image from Library
                </button>
                <button type="button" class="btn-tool" onclick="insertTag('h2')" title="Insert Subheading">H2</button>
                <button type="button" class="btn-tool" onclick="insertTag('h3')" title="Insert Sub-subheading">H3</button>
                <button type="button" class="btn-tool" onclick="insertTag('strong')" title="Bold text"><b>B</b></button>
                <button type="button" class="btn-tool" onclick="insertTag('p')" title="Paragraph">&lt;p&gt;</button>
                <button type="button" class="btn-tool" onclick="insertTag('callout')" title="Highlighted callout box"><i class="fa-solid fa-quote-left"></i> Callout</button>
              </div>
              <textarea id="postContent" name="content" class="form-textarea has-toolbar" rows="14" required
                placeholder="Write your article content here. Click 'Insert Image from Library' above to insert images anywhere in the text..."><?= $editPost ? htmlspecialchars($editPost['content']) : '' ?></textarea>
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

            <div class="admin-form-actions">
              <button type="submit" class="btn btn-primary"><?= $action === 'edit' ? 'Update Article' : 'Publish Article' ?> <i class="fa-solid fa-check"></i></button>
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
          <i class="fa-solid fa-images"></i> <span id="mediaTotalCount">Existing (0)</span>
        </button>
        <button type="button" class="media-tab-btn" id="tabBtnUpload" data-tab="upload">
          <i class="fa-solid fa-cloud-arrow-up"></i> Upload New
        </button>
      </div>

      <div class="media-modal-body">
        <!-- Tab 1: Browse Existing Images -->
        <div class="media-tab-content active" id="tabContentBrowse">
          <div class="media-browse-pane">
            <div class="media-grid-wrap">
              <div class="media-grid-filter">
                <i class="fa-solid fa-magnifying-glass" style="color: #94a3b8;"></i>
                <input type="text" id="mediaSearchInput" class="media-search-input" placeholder="Search photos by name (e.g. slave, museum, building, beach)..." />
                <button type="button" class="btn btn-sm btn-outline-dark" id="refreshMediaListBtn" title="Refresh library">
                  <i class="fa-solid fa-arrows-rotate"></i>
                </button>
              </div>
              <div class="media-grid" id="mediaGrid">
                <!-- Image items populated via JS -->
              </div>
            </div>

            <div class="media-details-sidebar" id="mediaSidebar">
              <h4 style="margin: 0 0 12px 0; font-size: 1rem;">Selected Image</h4>
              <img id="mediaSidebarPreview" src="" alt="" class="media-preview-big" style="display: none;" />
              
              <div id="mediaSidebarEmptyNotice" style="color: var(--text-muted); font-size: 0.9rem; text-align: center; padding: 36px 0;">
                <i class="fa-regular fa-image" style="font-size: 2.5rem; display: block; margin-bottom: 8px; color: #cbd5e1;"></i>
                Select an image from the grid or upload a new photo.
              </div>

              <div id="mediaSidebarDetails" style="display: none;">
                <div class="media-meta-text">
                  <strong id="mediaSidebarFilename" style="color: var(--dark);"></strong><br />
                  <span id="mediaSidebarPath" style="font-size: 0.78rem; color: #64748b;"></span>
                </div>

                <!-- Fields for Content Insertion Mode -->
                <div id="mediaContentOptions" style="display: none;">
                  <div class="form-group" style="margin-bottom: 12px;">
                    <label style="font-size: 0.85rem; margin-bottom: 4px; font-weight: 600;">Caption (Displayed under photo)</label>
                    <input type="text" id="mediaInputCaption" class="form-input" style="padding: 8px 10px; font-size: 0.88rem;" placeholder="e.g. Historic slave chains at the museum" />
                  </div>
                  <div class="form-group" style="margin-bottom: 12px;">
                    <label style="font-size: 0.85rem; margin-bottom: 4px; font-weight: 600;">Alt Description (SEO)</label>
                    <input type="text" id="mediaInputAlt" class="form-input" style="padding: 8px 10px; font-size: 0.88rem;" placeholder="Describe what is visible in image" />
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

        <!-- Tab 2: Upload New Image -->
        <div class="media-tab-content" id="tabContentUpload">
          <div class="media-upload-pane">
            <div class="media-dropzone" id="mediaDropzone">
              <i class="fa-solid fa-cloud-arrow-up"></i>
              <h3 style="margin: 0 0 8px 0; font-size: 1.25rem;">Drag &amp; Drop Photo Here</h3>
              <p style="margin: 0 0 16px 0; color: var(--text-muted); font-size: 0.95rem;">or click to select from your device (JPG, PNG, WebP)</p>
              <input type="file" id="mediaFileInput" accept=".jpg,.jpeg,.png,.webp" style="display: none;" />
              <button type="button" class="btn btn-primary" onclick="document.getElementById('mediaFileInput').click()">
                <i class="fa-solid fa-folder-open"></i> Browse Photos
              </button>
            </div>
            <div class="upload-status-box" id="uploadStatusBox"></div>
          </div>
        </div>
      </div>

      <div class="media-modal-footer">
        <button type="button" class="btn btn-outline-dark" id="mediaCancelBtn">Cancel</button>
        <button type="button" class="btn btn-primary" id="mediaConfirmActionBtn" disabled>
          <span id="mediaConfirmActionText">Select Image</span>
        </button>
      </div>
    </div>
  </div>

  <!-- Media Library Interactive Script -->
  <script>
    (function() {
      let currentModalMode = 'featured'; // 'featured' or 'content'
      let allLoadedImages = [];
      let selectedImage = null;

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

      const sidebarPreview = document.getElementById('mediaSidebarPreview');
      const sidebarEmptyNotice = document.getElementById('mediaSidebarEmptyNotice');
      const sidebarDetails = document.getElementById('mediaSidebarDetails');
      const sidebarFilename = document.getElementById('mediaSidebarFilename');
      const sidebarPath = document.getElementById('mediaSidebarPath');
      const contentOptions = document.getElementById('mediaContentOptions');
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

      function openModal(mode) {
        currentModalMode = mode;
        modalOverlay.classList.remove('modal-mode-featured', 'modal-mode-content');
        modalOverlay.classList.add('modal-mode-' + mode);

        const sidebar = document.getElementById('mediaSidebar');
        if (sidebar) sidebar.classList.remove('is-active');

        selectedImage = null;
        confirmBtn.disabled = true;

        if (mode === 'featured') {
          modalHeading.textContent = 'Choose Featured Image';
          confirmText.textContent = 'Set as Featured Image';
          contentOptions.style.display = 'none';
        } else {
          modalHeading.textContent = 'Insert Image into Article';
          confirmText.textContent = 'Insert into Article';
          contentOptions.style.display = 'block';
        }

        modalOverlay.classList.add('active');
        switchTab('browse');
        if (allLoadedImages.length === 0) {
          fetchImages();
        }
      }

      function closeModal() {
        modalOverlay.classList.remove('active');
        const sidebar = document.getElementById('mediaSidebar');
        if (sidebar) sidebar.classList.remove('is-active');
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

      // Fetch images via JSON API
      function fetchImages(selectFilenameOnDone) {
        mediaGrid.innerHTML = '<div style="padding: 24px; color: #64748b; font-size: 0.95rem; grid-column: 1/-1; text-align: center;"><i class="fa-solid fa-spinner fa-spin"></i> Loading library images...</div>';
        fetch('/admin?action=get_images_json')
          .then(res => res.json())
          .then(data => {
            if (data.success && data.images) {
              allLoadedImages = data.images;
              totalCountSpan.textContent = "Existing (" + allLoadedImages.length + ")";
              renderGrid(allLoadedImages);
              if (selectFilenameOnDone) {
                const found = allLoadedImages.find(img => img.filename === selectFilenameOnDone);
                if (found) selectImage(found);
              }
            } else {
              mediaGrid.innerHTML = '<div style="color: #ef4444; padding: 20px; grid-column: 1/-1; text-align: center;">Failed to load library images.</div>';
            }
          })
          .catch(err => {
            console.error(err);
            mediaGrid.innerHTML = '<div style="color: #ef4444; padding: 20px; grid-column: 1/-1; text-align: center;">Network error loading image library.</div>';
          });
      }

      if (refreshBtn) {
        refreshBtn.addEventListener('click', () => fetchImages());
      }

      // Render grid cards
      function renderGrid(images) {
        if (images.length === 0) {
          mediaGrid.innerHTML = '<div style="padding: 30px; color: #64748b; font-size: 0.95rem; grid-column: 1/-1; text-align: center;">No matching images found. Click "Upload New" above to add photos.</div>';
          return;
        }

        mediaGrid.innerHTML = '';
        images.forEach(img => {
          const item = document.createElement('div');
          item.className = 'media-item' + (selectedImage && selectedImage.filename === img.filename ? ' selected' : '');
          item.dataset.filename = img.filename;
          item.title = img.filename;
          const cleanName = escapeHtml(img.filename);
          item.innerHTML = `
            <img src="${img.url}" alt="${cleanName}" loading="lazy" onerror="this.onerror=null;this.src='/assets/images/' + encodeURIComponent('${img.filename}');" />
            <span class="select-check"><i class="fa-solid fa-check"></i></span>
            <span class="media-item-name">${cleanName}</span>
          `;

          item.addEventListener('click', () => selectImage(img));
          item.addEventListener('dblclick', () => {
            selectImage(img);
            confirmBtn.click();
          });
          mediaGrid.appendChild(item);
        });
      }

      function selectImage(img) {
        selectedImage = img;
        confirmBtn.disabled = false;

        document.querySelectorAll('.media-item').forEach(el => {
          el.classList.toggle('selected', el.dataset.filename === img.filename);
        });

        const sidebar = document.getElementById('mediaSidebar');
        if (sidebar) sidebar.classList.add('is-active');

        sidebarEmptyNotice.style.display = 'none';
        sidebarPreview.style.display = 'block';
        sidebarDetails.style.display = 'block';

        sidebarPreview.src = img.url;
        sidebarFilename.textContent = img.filename;
        sidebarPath.textContent = 'assets/images/' + img.filename;

        if (inputAlt) inputAlt.value = img.title || '';
        if (inputCaption) inputCaption.value = img.title || '';
      }

      // Search filter
      if (searchInput) {
        searchInput.addEventListener('input', (e) => {
          const q = e.target.value.toLowerCase().trim();
          if (!q) {
            renderGrid(allLoadedImages);
            return;
          }
          const filtered = allLoadedImages.filter(img => 
            img.filename.toLowerCase().includes(q) || 
            (img.title && img.title.toLowerCase().includes(q))
          );
          renderGrid(filtered);
        });
      }

      // Handle File Upload via AJAX
      function handleFileUpload(file) {
        if (!file) return;
        const formData = new FormData();
        formData.append('image_file', file);

        uploadStatusBox.innerHTML = '<span style="color: var(--primary);"><i class="fa-solid fa-spinner fa-spin"></i> Uploading photo and synchronizing sitemaps...</span>';

        fetch('/admin?action=ajax_upload_image', {
          method: 'POST',
          body: formData
        })
        .then(res => res.json())
        .then(data => {
          if (data.success) {
            uploadStatusBox.innerHTML = '<span style="color: #166534;"><i class="fa-solid fa-circle-check"></i> Uploaded: ' + escapeHtml(data.filename) + '! Sitemaps updated (' + data.sitemap_images + ' total images indexed).</span>';
            // Refresh library and auto-select this image
            switchTab('browse');
            fetchImages(data.filename);
          } else {
            uploadStatusBox.innerHTML = '<span style="color: #991b1b;"><i class="fa-solid fa-circle-exclamation"></i> ' + escapeHtml(data.error || 'Upload failed.') + '</span>';
          }
        })
        .catch(err => {
          console.error(err);
          uploadStatusBox.innerHTML = '<span style="color: #991b1b;"><i class="fa-solid fa-circle-exclamation"></i> Upload request failed.</span>';
        });
      }

      if (fileInput) {
        fileInput.addEventListener('change', (e) => {
          if (e.target.files && e.target.files[0]) {
            handleFileUpload(e.target.files[0]);
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
          if (e.dataTransfer && e.dataTransfer.files && e.dataTransfer.files[0]) {
            handleFileUpload(e.dataTransfer.files[0]);
          }
        });
      }

      // Confirm Action Button
      confirmBtn.addEventListener('click', () => {
        if (!selectedImage) return;

        if (currentModalMode === 'featured') {
          // Set Featured Image
          const postImageInput = document.getElementById('postImage');
          const thumbImg = document.getElementById('featuredThumbImg');
          const pathText = document.getElementById('featuredImgPathText');

          const relPath = 'assets/images/' + selectedImage.filename;
          if (postImageInput) postImageInput.value = relPath;
          if (thumbImg) thumbImg.src = selectedImage.url;
          if (pathText) pathText.textContent = relPath;

          closeModal();
        } else {
          // Insert into Article Content
          const contentTextarea = document.getElementById('postContent');
          if (!contentTextarea) return;

          const altText = (inputAlt && inputAlt.value.trim()) || selectedImage.title || '';
          const captionText = (inputCaption && inputCaption.value.trim()) || '';
          const align = (inputAlign && inputAlign.value) || 'center';

          let snippet = '';
          if (captionText) {
            snippet = `\n<figure class="img-${align}">\n  <img src="/assets/images/${selectedImage.filename}" alt="${escapeHtml(altText)}" loading="lazy" />\n  <figcaption>${escapeHtml(captionText)}</figcaption>\n</figure>\n`;
          } else {
            snippet = `\n<figure class="img-${align}">\n  <img src="/assets/images/${selectedImage.filename}" alt="${escapeHtml(altText)}" loading="lazy" />\n</figure>\n`;
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

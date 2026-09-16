<?php
session_start();
require_once __DIR__ . '/db.php';
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
    // Delete Post
    if ($action === 'delete' && isset($_GET['id'])) {
        $id = (int)$_GET['id'];
        $stmt = $db->prepare("DELETE FROM posts WHERE id = :id");
        $stmt->execute([':id' => $id]);
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
                    $success = 'Article updated successfully!';
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
                    $success = 'New article published successfully!';
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
        <div class="brand-text">
          <span class="logo-main">BADAGRY <strong>VISITORS GUIDE</strong></span>
          <span class="logo-sub">Blog Management System</span>
        </div>
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
            <label for="login_pin">Security PIN (Default: <code>badagry2026</code>)</label>
            <input type="password" id="login_pin" name="login_pin" class="form-input" placeholder="Enter PIN" required autofocus />
          </div>
          <button type="submit" class="btn btn-primary w-100">Unlock Dashboard <i class="fa-solid fa-unlock"></i></button>
        </form>
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
            <a href="/admin?action=new" class="btn btn-primary"><i class="fa-solid fa-plus"></i> Write New Article</a>
          <?php else: ?>
            <a href="/admin" class="btn btn-outline-dark"><i class="fa-solid fa-list"></i> Back to All Articles</a>
          <?php endif; ?>
        </div>
      </div>

      <?php if (isset($_GET['msg']) && $_GET['msg'] === 'deleted'): ?>
        <div class="admin-alert alert-success">Article was deleted successfully.</div>
      <?php endif; ?>
      <?php if ($success): ?>
        <div class="admin-alert alert-success"><?= htmlspecialchars($success) ?></div>
      <?php endif; ?>
      <?php if ($error): ?>
        <div class="admin-alert alert-error"><?= htmlspecialchars($error) ?></div>
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
              <div class="form-group">
                <label for="postImage">Featured Image URL / Path</label>
                <input type="text" id="postImage" name="featured_image" class="form-input"
                  value="<?= $editPost ? htmlspecialchars($editPost['featured_image']) : 'assets/images/Badagry-Heritage-Museum-1-420x347.jpg' ?>"
                  placeholder="e.g. assets/images/your-image.jpg" />
              </div>
              <div class="form-group">
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
              <label for="postContent">Article Body (Supports HTML tags: &lt;h2&gt;, &lt;p&gt;, &lt;ul&gt;, &lt;strong&gt;, etc.) *</label>
              <textarea id="postContent" name="content" class="form-textarea" rows="12" required
                placeholder="Write your article content here..."><?= $editPost ? htmlspecialchars($editPost['content']) : '' ?></textarea>
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
        <!-- Posts List Table -->
        <div class="admin-card">
          <div class="admin-table-responsive">
            <table class="admin-table">
              <thead>
                <tr>
                  <th>ID</th>
                  <th>Image</th>
                  <th>Title</th>
                  <th>Category</th>
                  <th>Views</th>
                  <th>Date</th>
                  <th>Actions</th>
                </tr>
              </thead>
              <tbody>
                <?php foreach ($allPosts as $p): ?>
                  <tr>
                    <td><?= $p['id'] ?></td>
                    <td>
                      <img src="/<?= ltrim(htmlspecialchars($p['featured_image'] ?: 'assets/images/Point-of-No-Return-420x347.jpg'), '/') ?>"
                        alt="" class="admin-table-thumb" />
                    </td>
                    <td>
                      <strong><a href="/blog/<?= urlencode($p['slug']) ?>" target="_blank"><?= htmlspecialchars($p['title']) ?></a></strong>
                      <br />
                      <small class="text-muted">/blog/<?= htmlspecialchars($p['slug']) ?></small>
                    </td>
                    <td><span class="badge-tag"><?= htmlspecialchars($p['category']) ?></span></td>
                    <td><?= number_format($p['views']) ?></td>
                    <td><?= date('M d, Y', strtotime($p['published_at'])) ?></td>
                    <td class="admin-actions-cell">
                      <a href="/blog/<?= urlencode($p['slug']) ?>" target="_blank" class="btn-icon" title="View Article"><i class="fa-solid fa-eye"></i></a>
                      <a href="/admin?action=edit&id=<?= $p['id'] ?>" class="btn-icon" title="Edit Article"><i class="fa-solid fa-pen"></i></a>
                      <a href="/admin?action=delete&id=<?= $p['id'] ?>" class="btn-icon delete-btn" title="Delete Article"
                        onclick="return confirm('Are you sure you want to delete this article?');"><i class="fa-solid fa-trash"></i></a>
                    </td>
                  </tr>
                <?php endforeach; ?>
              </tbody>
            </table>
          </div>
        </div>
      <?php endif; ?>

    <?php endif; ?>
  </main>

</body>

</html>

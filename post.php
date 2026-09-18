<?php
require_once __DIR__ . '/db.php';
require_once __DIR__ . '/sitemap-generator.php';
autoUpdateSitemapsIfModified(60);
$db = getDB();

$slug = trim($_GET['slug'] ?? '');
$id = (int)($_GET['id'] ?? 0);

// Auto-detect slug from request URI if accessed directly as /blog/{slug}
if ($slug === '' && $id === 0) {
    $uriPath = trim(parse_url($_SERVER['REQUEST_URI'] ?? '', PHP_URL_PATH), '/');
    if (preg_match('#^blog/([a-zA-Z0-9_-]+)$#', $uriPath, $m)) {
        $slug = $m[1];
    }
}

$post = null;
if ($slug !== '') {
    $stmt = $db->prepare("SELECT * FROM posts WHERE slug = :slug AND status = 'published' LIMIT 1");
    $stmt->execute([':slug' => $slug]);
    $post = $stmt->fetch();
} elseif ($id > 0) {
    $stmt = $db->prepare("SELECT * FROM posts WHERE id = :id AND status = 'published' LIMIT 1");
    $stmt->execute([':id' => $id]);
    $post = $stmt->fetch();
}

if ($post) {
    // Increment views
    $db->prepare("UPDATE posts SET views = views + 1 WHERE id = :id")->execute([':id' => $post['id']]);

    // Fetch 3 related posts (excluding current)
    $relStmt = $db->prepare("
        SELECT id, title, slug, excerpt, featured_image, category, read_time, published_at 
        FROM posts 
        WHERE id != :id AND status = 'published' 
        ORDER BY (category = :cat) DESC, published_at DESC 
        LIMIT 3
    ");
    $relStmt->execute([':id' => $post['id'], ':cat' => $post['category']]);
    $relatedPosts = $relStmt->fetchAll();
} else {
    http_response_code(404);
}

// Clean Pretty URL for Google SEO & Social Sharing
$cleanSlug = $post['slug'] ?? '';
$pageUrl = "https://badagryvisitorsguide.rav.com.ng/blog/" . urlencode($cleanSlug);
$imageUrl = "https://badagryvisitorsguide.rav.com.ng/" . ($post['featured_image'] ?? 'assets/images/Point-of-No-Return-420x347.jpg');
?>
<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <?php if ($post): ?>
    <title><?= htmlspecialchars($post['title']) ?> | Badagry Visitors Guide</title>
    <meta name="description" content="<?= htmlspecialchars($post['excerpt']) ?>" />
    <link rel="canonical" href="<?= $pageUrl ?>" />
    <meta name="robots" content="index, follow, max-image-preview:large" />

    <!-- Open Graph -->
    <meta property="og:type" content="article" />
    <meta property="og:site_name" content="Badagry Visitors Guide" />
    <meta property="og:title" content="<?= htmlspecialchars($post['title']) ?>" />
    <meta property="og:description" content="<?= htmlspecialchars($post['excerpt']) ?>" />
    <meta property="og:url" content="<?= $pageUrl ?>" />
    <meta property="og:image" content="<?= $imageUrl ?>" />
    <meta property="article:published_time" content="<?= date('c', strtotime($post['published_at'])) ?>" />
    <meta property="article:section" content="<?= htmlspecialchars($post['category']) ?>" />

    <!-- Twitter Card -->
    <meta name="twitter:card" content="summary_large_image" />
    <meta name="twitter:title" content="<?= htmlspecialchars($post['title']) ?>" />
    <meta name="twitter:description" content="<?= htmlspecialchars($post['excerpt']) ?>" />
    <meta name="twitter:image" content="<?= $imageUrl ?>" />

    <!-- Schema.org BlogPosting -->
    <script type="application/ld+json">
    {
      "@context": "https://schema.org",
      "@type": "BlogPosting",
      "headline": <?= json_encode($post['title']) ?>,
      "description": <?= json_encode($post['excerpt']) ?>,
      "image": <?= json_encode($imageUrl) ?>,
      "datePublished": <?= json_encode(date('c', strtotime($post['published_at']))) ?>,
      "author": {
        "@type": "Organization",
        "name": <?= json_encode($post['author']) ?>,
        "url": "https://badagryvisitorsguide.rav.com.ng/"
      },
      "publisher": {
        "@type": "Organization",
        "name": "Badagry Visitors Guide",
        "url": "https://badagryvisitorsguide.rav.com.ng/",
        "logo": "https://badagryvisitorsguide.rav.com.ng/assets/logo.jpg"
      },
      "mainEntityOfPage": {
        "@type": "WebPage",
        "@id": <?= json_encode($pageUrl) ?>
      }
    }
    </script>
  <?php else: ?>
    <title>Article Not Found | Badagry Visitors Guide</title>
  <?php endif; ?>

  <link rel="icon" type="image/jpeg" href="/assets/logo.jpg" />

  <!-- Google Fonts: Jost & Inter -->
  <link rel="preconnect" href="https://fonts.googleapis.com" />
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
  <link
    href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&family=Jost:wght@400;500;600;700;800&display=swap"
    rel="stylesheet" />

  <!-- Font Awesome 6 Icons -->
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" />

  <link rel="stylesheet" href="/style.css" />
</head>

<body>

  <!-- Mobile Offcanvas Menu Drawer -->
  <div class="mobile-menu-overlay" id="mobileMenuOverlay"></div>
  <aside class="mobile-menu-drawer" id="mobileMenuDrawer">
    <div class="drawer-header">
      <a href="/" class="drawer-logo">
        <img src="/assets/logo.jpg" alt="Badagry Visitors Guide" class="drawer-logo-img" />
        <span class="drawer-logo-text">Badagry <strong>Visitors Guide</strong></span>
      </a>
      <button class="drawer-close-btn" id="drawerCloseBtn" aria-label="Close menu">
        <i class="fa-solid fa-xmark"></i>
      </button>
    </div>
    <div class="drawer-content">
      <ul class="mobile-nav-list">
        <li><a href="/" class="mobile-nav-link">Home</a></li>
        <li><a href="/#about" class="mobile-nav-link">About Us</a></li>
        <li><a href="/#tours" class="mobile-nav-link">Tours</a></li>
        <li><a href="/blog/" class="mobile-nav-link active">Journal</a></li>
        <li><a href="/admin" class="mobile-nav-link"><i class="fa-solid fa-lock"></i> Author Login</a></li>
        <li><a href="http://wa.me/+2347056989224?text=from+google+map+or+site" target="_blank" rel="noopener"
            class="mobile-nav-link">Contact Us</a></li>
      </ul>
      <div class="mobile-drawer-cta">
        <a href="http://wa.me/+2347056989224?text=from+google+map+or+site" target="_blank" rel="noopener"
          class="btn btn-primary w-100">Book Your Tour</a>
      </div>
    </div>
  </aside>

  <!-- Top Announcement Bar -->
  <header class="header-wrapper">
    <div class="top-bar">
      <div class="site-container top-bar-container">
        <div class="top-bar-left">
          <span class="info-item">
            <i class="fa-solid fa-location-dot"></i> Badagry, Lagos, Nigeria.
          </span>
          <span class="info-item">
            <i class="fa-regular fa-clock"></i> 9AM – 6PM WAT
          </span>
        </div>
        <div class="top-bar-right">
          <div class="top-links">
            <a href="/blog/">All Articles</a>
            <span class="divider">|</span>
            <a href="/admin"><i class="fa-solid fa-pen-to-square"></i> Admin / Write</a>
            <span class="divider">|</span>
            <a href="http://wa.me/+2347056989224?text=from+google+map+or+site" target="_blank" rel="noopener">WhatsApp Support</a>
          </div>
        </div>
      </div>
    </div>

    <!-- Main Navigation Bar -->
    <nav class="main-navbar" id="mainNavbar">
      <div class="site-container nav-container">
        <a href="/" class="brand-logo">
          <img src="/assets/logo.jpg" alt="Badagry Visitors Guide Logo" class="brand-logo-img" />
          <div class="brand-text">
            <span class="logo-main">BADAGRY <strong>VISITORS GUIDE</strong></span>
            <span class="logo-sub">Heritage &amp; Cultural Tours</span>
          </div>
        </a>

        <ul class="nav-menu">
          <li class="nav-item"><a href="/" class="nav-link">Home</a></li>
          <li class="nav-item"><a href="/#about" class="nav-link">About Us</a></li>
          <li class="nav-item"><a href="/#tours" class="nav-link">Tours</a></li>
          <li class="nav-item"><a href="/blog/" class="nav-link active">Journal</a></li>
          <li class="nav-item"><a href="http://wa.me/+2347056989224?text=from+google+map+or+site" target="_blank"
              rel="noopener" class="nav-link">Contact</a></li>
        </ul>

        <div class="nav-actions">
          <a href="http://wa.me/+2347056989224?text=from+google+map+or+site" target="_blank" rel="noopener"
            class="btn btn-primary nav-book-btn">
            <span>Book Tour</span>
            <i class="fa-solid fa-arrow-right"></i>
          </a>
          <button class="mobile-toggle-btn" id="mobileToggleBtn" aria-label="Open mobile menu">
            <i class="fa-solid fa-bars"></i>
          </button>
        </div>
      </div>
    </nav>
  </header>

  <?php if (!$post): ?>
    <section class="section-padding">
      <div class="site-container text-center">
        <div class="blog-empty-state">
          <i class="fa-solid fa-circle-question empty-icon"></i>
          <h2>Article Not Found</h2>
          <p>The travel article you are looking for does not exist or has been moved.</p>
          <a href="/blog/" class="btn btn-primary mt-3"><i class="fa-solid fa-arrow-left"></i> Return to Travel Journal</a>
        </div>
      </div>
    </section>
  <?php else: ?>
    <!-- Breadcrumb -->
    <div class="blog-breadcrumb-bar">
      <div class="site-container">
        <nav class="breadcrumb-nav">
          <a href="/">Home</a>
          <span class="sep"><i class="fa-solid fa-chevron-right"></i></span>
          <a href="/blog/">Travel Journal</a>
          <span class="sep"><i class="fa-solid fa-chevron-right"></i></span>
          <span class="current"><?= htmlspecialchars($post['title']) ?></span>
        </nav>
      </div>
    </div>

    <!-- Article Content -->
    <article class="article-detail-section section-padding">
      <div class="site-container article-container">
        <!-- Article Header -->
        <header class="article-header">
          <div class="article-category-badge"><?= htmlspecialchars($post['category']) ?></div>
          <h1 class="article-title"><?= htmlspecialchars($post['title']) ?></h1>
          
          <div class="article-meta-row">
            <div class="article-author">
              <img src="/assets/logo.jpg" alt="<?= htmlspecialchars($post['author']) ?>" class="author-avatar" />
              <div>
                <span class="author-name"><?= htmlspecialchars($post['author']) ?></span>
                <span class="author-role">Local Historians &amp; Travel Specialists</span>
              </div>
            </div>
            <div class="article-dates">
              <span><i class="fa-regular fa-calendar"></i> <?= date('F d, Y', strtotime($post['published_at'])) ?></span>
              <span><i class="fa-regular fa-clock"></i> <?= htmlspecialchars($post['read_time']) ?></span>
              <span><i class="fa-regular fa-eye"></i> <?= number_format($post['views']) ?> views</span>
            </div>
          </div>
        </header>

        <!-- Featured Hero Image -->
        <?php if (!empty($post['featured_image'])): ?>
          <div class="article-hero-img-wrap">
            <img src="/<?= ltrim(htmlspecialchars($post['featured_image']), '/') ?>" alt="<?= htmlspecialchars($post['title']) ?>" class="article-hero-img" />
          </div>
        <?php endif; ?>

        <!-- Article Body -->
        <div class="article-body">
          <?= $post['content'] ?>
        </div>

        <!-- Social Share Bar -->
        <div class="article-share-box">
          <span class="share-label"><i class="fa-solid fa-share-nodes"></i> Share this guide:</span>
          <div class="share-buttons">
            <a href="https://api.whatsapp.com/send?text=<?= urlencode($post['title'] . ' ' . $pageUrl) ?>" target="_blank" rel="noopener"
              class="share-btn share-wa" aria-label="Share on WhatsApp">
              <i class="fa-brands fa-whatsapp"></i> WhatsApp
            </a>
            <a href="https://twitter.com/intent/tweet?text=<?= urlencode($post['title']) ?>&url=<?= urlencode($pageUrl) ?>" target="_blank" rel="noopener"
              class="share-btn share-tw" aria-label="Share on X Twitter">
              <i class="fa-brands fa-x-twitter"></i> X / Twitter
            </a>
            <a href="https://www.facebook.com/sharer/sharer.php?u=<?= urlencode($pageUrl) ?>" target="_blank" rel="noopener"
              class="share-btn share-fb" aria-label="Share on Facebook">
              <i class="fa-brands fa-facebook-f"></i> Facebook
            </a>
            <button onclick="navigator.clipboard.writeText('<?= $pageUrl ?>'); alert('Link copied to clipboard!');"
              class="share-btn share-copy" aria-label="Copy Link">
              <i class="fa-solid fa-link"></i> Copy Link
            </button>
          </div>
        </div>

        <!-- Tour Booking Box Callout -->
        <div class="article-cta-box">
          <div class="cta-box-icon"><i class="fa-solid fa-compass"></i></div>
          <div class="cta-box-text">
            <h3>Want to visit these historical landmarks with an indigenous guide?</h3>
            <p>We personalize day trips and multi-day tours across Badagry, Benin Republic, and Togo around your schedule and budget.</p>
          </div>
          <a href="http://wa.me/+2347056989224?text=<?= urlencode('Hello, I read the article "' . $post['title'] . '" and would like to plan a tour.') ?>"
            target="_blank" rel="noopener" class="btn btn-primary">
            Plan My Tour <i class="fa-solid fa-arrow-right"></i>
          </a>
        </div>

        <!-- Related Articles Section -->
        <?php if (!empty($relatedPosts)): ?>
          <div class="article-related-section">
            <h3 class="related-title">More Stories &amp; Guides</h3>
            <div class="blog-grid related-grid">
              <?php foreach ($relatedPosts as $rel): ?>
                <article class="blog-card">
                  <div class="blog-card-img-wrap">
                    <a href="/blog/<?= urlencode($rel['slug']) ?>">
                      <img src="/<?= ltrim(htmlspecialchars($rel['featured_image'] ?: 'assets/images/Point-of-No-Return-420x347.jpg'), '/') ?>"
                        alt="<?= htmlspecialchars($rel['title']) ?>" loading="lazy" />
                    </a>
                    <span class="blog-card-badge"><?= htmlspecialchars($rel['category']) ?></span>
                  </div>
                  <div class="blog-card-body">
                    <div class="blog-card-meta">
                      <span><i class="fa-regular fa-calendar"></i> <?= date('M d, Y', strtotime($rel['published_at'])) ?></span>
                      <span><i class="fa-regular fa-clock"></i> <?= htmlspecialchars($rel['read_time']) ?></span>
                    </div>
                    <h4 class="blog-card-title">
                      <a href="/blog/<?= urlencode($rel['slug']) ?>"><?= htmlspecialchars($rel['title']) ?></a>
                    </h4>
                  </div>
                </article>
              <?php endforeach; ?>
            </div>
          </div>
        <?php endif; ?>
      </div>
    </article>
  <?php endif; ?>

  <!-- Footer -->
  <footer class="footer-section">
    <div class="site-container">
      <div class="footer-grid">
        <div class="footer-col">
          <a href="/" class="footer-brand">
            <img src="/assets/logo.jpg" alt="Badagry Visitors Guide" class="footer-logo-img" />
            <div class="brand-text">
              <span class="logo-main">BADAGRY <strong>VISITORS GUIDE</strong></span>
              <span class="logo-sub">Heritage &amp; Cultural Tours</span>
            </div>
          </a>
          <p class="footer-about-text">
            Official Badagry Visitors Guide. Discover Culture, Heritage &amp; Coastal Experiences across Badagry, Benin Republic &amp; Togo.
          </p>
          <div class="footer-social-links">
            <a href="https://www.instagram.com/badagryvisitorsguide" target="_blank" rel="noopener" aria-label="Instagram"><i
                class="fa-brands fa-instagram"></i></a>
            <a href="https://www.tiktok.com/@vezeltour" target="_blank" rel="noopener" aria-label="TikTok"><i
                class="fa-brands fa-tiktok"></i></a>
            <a href="http://wa.me/+2347056989224?text=from+google+map+or+site" target="_blank" rel="noopener"
              aria-label="WhatsApp"><i class="fa-brands fa-whatsapp"></i></a>
            <a href="https://www.facebook.com/share/1EnBcsZgqb/" target="_blank" rel="noopener" aria-label="Facebook"><i
                class="fa-brands fa-facebook-f"></i></a>
            <a href="https://x.com/badagry_v_guide" target="_blank" rel="noopener" aria-label="X (Twitter)"><i
                class="fa-brands fa-x-twitter"></i></a>
            <a href="https://www.linkedin.com/in/ezekiel-viavonu-105a15111/" target="_blank" rel="noopener" aria-label="LinkedIn"><i
                class="fa-brands fa-linkedin-in"></i></a>
          </div>
        </div>

        <div class="footer-col">
          <h3 class="footer-title">Quick Links</h3>
          <ul class="footer-menu">
            <li><a href="/">Home</a></li>
            <li><a href="/#about">About Us</a></li>
            <li><a href="/#tours">Our Tours</a></li>
            <li><a href="/blog/">Our Journal</a></li>
            <li><a href="/admin">Author Login</a></li>
          </ul>
        </div>

        <div class="footer-col">
          <h3 class="footer-title">Get In Touch</h3>
          <div class="footer-contact-items">
            <div class="contact-item">
              <i class="fa-solid fa-phone"></i>
              <div>
                <span class="item-label">Phone &amp; WhatsApp</span>
                <a href="http://wa.me/+2347056989224?text=from+google+map+or+site" target="_blank" rel="noopener">+234 705 698 9224</a>
              </div>
            </div>
            <div class="contact-item">
              <i class="fa-solid fa-envelope"></i>
              <div>
                <span class="item-label">Email Inquiries</span>
                <a href="mailto:info@badagryvisitorsguide.com">info@badagryvisitorsguide.com</a>
              </div>
            </div>
          </div>
        </div>
      </div>

      <div class="footer-bottom">
        <p class="copyright-text">&copy; Copyright 2026 Badagry Visitors Guide Ltd. All Rights Reserved.</p>
      </div>
    </div>
  </footer>

  <script src="/app.js"></script>
</body>

</html>

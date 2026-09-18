<?php
require_once __DIR__ . '/db.php';
require_once __DIR__ . '/sitemap-generator.php';
autoUpdateSitemapsIfModified(60);
$db = getDB();

$search = trim($_GET['q'] ?? '');
$category = trim($_GET['category'] ?? '');

// Fetch distinct categories
$catStmt = $db->query("SELECT DISTINCT category FROM posts WHERE status = 'published' ORDER BY category ASC");
$categories = $catStmt->fetchAll(PDO::FETCH_COLUMN);

// Build query
$sql = "SELECT * FROM posts WHERE status = 'published'";
$params = [];

if ($category !== '') {
    $sql .= " AND category = :category";
    $params[':category'] = $category;
}

if ($search !== '') {
    $sql .= " AND (title LIKE :search OR excerpt LIKE :search OR content LIKE :search)";
    $params[':search'] = '%' . $search . '%';
}

$sql .= " ORDER BY published_at DESC";
$stmt = $db->prepare($sql);
$stmt->execute($params);
$posts = $stmt->fetchAll();

// Total count
$totalPosts = count($posts);
?>
<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>West Africa Travel Journal &amp; Blog | Badagry Visitors Guide</title>
  <meta name="description"
    content="Guides, historical perspectives, border crossing advice, and local travel stories across Badagry, Benin Republic, and Togo by indigenous historians." />
  <link rel="canonical" href="https://badagryvisitorsguide.rav.com.ng/blog/" />
  <meta name="robots" content="index, follow, max-image-preview:large" />

  <!-- Open Graph -->
  <meta property="og:type" content="blog" />
  <meta property="og:site_name" content="Badagry Visitors Guide" />
  <meta property="og:title" content="West Africa Travel Journal &amp; Blog | Badagry Visitors Guide" />
  <meta property="og:description" content="Authentic historical accounts, travel guides, and insider tips for Badagry, Benin Republic, and Togo." />
  <meta property="og:url" content="https://badagryvisitorsguide.rav.com.ng/blog/" />
  <meta property="og:image" content="https://badagryvisitorsguide.rav.com.ng/assets/images/Point-of-No-Return-420x347.jpg" />

  <!-- Twitter Card -->
  <meta name="twitter:card" content="summary_large_image" />
  <meta name="twitter:title" content="West Africa Travel Journal &amp; Blog | Badagry Visitors Guide" />
  <meta name="twitter:description" content="Authentic historical accounts, travel guides, and insider tips for Badagry, Benin Republic, and Togo." />
  <meta name="twitter:image" content="https://badagryvisitorsguide.rav.com.ng/assets/images/Point-of-No-Return-420x347.jpg" />

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

  <!-- Schema.org Blog structured data -->
  <script type="application/ld+json">
  {
    "@context": "https://schema.org",
    "@type": "Blog",
    "name": "West Africa Travel Journal",
    "description": "Guides, border tips, and heritage reflections on Badagry, Benin Republic, and Togo.",
    "url": "https://badagryvisitorsguide.rav.com.ng/blog/",
    "publisher": {
      "@type": "Organization",
      "name": "Badagry Visitors Guide",
      "url": "https://badagryvisitorsguide.rav.com.ng/",
      "logo": "https://badagryvisitorsguide.rav.com.ng/assets/logo.jpg"
    }
  }
  </script>
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
        <li><a href="/#categories" class="mobile-nav-link">Services</a></li>
        <li><a href="/#tours" class="mobile-nav-link">Tours</a></li>
        <li><a href="/#corridor" class="mobile-nav-link">Destinations</a></li>
        <li><a href="/blog/" class="mobile-nav-link active">Journal</a></li>
        <li><a href="/admin" class="mobile-nav-link"><i class="fa-solid fa-lock"></i> Author Login</a></li>
        <li><a href="http://wa.me/+2347056989224?text=from+google+map+or+site" target="_blank" rel="noopener"
            class="mobile-nav-link">Contact Us</a></li>
      </ul>
      <div class="mobile-drawer-cta">
        <a href="http://wa.me/+2347056989224?text=from+google+map+or+site" target="_blank" rel="noopener"
          class="btn btn-primary w-100">Book Your Tour</a>
      </div>
      <div class="drawer-contact-info">
        <p><i class="fa-solid fa-location-dot"></i> Badagry, Lagos, Nigeria</p>
        <p><i class="fa-solid fa-phone"></i> +234 705 698 9224</p>
        <p><i class="fa-solid fa-envelope"></i> info@badagryvisitorsguide.com</p>
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
            <a href="/#faq">FAQ</a>
            <span class="divider">|</span>
            <a href="/admin"><i class="fa-solid fa-pen-to-square"></i> Admin / Write</a>
            <span class="divider">|</span>
            <a href="http://wa.me/+2347056989224?text=from+google+map+or+site" target="_blank" rel="noopener">Support</a>
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
          <li class="nav-item"><a href="/#categories" class="nav-link">Services</a></li>
          <li class="nav-item"><a href="/#tours" class="nav-link">Tours</a></li>
          <li class="nav-item"><a href="/#corridor" class="nav-link">Destinations</a></li>
          <li class="nav-item"><a href="/blog/" class="nav-link active">Journal</a></li>
          <li class="nav-item"><a href="http://wa.me/+2347056989224?text=from+google+map+or+site" target="_blank"
              rel="noopener" class="nav-link">Contact</a></li>
        </ul>

        <div class="nav-actions">
          <a href="http://wa.me/+2347056989224?text=from+google+map+or+site" target="_blank" rel="noopener"
            class="btn btn-primary nav-book-btn">
            <span>Plan Tour</span>
            <i class="fa-solid fa-arrow-right"></i>
          </a>
          <button class="mobile-toggle-btn" id="mobileToggleBtn" aria-label="Open mobile menu">
            <i class="fa-solid fa-bars"></i>
          </button>
        </div>
      </div>
    </nav>
  </header>

  <!-- Blog Page Hero Banner -->
  <section class="blog-hero-section">
    <div class="site-container text-center">
      <span class="hero-badge animate-up">West Africa Travel Journal</span>
      <h1 class="blog-page-title animate-up delay-1">Stories, Guides &amp; Cultural Heritage</h1>
      <p class="blog-page-lead animate-up delay-2">
        Insider tips, border crossing walkthroughs, and poignant historical accounts curated by our local guides across Badagry, Benin Republic, and Togo.
      </p>

      <!-- Search and Filter Box -->
      <div class="blog-search-card animate-up delay-3">
        <form action="/blog/" method="GET" class="blog-search-form">
          <div class="blog-search-input-wrap">
            <i class="fa-solid fa-magnifying-glass"></i>
            <input type="text" name="q" placeholder="Search articles (e.g. Yellow card, Point of No Return, hotels...)"
              value="<?= htmlspecialchars($search) ?>" class="blog-search-input" />
            <?php if ($search !== ''): ?>
              <a href="/blog/<?= $category !== '' ? '?category=' . urlencode($category) : '' ?>" class="blog-search-clear" title="Clear search">&times;</a>
            <?php endif; ?>
          </div>
          <button type="submit" class="btn btn-primary blog-search-btn">Search</button>
        </form>

        <!-- Category Filter Pills -->
        <div class="blog-categories-pills">
          <a href="/blog/<?= $search !== '' ? '?q=' . urlencode($search) : '' ?>"
            class="category-pill <?= $category === '' ? 'active' : '' ?>">All Categories</a>
          <?php foreach ($categories as $cat): ?>
            <a href="/blog/?category=<?= urlencode($cat) ?><?= $search !== '' ? '&q=' . urlencode($search) : '' ?>"
              class="category-pill <?= $category === $cat ? 'active' : '' ?>">
              <?= htmlspecialchars($cat) ?>
            </a>
          <?php endforeach; ?>
        </div>
      </div>
    </div>
  </section>

  <!-- Blog Content Section -->
  <section class="blog-main-section section-padding">
    <div class="site-container">
      <div class="blog-results-header">
        <div>
          <h2 class="blog-results-title">
            <?php if ($search !== '' && $category !== ''): ?>
              Showing results for "<?= htmlspecialchars($search) ?>" in <em><?= htmlspecialchars($category) ?></em>
            <?php elseif ($search !== ''): ?>
              Showing search results for "<?= htmlspecialchars($search) ?>"
            <?php elseif ($category !== ''): ?>
              Articles in <em><?= htmlspecialchars($category) ?></em>
            <?php else: ?>
              All Published Articles
            <?php endif; ?>
          </h2>
          <span class="blog-post-count"><?= $totalPosts ?> <?= $totalPosts === 1 ? 'article found' : 'articles found' ?></span>
        </div>

        <?php if ($search !== '' || $category !== ''): ?>
          <a href="/blog/" class="btn btn-sm btn-outline-dark"><i class="fa-solid fa-rotate-left"></i> Reset Filters</a>
        <?php endif; ?>
      </div>

      <?php if (empty($posts)): ?>
        <div class="blog-empty-state">
          <i class="fa-regular fa-folder-open empty-icon"></i>
          <h3>No articles found</h3>
          <p>We couldn't find any articles matching your search query. Try searching with different keywords or browse all categories.</p>
          <a href="/blog/" class="btn btn-primary mt-3">View All Articles</a>
        </div>
      <?php else: ?>
        <div class="blog-grid">
          <?php foreach ($posts as $post): ?>
            <article class="blog-card">
              <div class="blog-card-img-wrap">
                <a href="/blog/<?= urlencode($post['slug']) ?>">
                  <img src="/<?= ltrim(htmlspecialchars($post['featured_image'] ?: 'assets/images/Point-of-No-Return-420x347.jpg'), '/') ?>"
                    alt="<?= htmlspecialchars($post['title']) ?>" loading="lazy" />
                </a>
                <span class="blog-card-badge"><?= htmlspecialchars($post['category']) ?></span>
              </div>
              <div class="blog-card-body">
                <div class="blog-card-meta">
                  <span><i class="fa-regular fa-calendar"></i> <?= date('M d, Y', strtotime($post['published_at'])) ?></span>
                  <span><i class="fa-regular fa-clock"></i> <?= htmlspecialchars($post['read_time']) ?></span>
                </div>
                <h3 class="blog-card-title">
                  <a href="/blog/<?= urlencode($post['slug']) ?>"><?= htmlspecialchars($post['title']) ?></a>
                </h3>
                <p class="blog-card-excerpt">
                  <?= htmlspecialchars($post['excerpt']) ?>
                </p>
                <div class="blog-card-footer">
                  <a href="/blog/<?= urlencode($post['slug']) ?>" class="btn-text-link">
                    <span>Read Article</span>
                    <i class="fa-solid fa-arrow-right-long"></i>
                  </a>
                </div>
              </div>
            </article>
          <?php endforeach; ?>
        </div>
      <?php endif; ?>
    </div>
  </section>

  <!-- Guided Tour Banner CTA -->
  <section class="blog-cta-banner">
    <div class="site-container">
      <div class="cta-banner-card">
        <div class="cta-banner-content">
          <span class="sub-title text-light">Experience It In Person</span>
          <h2>Ready to Explore Badagry &amp; West Africa?</h2>
          <p>From day trips to 5-day cross-border journeys across Nigeria, Benin Republic, and Togo. We build tours around your budget.</p>
          <div class="cta-banner-buttons">
            <a href="/#tours" class="btn btn-primary">Browse Tours <i class="fa-solid fa-compass"></i></a>
            <a href="http://wa.me/+2347056989224?text=from+google+map+or+site" target="_blank" rel="noopener"
              class="btn btn-outline-white">Chat on WhatsApp <i class="fa-brands fa-whatsapp"></i></a>
          </div>
        </div>
      </div>
    </div>
  </section>

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

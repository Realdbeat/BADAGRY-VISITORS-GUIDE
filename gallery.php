<?php
require_once __DIR__ . '/db.php';
require_once __DIR__ . '/sitemap-generator.php';
autoUpdateSitemapsIfModified(60);

// Retrieve all images and videos
$allRawImages = getAllProjectImages();
$allRawVideos = getAllProjectVideos();

// Exclude avatar portraits, thumbnails, system assets, and non-Badagry media
$excludedPatterns = [
    '-420x347', 'logo.jpg', 'Chukwuemeka-Eze', 'Claire-Dubois', 'James-Sutton',
    'Kemi-Adeyemi', 'Nkechi-Okafor-Williams', 'Yetunde-Fashola', 'eYellow-Card',
    'Ade-Sofola', 'Adedoyin-Dosunmu-Ogunbi', 'Ashola-Moyinoluwa', 'SEGUN-OPAYELE',
    'Tutor-Saheed', 'Olayinka-Oyeku',
    'benin', 'togo', 'ouidah', 'cotonou', 'porto-novo', 'lome', 'amazone', 'ganvie',
    'pendjari', 'babs-dock', 'prince-lorenzo', 'grand-marche'
];

function isExcludedGalleryItem(string $filename, array $excluded): bool {
    foreach ($excluded as $pat) {
        if (stripos($filename, $pat) !== false) {
            return true;
        }
    }
    return false;
}

// Categorization helper (Badagry-Exclusive Experiences)
function getMediaCategory(string $filename, string $title, string $geo): array {
    $fn = strtolower($filename);
    $t = strtolower($title);
    $g = strtolower($geo);
    $combined = $fn . ' ' . $t . ' ' . $g;

    $catSlug = 'landmarks';
    $catName = 'Historical Landmarks';
    $country = 'Badagry, Lagos';
    $countryFlag = '🏛️';

    if (strpos($combined, 'slave') !== false || strpos($combined, 'return') !== false || strpos($combined, 'memorial') !== false || strpos($combined, 'route') !== false) {
        $catSlug = 'slave-route';
        $catName = 'Slave Route & Memorials';
        $country = 'Badagry, Lagos';
        $countryFlag = '🔗';
    } elseif (strpos($combined, 'group') !== false || strpos($combined, 'team') !== false || strpos($combined, 'school') !== false || strpos($combined, 'client') !== false || strpos($combined, 'private') !== false || strpos($combined, 'corporate') !== false || strpos($combined, 'zangbeto') !== false || strpos($combined, 'festival') !== false) {
        $catSlug = 'tours';
        $catName = 'Tour Groups & Excursions';
        $country = 'Badagry, Lagos';
        $countryFlag = '👥';
    } elseif (strpos($combined, 'beach') !== false || strpos($combined, 'creek') !== false || strpos($combined, 'leisure') !== false || strpos($combined, 'weekend') !== false || strpos($combined, 'palms') !== false) {
        $catSlug = 'leisure';
        $catName = 'Creeks & Marina';
        $country = 'Badagry, Lagos';
        $countryFlag = '🌴';
    }

    return [$catSlug, $catName, $country, $countryFlag];
}

// Prepare gallery items
$galleryItems = [];

// 1. Process project images
foreach ($allRawImages as $filename => $img) {
    if (isExcludedGalleryItem($filename, $excludedPatterns)) {
        continue;
    }
    list($catSlug, $catName, $country, $countryFlag) = getMediaCategory($filename, $img['title'], $img['geo']);
    $galleryItems[] = [
        'type' => 'image',
        'filename' => $filename,
        'title' => $img['title'],
        'caption' => $img['caption'],
        'geo' => 'Badagry, Lagos, Nigeria',
        'url' => '/assets/images/' . $filename,
        'fullUrl' => $img['url'],
        'category_slug' => $catSlug,
        'category_name' => $catName,
        'country' => $country,
        'country_flag' => $countryFlag,
        'mtime' => $img['mtime'] ?? time()
    ];
}

// 2. Process project videos
foreach ($allRawVideos as $filename => $vid) {
    list($catSlug, $catName, $country, $countryFlag) = getMediaCategory($filename, $vid['title'], $vid['caption']);
    $galleryItems[] = [
        'type' => 'video',
        'filename' => $filename,
        'title' => $vid['title'],
        'caption' => $vid['caption'],
        'geo' => 'Badagry Tour Highlight',
        'url' => $vid['url'],
        'fullUrl' => $vid['fullUrl'],
        'category_slug' => 'videos',
        'category_name' => 'Video Highlights',
        'country' => 'Badagry Video',
        'country_flag' => '🎬',
        'mtime' => file_exists(__DIR__ . '/assets/videos/' . $filename) ? filemtime(__DIR__ . '/assets/videos/' . $filename) : time()
    ];
}

// Sort by timestamp descending so newly added items show first
usort($galleryItems, function($a, $b) {
    return $b['mtime'] <=> $a['mtime'];
});

// Category counts
$counts = [
    'all' => count($galleryItems),
    'landmarks' => 0,
    'slave-route' => 0,
    'leisure' => 0,
    'tours' => 0,
    'videos' => 0
];
foreach ($galleryItems as $item) {
    if (isset($counts[$item['category_slug']])) {
        $counts[$item['category_slug']]++;
    }
    if ($item['type'] === 'video') {
        $counts['videos']++;
    }
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Photo &amp; Video Gallery | Badagry Heritage &amp; Cultural Tours</title>
  <meta name="description"
    content="Browse high-resolution photographs and tour videos of Badagry slave trade landmarks, First Storey Building, Point of No Return, Seriki Williams Abass Barracoon, and real guided tour experiences." />
  <link rel="canonical" href="https://badagryvisitorsguide.com.ng/gallery/" />
  <meta name="robots" content="index, follow, max-image-preview:large, max-snippet:-1, max-video-preview:-1" />
  <meta name="googlebot" content="index, follow, max-snippet:-1, max-image-preview:large, max-video-preview:-1" />
  <meta name="bingbot" content="index, follow, max-snippet:-1, max-image-preview:large, max-video-preview:-1" />
  <meta name="google-site-verification" content="google1c708dda37599c2a" />

  <!-- Open Graph -->
  <meta property="og:type" content="website" />
  <meta property="og:site_name" content="Badagry Visitors Guide" />
  <meta property="og:title" content="Photo &amp; Video Gallery | Badagry Heritage &amp; Cultural Tours" />
  <meta property="og:description" content="Explore authentic photographs, archival landmarks, and video chronicles from our guided Badagry heritage and coastal expeditions." />
  <meta property="og:url" content="https://badagryvisitorsguide.com.ng/gallery/" />
  <meta property="og:image" content="https://badagryvisitorsguide.com.ng/assets/images/Point-of-No-Return-Monument-Badagry.jpg" />

  <!-- Twitter Card -->
  <meta name="twitter:card" content="summary_large_image" />
  <meta name="twitter:title" content="Photo &amp; Video Gallery | Badagry Visitors Guide" />
  <meta name="twitter:description" content="Discover authentic heritage monuments, slave route memorials, and creek boat tours across Badagry, Lagos State, Nigeria." />
  <meta name="twitter:image" content="https://badagryvisitorsguide.com.ng/assets/images/Point-of-No-Return-Monument-Badagry.jpg" />

  <link rel="icon" type="image/jpeg" href="/assets/logo.jpg" />

  <!-- Google Fonts: Plus Jakarta Sans & Playfair Display -->
  <link rel="preconnect" href="https://fonts.googleapis.com" />
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
  <link
    href="https://fonts.googleapis.com/css2?family=Playfair+Display:ital,wght@0,600;0,700;1,400&family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&display=swap"
    rel="stylesheet" />

  <!-- Font Awesome 6 Icons -->
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" />

  <link rel="stylesheet" href="/style.css?v=3.04" />

  <!-- Schema.org Breadcrumbs structured data -->
  <script type="application/ld+json">
  {
    "@context": "https://schema.org",
    "@type": "BreadcrumbList",
    "itemListElement": [
      {
        "@type": "ListItem",
        "position": 1,
        "name": "Home",
        "item": "https://badagryvisitorsguide.com.ng/"
      },
      {
        "@type": "ListItem",
        "position": 2,
        "name": "Media Gallery",
        "item": "https://badagryvisitorsguide.com.ng/gallery/"
      }
    ]
  }
  </script>

  <!-- Schema.org ImageGallery Structured Data -->
  <script type="application/ld+json">
  {
    "@context": "https://schema.org",
    "@type": "ImageGallery",
    "name": "Badagry Heritage Tour Photo & Video Gallery",
    "description": "Authentic photography and tour documentation from Badagry, Lagos State, Nigeria.",
    "url": "https://badagryvisitorsguide.com.ng/gallery/",
    "publisher": {
      "@type": "Organization",
      "name": "Badagry Visitors Guide",
      "url": "https://badagryvisitorsguide.com.ng/",
      "logo": {
        "@type": "ImageObject",
        "url": "https://badagryvisitorsguide.com.ng/assets/logo.jpg"
      }
    },
    "image": [
      <?php
      $schemaImages = [];
      $slice = array_slice($galleryItems, 0, 25);
      foreach ($slice as $itm) {
          if ($itm['type'] === 'image') {
              $schemaImages[] = json_encode([
                  '@type' => 'ImageObject',
                  'name' => $itm['title'],
                  'caption' => $itm['caption'],
                  'contentUrl' => $itm['fullUrl'],
                  'thumbnailUrl' => $itm['fullUrl']
              ], JSON_UNESCAPED_SLASHES);
          }
      }
      echo implode(",\n      ", $schemaImages);
      ?>
    ]
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
        <li><a href="/gallery/" class="mobile-nav-link active">Gallery</a></li>
        <li><a href="/blog/" class="mobile-nav-link">Journal</a></li>
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
        <p><i class="fa-solid fa-phone"></i> +234 802 593 7044</p>
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
            <a href="/admin"><i class="fa-solid fa-cloud-arrow-up"></i> Upload Media</a>
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
          <li class="nav-item"><a href="/gallery/" class="nav-link active">Gallery</a></li>
          <li class="nav-item"><a href="/blog/" class="nav-link">Journal</a></li>
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

  <!-- Gallery Hero Banner -->
  <section class="gallery-hero-section">
    <div class="gallery-hero-bg"
      style="background-image: linear-gradient(rgba(17, 24, 39, 0.74), rgba(17, 24, 39, 0.88)), url('/assets/images/Point-of-No-Return-Monument-Badagry.jpg');">
    </div>
    <div class="site-container gallery-hero-content text-center">
      <div class="gallery-badge">
        <i class="fa-solid fa-camera-retro"></i> Curated Visual Showcase
      </div>
      <h1 class="gallery-hero-title">Badagry Photo &amp; Video Gallery</h1>
      <p class="gallery-hero-subtitle">
        Authentic archival moments, living sacred monuments, and unforgettable journeys across Badagry, Lagos State, Nigeria.
      </p>

      <!-- Key Stats Bar -->
      <div class="gallery-stats-bar">
        <div class="stat-pill">
          <i class="fa-solid fa-images"></i>
          <span><strong><?= count($galleryItems) ?>+</strong> Authentic Media</span>
        </div>
        <div class="stat-pill">
          <i class="fa-solid fa-landmark"></i>
          <span><strong>10+</strong> Historical Sites</span>
        </div>
        <div class="stat-pill">
          <i class="fa-solid fa-water"></i>
          <span><strong>Scenic</strong> Creeks &amp; Marina</span>
        </div>
        <div class="stat-pill">
          <i class="fa-solid fa-users"></i>
          <span><strong>100%</strong> Native Guided</span>
        </div>
      </div>
    </div>
  </section>

  <!-- Interactive Gallery Container Section -->
  <section class="gallery-main-section section-padding">
    <div class="site-container">

      <!-- Gallery Toolbar: Categories & Search -->
      <div class="gallery-toolbar-wrap">
        <!-- Filter Tabs -->
        <div class="gallery-filter-tabs" id="galleryFilterTabs">
          <button class="filter-tab-btn active" data-filter="all">
            <i class="fa-solid fa-border-all"></i> All Media <span class="tab-badge"><?= $counts['all'] ?></span>
          </button>
          <button class="filter-tab-btn" data-filter="landmarks">
            🏛️ Historical Landmarks <span class="tab-badge"><?= $counts['landmarks'] ?></span>
          </button>
          <button class="filter-tab-btn" data-filter="slave-route">
            🔗 Slave Route &amp; Memorials <span class="tab-badge"><?= $counts['slave-route'] ?></span>
          </button>
          <button class="filter-tab-btn" data-filter="leisure">
            🌴 Creeks &amp; Marina <span class="tab-badge"><?= $counts['leisure'] ?></span>
          </button>
          <button class="filter-tab-btn" data-filter="tours">
            👥 Tour Groups <span class="tab-badge"><?= $counts['tours'] ?></span>
          </button>
          <?php if ($counts['videos'] > 0): ?>
          <button class="filter-tab-btn" data-filter="videos">
            🎬 Videos <span class="tab-badge"><?= $counts['videos'] ?></span>
          </button>
          <?php endif; ?>
        </div>

        <!-- Search Bar -->
        <div class="gallery-search-wrap">
          <i class="fa-solid fa-magnifying-glass search-icon"></i>
          <input type="text" id="gallerySearchInput" class="gallery-search-input"
            placeholder="Search by monument, country, or keyword..." />
          <button type="button" id="gallerySearchClear" class="gallery-search-clear" title="Clear search" style="display: none;">
            <i class="fa-solid fa-xmark"></i>
          </button>
        </div>
      </div>

      <!-- Live Results Counter -->
      <div class="gallery-results-bar">
        <span id="galleryResultsCount">Showing <strong><?= count($galleryItems) ?></strong> photos &amp; videos</span>
        <div class="gallery-layout-hint">
          <i class="fa-regular fa-hand-pointer"></i> Click any item to enlarge with full historical caption &amp; booking inquiry
        </div>
      </div>

      <!-- Gallery Grid -->
      <div class="gallery-media-grid" id="galleryMediaGrid">
        <?php foreach ($galleryItems as $index => $item): ?>
          <div class="gallery-card"
            data-index="<?= $index ?>"
            data-type="<?= htmlspecialchars($item['type']) ?>"
            data-category="<?= htmlspecialchars($item['category_slug']) ?>"
            data-title="<?= htmlspecialchars($item['title']) ?>"
            data-caption="<?= htmlspecialchars($item['caption']) ?>"
            data-geo="<?= htmlspecialchars($item['geo']) ?>"
            data-src="<?= htmlspecialchars($item['url']) ?>"
            data-country="<?= htmlspecialchars($item['country']) ?>"
            data-flag="<?= htmlspecialchars($item['country_flag']) ?>">

            <div class="gallery-thumb-container">
              <?php if ($item['type'] === 'video'): ?>
                <video src="<?= htmlspecialchars($item['url']) ?>" preload="metadata" muted playsinline class="gallery-thumb-media"></video>
                <div class="media-type-badge video-badge">
                  <i class="fa-solid fa-video"></i> Video
                </div>
                <div class="play-overlay-icon">
                  <i class="fa-solid fa-circle-play"></i>
                </div>
              <?php else: ?>
                <img src="<?= htmlspecialchars($item['url']) ?>"
                  alt="<?= htmlspecialchars($item['title']) ?>"
                  title="<?= htmlspecialchars($item['title']) ?>"
                  loading="lazy"
                  class="gallery-thumb-media" />
                <div class="media-type-badge photo-badge">
                  <i class="fa-solid fa-camera"></i> Photo
                </div>
              <?php endif; ?>

              <!-- Country Tag -->
              <span class="country-pill">
                <?= $item['country_flag'] ?> <?= htmlspecialchars($item['country']) ?>
              </span>

              <!-- Hover Overlay -->
              <div class="gallery-card-overlay">
                <div class="overlay-top">
                  <span class="category-pill"><?= htmlspecialchars($item['category_name']) ?></span>
                </div>
                <div class="overlay-bottom">
                  <h3 class="card-title"><?= htmlspecialchars($item['title']) ?></h3>
                  <p class="card-geo"><i class="fa-solid fa-location-dot"></i> <?= htmlspecialchars($item['geo']) ?></p>
                  <div class="overlay-cta-wrap">
                    <span class="expand-btn"><i class="fa-solid fa-expand"></i> View Details</span>
                  </div>
                </div>
              </div>
            </div>

            <!-- Card Bottom Bar for Accessibility & Clear Labeling -->
            <div class="gallery-card-caption-bar">
              <h4 class="caption-title"><?= htmlspecialchars($item['title']) ?></h4>
              <span class="caption-location"><i class="fa-solid fa-location-dot"></i> <?= htmlspecialchars($item['geo']) ?></span>
            </div>
          </div>
        <?php endforeach; ?>
      </div>

      <!-- Empty State -->
      <div class="gallery-empty-state" id="galleryEmptyState" style="display: none;">
        <i class="fa-regular fa-image empty-icon"></i>
        <h3>No matching media found</h3>
        <p>Try searching with another keyword or select "All Media" to see all tour moments.</p>
        <button class="btn btn-outline" id="galleryResetBtn">Reset Filters</button>
      </div>

    </div>
  </section>

  <!-- Tour Booking CTA Section -->
  <section class="gallery-cta-section section-padding">
    <div class="site-container">
      <div class="gallery-cta-box">
        <div class="cta-text">
          <span class="sub-title text-accent">Inspired By What You See?</span>
          <h2 class="cta-title">Experience These Badagry Historical Landmarks In Person</h2>
          <p class="cta-desc">
            From the 1845 First Storey Building to the solemn Point of No Return across Gberefu Island — let our native historians guide your journey with customized itineraries and authentic local care.
          </p>
        </div>
        <div class="cta-buttons">
          <a href="http://wa.me/+2347056989224?text=Hello+Badagry+Visitors+Guide!+I+was+browsing+your+gallery+and+would+like+to+plan+a+tour."
            target="_blank" rel="noopener" class="btn btn-primary">
            <i class="fa-brands fa-whatsapp"></i> Chat On WhatsApp
          </a>
          <a href="/#tours" class="btn btn-outline-white">
            <i class="fa-solid fa-compass"></i> View Tour Packages
          </a>
        </div>
      </div>
    </div>
  </section>

  <!-- Fullscreen Lightbox Modal -->
  <div class="gallery-lightbox-modal" id="galleryLightboxModal" role="dialog" aria-modal="true" aria-label="Media Preview Lightbox">
    <button class="lightbox-nav-btn lightbox-close-btn" id="modalCloseBtn" aria-label="Close modal">
      <i class="fa-solid fa-xmark"></i>
    </button>
    <button class="lightbox-nav-btn lightbox-prev-btn" id="modalPrevBtn" aria-label="Previous item">
      <i class="fa-solid fa-chevron-left"></i>
    </button>
    <button class="lightbox-nav-btn lightbox-next-btn" id="modalNextBtn" aria-label="Next item">
      <i class="fa-solid fa-chevron-right"></i>
    </button>

    <div class="lightbox-dialog-wrap">
      <!-- Media Stage -->
      <div class="lightbox-media-stage" id="modalMediaStage">
        <!-- Injected via JavaScript (img or video) -->
      </div>

      <!-- Details Drawer -->
      <div class="lightbox-meta-panel" id="modalMetaPanel">
        <div class="meta-header">
          <div class="meta-tag-row">
            <span class="meta-badge" id="modalCountryBadge">🇳🇬 Nigeria</span>
            <span class="meta-counter" id="modalCounter">1 / <?= count($galleryItems) ?></span>
          </div>
          <h2 class="meta-title" id="modalTitle">Landmark Title</h2>
          <p class="meta-location" id="modalLocation">
            <i class="fa-solid fa-location-dot"></i> <span>Location Name</span>
          </p>
        </div>

        <div class="meta-body">
          <p class="meta-caption" id="modalCaption">
            Detailed cultural and historical description of the site.
          </p>
        </div>

        <div class="meta-footer">
          <a href="#" id="modalInquireBtn" target="_blank" rel="noopener" class="btn btn-primary w-100">
            <i class="fa-brands fa-whatsapp"></i> Inquire About This Location
          </a>
          <div class="meta-sub-actions">
            <button type="button" class="btn-text-action" id="modalCopyLinkBtn">
              <i class="fa-regular fa-copy"></i> Copy Direct Link
            </button>
            <a href="#" target="_blank" class="btn-text-action" id="modalViewOriginalBtn">
              <i class="fa-solid fa-arrow-up-right-from-square"></i> Open High-Res
            </a>
          </div>
        </div>
      </div>
    </div>
  </div>

  <!-- Footer -->
  <footer class="site-footer">
    <div class="site-container">
      <div class="footer-grid">
        <!-- Col 1: Brand -->
        <div class="footer-col footer-brand-col">
          <a href="/" class="footer-logo">
            <img src="/assets/logo.jpg" alt="Badagry Visitors Guide Logo" class="footer-logo-img" />
            <div class="brand-text">
              <span class="logo-main">BADAGRY <strong>VISITORS GUIDE</strong></span>
              <span class="logo-sub">Heritage &amp; Cultural Tours</span>
            </div>
          </a>
          <p class="footer-desc">
            Authentic, affordable heritage and coastal adventures across Badagry, Lagos State, Nigeria. Led by native historians.
          </p>
          <div class="footer-social-links">
            <a href="https://www.instagram.com/badagryvisitorsguide" target="_blank" rel="noopener" aria-label="Instagram"><i class="fa-brands fa-instagram"></i></a>
            <a href="https://www.tiktok.com/@vezeltour" target="_blank" rel="noopener" aria-label="TikTok"><i class="fa-brands fa-tiktok"></i></a>
            <a href="http://wa.me/+2347056989224?text=from+google+map+or+site" target="_blank" rel="noopener" aria-label="WhatsApp"><i class="fa-brands fa-whatsapp"></i></a>
            <a href="https://www.facebook.com/share/1EnBcsZgqb/" target="_blank" rel="noopener" aria-label="Facebook"><i class="fa-brands fa-facebook-f"></i></a>
            <a href="https://x.com/badagry_v_guide" target="_blank" rel="noopener" aria-label="X (Twitter)"><i class="fa-brands fa-x-twitter"></i></a>
            <a href="https://www.linkedin.com/in/ezekiel-viavonu-105a15111/" target="_blank" rel="noopener" aria-label="LinkedIn"><i class="fa-brands fa-linkedin-in"></i></a>
          </div>
        </div>

        <!-- Col 2: Quick Links -->
        <div class="footer-col">
          <h3 class="footer-title">Quick Links</h3>
          <ul class="footer-menu">
            <li><a href="/">Home</a></li>
            <li><a href="/#about">About Us</a></li>
            <li><a href="/#categories">Services</a></li>
            <li><a href="/#tours">Our Tours</a></li>
            <li><a href="/gallery/">Photo Gallery</a></li>
            <li><a href="/blog/">Heritage Chronicle</a></li>
            <li><a href="/admin">Upload Media</a></li>
            <li><a href="http://wa.me/+2347056989224?text=from+google+map+or+site" target="_blank" rel="noopener">Contact Us</a></li>
          </ul>
        </div>

        <!-- Col 3: Get In Touch -->
        <div class="footer-col">
          <h3 class="footer-title">Get In Touch</h3>
          <div class="footer-contact-items">
            <div class="contact-item">
              <i class="fa-solid fa-phone"></i>
              <div>
                <span class="item-label">Phone &amp; WhatsApp</span>
                <a href="http://wa.me/+2348025937044?text=from+google+map+or+site" target="_blank" rel="noopener">+234 802 593 7044</a>
              </div>
            </div>
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
                <span class="item-label">Email</span>
                <a href="mailto:info@badagryvisitorsguide.com">info@badagryvisitorsguide.com</a>
              </div>
            </div>
            <div class="contact-item">
              <i class="fa-solid fa-location-dot"></i>
              <div>
                <span class="item-label">Location</span>
                <span>Badagry Roundabout, Badagry, Lagos State, Nigeria</span>
              </div>
            </div>
          </div>
        </div>

        <!-- Col 4: Landmark Highlights -->
        <div class="footer-col">
          <h3 class="footer-title">Badagry Landmarks</h3>
          <ul class="footer-menu">
            <li><a href="/#landmarks">Point of No Return &amp; Gberefu Island</a></li>
            <li><a href="/#landmarks">First Storey Building in Nigeria (1845)</a></li>
            <li><a href="/#landmarks">Seriki Williams Abass Barracoon</a></li>
            <li><a href="/#landmarks">Badagry Heritage Museum &amp; Relics</a></li>
            <li><a href="/#landmarks">Whispering Palms &amp; Badagry Creek</a></li>
          </ul>
        </div>
      </div>

      <div class="footer-bottom">
        <p>&copy; <?= date('Y') ?> Badagry Visitors Guide. All rights reserved.</p>
        <div class="footer-bottom-links">
          <a href="/#faq">FAQs</a>
          <a href="/gallery/">Media Gallery</a>
          <a href="/blog/">Heritage Chronicle</a>
          <a href="/admin">Portal Login</a>
        </div>
      </div>
    </div>
  </footer>

  <!-- Floating WhatsApp Click-To-Chat Widget -->
  <div class="whatsapp-floating-widget" id="whatsappWidget">
    <div class="wa-chat-box" id="waChatBox">
      <div class="wa-chat-header">
        <div class="wa-avatar-wrap">
          <div class="wa-avatar">
            <img src="/assets/logo.jpg" alt="Badagry Visitors Guide" class="wa-avatar-img" />
          </div>
          <span class="wa-online-dot"></span>
        </div>
        <div class="wa-header-info">
          <h4>Badagry Visitors Guide</h4>
          <p>Typically replies within minutes</p>
        </div>
        <button class="wa-close-btn" id="waCloseBtn" aria-label="Close WhatsApp chat">&times;</button>
      </div>
      <div class="wa-chat-body">
        <div class="wa-bubble received">
          <p>Hi there! Welcome to <strong>Badagry Visitors Guide</strong>!</p>
          <p>Planning a trip to Badagry? Click below to chat directly with our local tour guides on WhatsApp.</p>
          <a href="http://wa.me/+2347056989224?text=from+google+map+or+site" target="_blank" rel="noopener"
            class="btn btn-sm btn-primary w-100" style="margin-top: 10px;">
            <i class="fa-brands fa-whatsapp"></i> Chat On WhatsApp Now
          </a>
          <span class="wa-time">Just now</span>
        </div>
      </div>
      <div class="wa-chat-footer">
        <input type="text" id="waInput" placeholder="Type a message..." />
        <button id="waSendBtn" class="wa-send-btn" aria-label="Send WhatsApp message">
          <i class="fa-solid fa-paper-plane"></i>
        </button>
      </div>
    </div>

    <button class="wa-toggle-btn" id="waToggleBtn" aria-label="Chat on WhatsApp">
      <i class="fa-brands fa-whatsapp"></i>
      <span class="wa-tooltip">Chat with us!</span>
    </button>
  </div>

  <!-- Interactive Gallery JavaScript -->
  <script>
  document.addEventListener('DOMContentLoaded', () => {
    // Mobile Drawer Toggle
    const mobileToggleBtn = document.getElementById('mobileToggleBtn');
    const drawerCloseBtn = document.getElementById('drawerCloseBtn');
    const mobileMenuDrawer = document.getElementById('mobileMenuDrawer');
    const mobileMenuOverlay = document.getElementById('mobileMenuOverlay');

    function openMobileDrawer() {
      if (mobileMenuDrawer) mobileMenuDrawer.classList.add('active');
      if (mobileMenuOverlay) mobileMenuOverlay.classList.add('active');
      document.body.style.overflow = 'hidden';
    }
    function closeMobileDrawer() {
      if (mobileMenuDrawer) mobileMenuDrawer.classList.remove('active');
      if (mobileMenuOverlay) mobileMenuOverlay.classList.remove('active');
      document.body.style.overflow = '';
    }

    // WhatsApp Floating Widget
    const waToggleBtn = document.getElementById('waToggleBtn');
    const waChatBox  = document.getElementById('waChatBox');
    const waCloseBtn = document.getElementById('waCloseBtn');
    const waInput    = document.getElementById('waInput');
    const waSendBtn  = document.getElementById('waSendBtn');

    if (waToggleBtn && waChatBox) {
      waToggleBtn.addEventListener('click', () => {
        waChatBox.classList.toggle('active');
      });
      if (waCloseBtn) {
        waCloseBtn.addEventListener('click', () => waChatBox.classList.remove('active'));
      }
      function sendWaMessage() {
        const text = waInput ? waInput.value.trim() : '';
        const url = text
          ? `http://wa.me/+2347056989224?text=${encodeURIComponent('from gallery: ' + text)}`
          : 'http://wa.me/+2347056989224?text=from+gallery';
        window.open(url, '_blank');
        if (waInput) waInput.value = '';
      }
      if (waSendBtn) waSendBtn.addEventListener('click', sendWaMessage);
      if (waInput) {
        waInput.addEventListener('keypress', (e) => {
          if (e.key === 'Enter') { e.preventDefault(); sendWaMessage(); }
        });
      }
    }

    if (mobileToggleBtn) mobileToggleBtn.addEventListener('click', openMobileDrawer);
    if (drawerCloseBtn) drawerCloseBtn.addEventListener('click', closeMobileDrawer);
    if (mobileMenuOverlay) mobileMenuOverlay.addEventListener('click', closeMobileDrawer);

    // Gallery Filtering & Search
    const cards = Array.from(document.querySelectorAll('.gallery-card'));
    const filterBtns = Array.from(document.querySelectorAll('.filter-tab-btn'));
    const searchInput = document.getElementById('gallerySearchInput');
    const searchClear = document.getElementById('gallerySearchClear');
    const resultsCountEl = document.getElementById('galleryResultsCount');
    const emptyState = document.getElementById('galleryEmptyState');
    const resetBtn = document.getElementById('galleryResetBtn');

    let currentFilter = 'all';
    let currentSearch = '';

    function applyFilters() {
      let visibleCount = 0;
      const query = currentSearch.toLowerCase().trim();

      cards.forEach(card => {
        const cat = card.dataset.category;
        const type = card.dataset.type;
        const title = (card.dataset.title || '').toLowerCase();
        const caption = (card.dataset.caption || '').toLowerCase();
        const geo = (card.dataset.geo || '').toLowerCase();
        const country = (card.dataset.country || '').toLowerCase();

        // Check category match
        let matchesCat = false;
        if (currentFilter === 'all') {
          matchesCat = true;
        } else if (currentFilter === 'videos') {
          matchesCat = (type === 'video');
        } else {
          matchesCat = (cat === currentFilter);
        }

        // Check search match
        let matchesSearch = true;
        if (query) {
          matchesSearch = title.includes(query) || caption.includes(query) || geo.includes(query) || country.includes(query);
        }

        if (matchesCat && matchesSearch) {
          card.style.display = '';
          visibleCount++;
        } else {
          card.style.display = 'none';
        }
      });

      // Update counter
      if (resultsCountEl) {
        resultsCountEl.innerHTML = `Showing <strong>${visibleCount}</strong> of ${cards.length} photos & videos`;
      }

      // Empty state
      if (emptyState) {
        emptyState.style.display = visibleCount === 0 ? 'block' : 'none';
      }
    }

    filterBtns.forEach(btn => {
      btn.addEventListener('click', () => {
        filterBtns.forEach(b => b.classList.remove('active'));
        btn.classList.add('active');
        currentFilter = btn.dataset.filter;
        applyFilters();
      });
    });

    if (searchInput) {
      searchInput.addEventListener('input', () => {
        currentSearch = searchInput.value;
        if (searchClear) searchClear.style.display = currentSearch ? 'flex' : 'none';
        applyFilters();
      });
    }

    if (searchClear) {
      searchClear.addEventListener('click', () => {
        searchInput.value = '';
        currentSearch = '';
        searchClear.style.display = 'none';
        applyFilters();
        searchInput.focus();
      });
    }

    if (resetBtn) {
      resetBtn.addEventListener('click', () => {
        currentFilter = 'all';
        currentSearch = '';
        if (searchInput) searchInput.value = '';
        if (searchClear) searchClear.style.display = 'none';
        filterBtns.forEach(b => b.classList.toggle('active', b.dataset.filter === 'all'));
        applyFilters();
      });
    }

    // ==========================================
    // Fullscreen Interactive Lightbox
    // ==========================================
    const modal = document.getElementById('galleryLightboxModal');
    const mediaStage = document.getElementById('modalMediaStage');
    const modalTitle = document.getElementById('modalTitle');
    const modalCaption = document.getElementById('modalCaption');
    const modalLocation = document.getElementById('modalLocation');
    const modalCountryBadge = document.getElementById('modalCountryBadge');
    const modalCounter = document.getElementById('modalCounter');
    const modalInquireBtn = document.getElementById('modalInquireBtn');
    const modalViewOriginalBtn = document.getElementById('modalViewOriginalBtn');
    const modalCopyLinkBtn = document.getElementById('modalCopyLinkBtn');
    const modalCloseBtn = document.getElementById('modalCloseBtn');
    const modalPrevBtn = document.getElementById('modalPrevBtn');
    const modalNextBtn = document.getElementById('modalNextBtn');

    let currentVisibleCards = [];
    let activeIndex = -1;

    function getVisibleCards() {
      return cards.filter(c => c.style.display !== 'none');
    }

    function openLightbox(card) {
      currentVisibleCards = getVisibleCards();
      activeIndex = currentVisibleCards.indexOf(card);
      if (activeIndex === -1) activeIndex = 0;
      renderActiveItem();
      if (modal) modal.classList.add('active');
      document.body.style.overflow = 'hidden';
    }

    function closeLightbox() {
      if (modal) modal.classList.remove('active');
      if (mediaStage) mediaStage.innerHTML = '';
      document.body.style.overflow = '';
    }

    function renderActiveItem() {
      if (activeIndex < 0 || activeIndex >= currentVisibleCards.length) return;
      const card = currentVisibleCards[activeIndex];
      const type = card.dataset.type;
      const src = card.dataset.src;
      const title = card.dataset.title;
      const caption = card.dataset.caption;
      const geo = card.dataset.geo;
      const flag = card.dataset.flag || '📍';
      const country = card.dataset.country || 'West Africa';

      // Update Media Stage
      mediaStage.innerHTML = '';
      if (type === 'video') {
        const vid = document.createElement('video');
        vid.src = src;
        vid.controls = true;
        vid.autoplay = true;
        vid.playsInline = true;
        vid.className = 'lightbox-media-element';
        mediaStage.appendChild(vid);
      } else {
        const img = document.createElement('img');
        img.src = src;
        img.alt = title;
        img.className = 'lightbox-media-element';
        mediaStage.appendChild(img);
      }

      // Update Metadata
      if (modalTitle) modalTitle.textContent = title;
      if (modalCaption) modalCaption.textContent = caption;
      if (modalLocation) {
        modalLocation.innerHTML = `<i class="fa-solid fa-location-dot"></i> <span>${geo}</span>`;
      }
      if (modalCountryBadge) {
        modalCountryBadge.textContent = `${flag} ${country}`;
      }
      if (modalCounter) {
        modalCounter.textContent = `${activeIndex + 1} / ${currentVisibleCards.length}`;
      }

      // Inquire button
      if (modalInquireBtn) {
        const waText = encodeURIComponent(`Hello Badagry Visitors Guide! I was viewing "${title}" (${geo}) in your gallery and would love to book or inquire about a tour to this location.`);
        modalInquireBtn.href = `http://wa.me/+2347056989224?text=${waText}`;
      }

      // Original link
      if (modalViewOriginalBtn) {
        modalViewOriginalBtn.href = src;
      }
    }

    function showNext() {
      if (!currentVisibleCards.length) return;
      activeIndex = (activeIndex + 1) % currentVisibleCards.length;
      renderActiveItem();
    }

    function showPrev() {
      if (!currentVisibleCards.length) return;
      activeIndex = (activeIndex - 1 + currentVisibleCards.length) % currentVisibleCards.length;
      renderActiveItem();
    }

    // Attach click to gallery cards
    cards.forEach(card => {
      card.addEventListener('click', () => {
        openLightbox(card);
      });
    });

    if (modalCloseBtn) modalCloseBtn.addEventListener('click', closeLightbox);
    if (modalNextBtn) modalNextBtn.addEventListener('click', (e) => { e.stopPropagation(); showNext(); });
    if (modalPrevBtn) modalPrevBtn.addEventListener('click', (e) => { e.stopPropagation(); showPrev(); });

    // Keyboard support
    document.addEventListener('keydown', (e) => {
      if (!modal || !modal.classList.contains('active')) return;
      if (e.key === 'Escape') closeLightbox();
      if (e.key === 'ArrowRight') showNext();
      if (e.key === 'ArrowLeft') showPrev();
    });

    // Close when clicking modal backdrop
    if (modal) {
      modal.addEventListener('click', (e) => {
        if (e.target === modal || e.target.classList.contains('lightbox-dialog-wrap')) {
          closeLightbox();
        }
      });
    }

    // Copy direct link action
    if (modalCopyLinkBtn) {
      modalCopyLinkBtn.addEventListener('click', () => {
        if (activeIndex < 0 || activeIndex >= currentVisibleCards.length) return;
        const currentSrc = window.location.origin + currentVisibleCards[activeIndex].dataset.src;
        navigator.clipboard.writeText(currentSrc).then(() => {
          modalCopyLinkBtn.innerHTML = '<i class="fa-solid fa-check"></i> Link Copied!';
          setTimeout(() => {
            modalCopyLinkBtn.innerHTML = '<i class="fa-regular fa-copy"></i> Copy Direct Link';
          }, 2000);
        });
      });
    }
  });
  </script>
</body>
</html>

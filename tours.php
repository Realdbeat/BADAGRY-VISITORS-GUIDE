<?php
/* ============================================================
   tours.php — Badagry Visitors Guide · Expedition Results Page
   Reads ?destination=&duration=&budget= from the finder form
   and renders every matching tour card.
============================================================ */

// ── All tour data (single source of truth) ─────────────────
$allTours = [
  [
    'id'        => 'heritage-day',
    'title'     => 'Badagry Heritage Day Tour',
    'location'  => 'Badagry Historic District',
    'summary'   => 'Discover the first storey building in Nigeria, slave relics, Mobee museum, and a boat ride across the creek to the Point of No Return.',
    'image'     => '/assets/images/Badagry-Heritage-Day-Tour.jpg',
    'price'     => 75,
    'price_fmt' => '$75',
    'unit'      => 'Person',
    'duration'  => 1,
    'category'  => 'day',
    'corridor'  => 'Badagry',
    'perks'     => [['icon'=>'fa-sailboat','label'=>'Boat Cruise'],['icon'=>'fa-ticket','label'=>'All Passes'],['icon'=>'fa-user-tie','label'=>'Native Guide']],
    'badge'     => 'Best Seller',
  ],
  [
    'id'        => 'beach-creek',
    'title'     => 'Badagry Beach & Creek Leisure Day',
    'location'  => 'Badagry Marina & Creeks',
    'summary'   => 'A relaxed coastal day featuring canoe cruising along serene creeks, coconut groves, seafood lunch, and Atlantic shore relaxation.',
    'image'     => '/assets/images/Badagry-Beach-Creek-Leisure-Day.jpg',
    'price'     => 55,
    'price_fmt' => '$55',
    'unit'      => 'Person',
    'duration'  => 1,
    'category'  => 'day',
    'corridor'  => 'Badagry',
    'perks'     => [['icon'=>'fa-water','label'=>'Lagoon Canoe'],['icon'=>'fa-umbrella-beach','label'=>'Beach Access'],['icon'=>'fa-fish','label'=>'Fresh Seafood']],
    'badge'     => 'Most Relaxed',
  ],
  [
    'id'        => 'schools',
    'title'     => 'Badagry for Schools & Student Groups',
    'location'  => 'Educational Heritage Circuit',
    'summary'   => 'Curriculum-aligned educational excursion offering young learners real engagement with Nigerian and West African historical milestones.',
    'image'     => '/assets/images/Badagry-for-Schools-and-Student-Groups.jpg',
    'price'     => 45,
    'price_fmt' => '$45',
    'unit'      => 'Student',
    'duration'  => 1,
    'category'  => 'educational',
    'corridor'  => 'Badagry',
    'perks'     => [['icon'=>'fa-book-open','label'=>'Study Materials'],['icon'=>'fa-bus','label'=>'Escorted Transit'],['icon'=>'fa-chalkboard-user','label'=>'Certified Docent']],
    'badge'     => 'School Special',
  ],
  [
    'id'        => 'private-day',
    'title'     => 'Private Group Badagry Day Tour',
    'location'  => 'Exclusive Private Charter',
    'summary'   => 'Customized itinerary for families, associations, or private circles with dedicated luxury transport and personalized private historian.',
    'image'     => '/assets/images/Private-Group-Badagry-Heritage-Day-Tour.jpg',
    'price'     => 180,
    'price_fmt' => '$180',
    'unit'      => 'Group',
    'duration'  => 1,
    'category'  => 'day',
    'corridor'  => 'Badagry',
    'perks'     => [['icon'=>'fa-car','label'=>'Private Van'],['icon'=>'fa-crown','label'=>'VIP Fast-Track'],['icon'=>'fa-camera','label'=>'Tour Photos']],
    'badge'     => 'Private',
  ],
  [
    'id'        => 'heritage-beach-weekend',
    'title'     => 'Badagry Heritage & Beach Weekend',
    'location'  => 'Beach Resort & Museums',
    'summary'   => 'Overnight stay at a scenic beach resort in Badagry coupled with deep museum explorations, bonfire storytelling, and fresh ocean dining.',
    'image'     => '/assets/images/Badagry-Heritage-Beach-Weekend.jpg',
    'price'     => 195,
    'price_fmt' => '$195',
    'unit'      => 'Person',
    'duration'  => 2,
    'category'  => 'weekend',
    'corridor'  => 'Badagry',
    'perks'     => [['icon'=>'fa-bed','label'=>'Resort Lodging'],['icon'=>'fa-fire','label'=>'Evening Bonfire'],['icon'=>'fa-utensils','label'=>'Meals Covered']],
    'badge'     => 'Weekend Pick',
  ],
  [
    'id'        => 'seriki-barracoon',
    'title'     => 'Seriki Abass Barracoon & Relics Tour',
    'location'  => 'Brazilian Compound & Vlekete',
    'summary'   => "In-depth exploration of the 1840 Brazilian-style slave barracks, Chief Seriki Abass's tomb, authentic shackles, and the ancient Vlekete Slave Market.",
    'image'     => '/assets/images/Seriki-Williams-Abass-Baracoon.jpg',
    'price'     => 85,
    'price_fmt' => '$85',
    'unit'      => 'Person',
    'duration'  => 1,
    'category'  => 'day',
    'corridor'  => 'Badagry',
    'perks'     => [['icon'=>'fa-door-open','label'=>'40 Cells Access'],['icon'=>'fa-link','label'=>'Authentic Shackles'],['icon'=>'fa-landmark','label'=>'Vlekete Market']],
    'badge'     => 'Historical Deep-Dive',
  ],
  [
    'id'        => 'whispering-palms',
    'title'     => 'Whispering Palms & Marina Escape',
    'location'  => 'Whispering Palms Waterfront',
    'summary'   => 'Relaxing 2-day waterfront getaway at Whispering Palms Resort with calm Badagry creek boat cruise, coconut palms, and authentic Lagos coastal cuisine.',
    'image'     => '/assets/images/Badagry-Beach-Creek-Leisure-Day.jpg',
    'price'     => 220,
    'price_fmt' => '$220',
    'unit'      => 'Person',
    'duration'  => 2,
    'category'  => 'weekend',
    'corridor'  => 'Badagry',
    'perks'     => [['icon'=>'fa-hotel','label'=>'Waterfront Stay'],['icon'=>'fa-ship','label'=>'Creek Cruise'],['icon'=>'fa-cocktail','label'=>'Coconut Lounge']],
    'badge'     => 'Leisure Favourite',
  ],
  [
    'id'        => 'corporate-retreat',
    'title'     => 'Corporate Team Heritage Retreat',
    'location'  => 'Executive Retreat Logistics',
    'summary'   => 'Curated executive retreat combining team bonding, cultural enlightenment, premium coastal accommodation, and smooth VIP logistics across Badagry.',
    'image'     => '/assets/images/Corporate-Team-Heritage-Weekend.jpg',
    'price'     => 380,
    'price_fmt' => '$380',
    'unit'      => 'Person',
    'duration'  => 2,
    'category'  => 'weekend',
    'corridor'  => 'Badagry',
    'perks'     => [['icon'=>'fa-users-gear','label'=>'Team Workshop'],['icon'=>'fa-shield','label'=>'Security Escort'],['icon'=>'fa-champagne-glasses','label'=>'Executive Dining']],
    'badge'     => 'Corporate',
  ],
  [
    'id'        => '3-day-immersion',
    'title'     => '3-Day Badagry Heritage Immersion',
    'location'  => 'Comprehensive All-Circuit Pass',
    'summary'   => 'The ultimate Badagry circuit: 1845 First Storey Building, Gberefu Point of No Return, Seriki Abass Barracoons, Mobee Museum, and beachside relaxation.',
    'image'     => '/assets/images/Badagry.jpg',
    'price'     => 350,
    'price_fmt' => '$350',
    'unit'      => 'Person',
    'duration'  => 3,
    'category'  => 'weekend',
    'corridor'  => 'Badagry',
    'perks'     => [['icon'=>'fa-compass','label'=>'Complete Circuit'],['icon'=>'fa-hotel','label'=>'2 Nights Hotel'],['icon'=>'fa-utensils','label'=>'All Meals']],
    'badge'     => 'Most Complete',
  ],
  [
    'id'        => 'diaspora-pilgrimage',
    'title'     => 'Badagry Diaspora Roots Pilgrimage',
    'location'  => 'Diaspora Ancestral Homecoming',
    'summary'   => 'Sacred pilgrimage for African diaspora descendants connecting with ancestral roots, traditional welcome rites, ancestral reflection, and Atlantic healing sessions.',
    'image'     => '/assets/images/Diaspora-5-Day-Heritage-Homecoming-Tour.jpg',
    'price'     => 550,
    'price_fmt' => '$550',
    'unit'      => 'Person',
    'duration'  => 3,
    'category'  => 'diaspora',
    'corridor'  => 'Badagry',
    'perks'     => [['icon'=>'fa-hand-holding-heart','label'=>'Ancestral Rites'],['icon'=>'fa-dove','label'=>'Atlantic Healing'],['icon'=>'fa-certificate','label'=>'Roots Certificate']],
    'badge'     => 'Diaspora Special',
  ],
  [
    'id'        => 'zangbeto',
    'title'     => 'Zangbeto Festival Tour',
    'location'  => 'Ogu Living Cultural Heritage',
    'summary'   => 'Experience the mystical Zangbeto masquerade — the traditional guardian of the night. Featuring energetic Ogu drumming, traditional chants, and sacred dances.',
    'image'     => '/assets/images/Zangbeto-Festival-Tour.jpg',
    'price'     => 95,
    'price_fmt' => '$95',
    'unit'      => 'Person',
    'duration'  => 1,
    'category'  => 'diaspora',
    'corridor'  => 'Badagry',
    'perks'     => [['icon'=>'fa-drum','label'=>'Ogu Drumming'],['icon'=>'fa-masks-theater','label'=>'Sacred Rites'],['icon'=>'fa-circle-nodes','label'=>'Native Elder Docent']],
    'badge'     => 'Cultural Immersion',
  ],
];

// ── Read & sanitise filter params ───────────────────────────
$f_destination = trim($_GET['destination'] ?? 'all');
$f_duration    = trim($_GET['duration']    ?? 'all');
$f_budget      = trim($_GET['budget']      ?? 'all');

$validDestinations = ['all','Badagry','day','weekend','educational','diaspora'];
$validDurations    = ['all','1','2','3'];
$validBudgets      = ['all','budget','moderate','premium'];

if (!in_array($f_destination, $validDestinations)) $f_destination = 'all';
if (!in_array($f_duration,    $validDurations))    $f_duration    = 'all';
if (!in_array($f_budget,      $validBudgets))      $f_budget      = 'all';

// ── Filter logic (mirrors app.js exactly) ──────────────────
function matchesBudget(int $price, string $budget): bool {
    if ($budget === 'all')      return true;
    if ($budget === 'budget')   return $price <= 100;
    if ($budget === 'moderate') return $price > 100 && $price <= 350;
    if ($budget === 'premium')  return $price > 350;
    return true;
}

function matchesDestination(array $tour, string $destination): bool {
    if ($destination === 'all') return true;
    return ($tour['corridor'] === $destination || $tour['category'] === $destination);
}

function matchesDuration(array $tour, string $duration): bool {
    if ($duration === 'all') return true;
    return (string)$tour['duration'] === $duration;
}

$results = array_filter($allTours, function($tour) use ($f_destination, $f_duration, $f_budget) {
    return matchesDestination($tour, $f_destination)
        && matchesDuration($tour, $f_duration)
        && matchesBudget($tour['price'], $f_budget);
});

// ── Label helpers for display ───────────────────────────────
$destinationLabels = [
    'all'         => 'All Heritage & Coastal Circuits',
    'Badagry'     => 'Transatlantic Slave Route & Relics',
    'day'         => 'Single-Day Cultural Excursions',
    'weekend'     => 'Coastal Lagoon & Beach Weekends',
    'educational' => 'Educational & Student Delegations',
    'diaspora'    => 'Diaspora Roots & Sacred Masquerade',
];
$durationLabels = ['all'=>'Any Duration','1'=>'1 Day','2'=>'2 Days','3'=>'3 Days'];
$budgetLabels   = [
    'all'      => 'All Price Bands',
    'budget'   => 'Community Standard (Under $100)',
    'moderate' => 'Guided Immersion ($100-$350)',
    'premium'  => 'Private VIP ($350+)',
];

$activeFilters = [];
if ($f_destination !== 'all') $activeFilters[] = $destinationLabels[$f_destination];
if ($f_duration    !== 'all') $activeFilters[] = $durationLabels[$f_duration];
if ($f_budget      !== 'all') $activeFilters[] = $budgetLabels[$f_budget];

$resultCount = count($results);
$pageTitle   = $resultCount > 0
    ? "Found {$resultCount} Expedition" . ($resultCount !== 1 ? 's' : '') . " | Badagry Visitors Guide"
    : "No Expeditions Found | Badagry Visitors Guide";
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title><?= htmlspecialchars($pageTitle) ?></title>
  <meta name="description" content="Browse filtered Badagry guided expedition results. Find your perfect tour by journey focus, duration, and budget." />
  <link rel="canonical" href="https://badagryvisitorsguide.com.ng/tours/" />
  <meta name="robots" content="noindex, follow" />
  <meta property="og:type" content="website" />
  <meta property="og:site_name" content="Badagry Visitors Guide" />
  <meta property="og:title" content="<?= htmlspecialchars($pageTitle) ?>" />
  <meta property="og:image" content="https://badagryvisitorsguide.com.ng/assets/images/Point-of-No-Return-Monument-Badagry.jpg" />
  <link rel="icon" type="image/jpeg" href="/assets/logo.jpg" />
  <link rel="preconnect" href="https://fonts.googleapis.com" />
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
  <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:ital,wght@0,600;0,700;1,400&family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&display=swap" rel="stylesheet" />
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" />
  <link rel="stylesheet" href="/style.css?v=3.04" />
  <style>
    .results-hero {
      background: linear-gradient(135deg, #0f172a 0%, #17281f 60%, #0f172a 100%);
      padding: 70px 0 50px;
      position: relative;
      overflow: hidden;
    }
    .results-hero::before {
      content: '';
      position: absolute;
      inset: 0;
      background: url('/assets/images/Point-of-No-Return-Monument-Badagry.jpg') center/cover no-repeat;
      opacity: 0.08;
    }
    .results-hero-inner { position: relative; z-index: 1; text-align: center; }
    .results-breadcrumb {
      display: flex;
      align-items: center;
      gap: 8px;
      justify-content: center;
      font-size: 0.82rem;
      color: rgba(255,255,255,0.55);
      margin-bottom: 22px;
    }
    .results-breadcrumb a { color: rgba(255,255,255,0.65); text-decoration: none; }
    .results-breadcrumb a:hover { color: #fff; }
    .results-breadcrumb i { font-size: 0.65rem; }
    .results-hero h1 {
      font-family: var(--font-heading);
      font-size: clamp(1.8rem, 4vw, 2.8rem);
      font-weight: 800;
      color: #fff;
      margin-bottom: 12px;
      line-height: 1.2;
    }
    .results-hero h1 span { color: var(--primary); }
    .results-hero-sub {
      font-size: 1rem;
      color: rgba(255,255,255,0.65);
      max-width: 560px;
      margin: 0 auto 30px;
      line-height: 1.6;
    }
    .active-chips {
      display: flex;
      flex-wrap: wrap;
      gap: 10px;
      justify-content: center;
    }
    .filter-chip {
      display: inline-flex;
      align-items: center;
      gap: 6px;
      background: rgba(35,136,67,0.2);
      border: 1px solid rgba(35,136,67,0.45);
      color: #4ade80;
      font-size: 0.78rem;
      font-weight: 600;
      padding: 5px 13px;
      border-radius: 9999px;
    }
    .chip-all {
      background: rgba(255,255,255,0.08);
      border-color: rgba(255,255,255,0.2);
      color: rgba(255,255,255,0.7);
    }
    .refine-bar {
      background: #fff;
      border-bottom: 1px solid var(--border-color);
      padding: 16px 0;
      position: sticky;
      top: 0;
      z-index: 90;
      box-shadow: 0 2px 12px rgba(15,23,42,0.06);
    }
    .refine-inner {
      display: flex;
      align-items: center;
      flex-wrap: wrap;
      gap: 14px;
    }
    .refine-label {
      font-size: 0.82rem;
      font-weight: 700;
      color: var(--text-muted);
      text-transform: uppercase;
      letter-spacing: 0.6px;
      white-space: nowrap;
    }
    .refine-form {
      display: flex;
      flex-wrap: wrap;
      gap: 10px;
      flex: 1;
      align-items: center;
    }
    .refine-select {
      flex: 1;
      min-width: 170px;
      padding: 9px 32px 9px 14px;
      border: 1.5px solid var(--border-color);
      border-radius: 10px;
      font-family: var(--font-body);
      font-size: 0.85rem;
      color: var(--navy);
      background: #fafafa url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='12' height='8' viewBox='0 0 12 8'%3E%3Cpath d='M1 1l5 5 5-5' stroke='%23666' stroke-width='1.5' fill='none' stroke-linecap='round'/%3E%3C/svg%3E") no-repeat right 12px center;
      cursor: pointer;
      appearance: none;
      transition: border-color var(--transition-fast);
    }
    .refine-select:focus { outline: none; border-color: var(--primary); }
    .refine-submit {
      display: inline-flex;
      align-items: center;
      gap: 8px;
      padding: 9px 22px;
      background: var(--primary);
      color: #fff;
      border: none;
      border-radius: 10px;
      font-family: var(--font-body);
      font-size: 0.87rem;
      font-weight: 700;
      cursor: pointer;
      transition: background var(--transition-fast);
      white-space: nowrap;
    }
    .refine-submit:hover { background: var(--primary-hover); }
    .result-count-bar {
      display: flex;
      align-items: center;
      justify-content: space-between;
      flex-wrap: wrap;
      gap: 12px;
      padding: 28px 0 18px;
    }
    .result-count-text { font-size: 0.9rem; color: var(--text-muted); }
    .result-count-text strong { color: var(--navy); font-weight: 700; }
    .sort-back-link {
      display: inline-flex;
      align-items: center;
      gap: 7px;
      font-size: 0.85rem;
      font-weight: 600;
      color: var(--primary);
      text-decoration: none;
    }
    .sort-back-link:hover { color: var(--primary-hover); }
    .results-grid {
      display: grid;
      grid-template-columns: repeat(auto-fill, minmax(310px, 1fr));
      gap: 28px;
      padding-bottom: 60px;
    }
    .tour-card-badge {
      position: absolute;
      top: 14px;
      left: 14px;
      background: var(--primary);
      color: #fff;
      font-size: 0.72rem;
      font-weight: 700;
      text-transform: uppercase;
      letter-spacing: 0.6px;
      padding: 4px 11px;
      border-radius: 9999px;
      z-index: 2;
    }
    .no-results-wrap { text-align: center; padding: 80px 20px; }
    .no-results-icon { font-size: 4rem; color: var(--border-color); margin-bottom: 24px; }
    .no-results-wrap h2 {
      font-family: var(--font-heading);
      font-size: 1.8rem;
      font-weight: 700;
      color: var(--navy);
      margin-bottom: 12px;
    }
    .no-results-wrap p {
      font-size: 1rem;
      color: var(--text-muted);
      max-width: 480px;
      margin: 0 auto 30px;
      line-height: 1.65;
    }
    .no-results-actions { display: flex; gap: 14px; justify-content: center; flex-wrap: wrap; }
    .results-cta-strip {
      background: linear-gradient(135deg, #0f172a 0%, #17281f 100%);
      border-radius: 24px;
      padding: 50px 40px;
      text-align: center;
      margin-bottom: 60px;
      position: relative;
      overflow: hidden;
    }
    .results-cta-strip::before {
      content: '';
      position: absolute;
      top: -40px; right: -40px;
      width: 200px; height: 200px;
      border-radius: 50%;
      background: rgba(35,136,67,0.12);
    }
    .results-cta-strip h2 {
      font-family: var(--font-heading);
      font-size: clamp(1.4rem, 3vw, 2rem);
      font-weight: 800;
      color: #fff;
      margin-bottom: 12px;
    }
    .results-cta-strip p {
      color: rgba(255,255,255,0.7);
      font-size: 0.97rem;
      max-width: 540px;
      margin: 0 auto 28px;
      line-height: 1.6;
    }
    .results-cta-btns { display: flex; gap: 14px; justify-content: center; flex-wrap: wrap; }
    @media (max-width: 768px) {
      .results-hero { padding: 55px 0 36px; }
      .refine-inner { flex-direction: column; align-items: stretch; }
      .refine-label { display: none; }
      .refine-select { min-width: unset; }
      .results-grid { grid-template-columns: 1fr; }
      .results-cta-strip { padding: 36px 20px; }
    }
  </style>
</head>
<body>

  <!-- Mobile Drawer -->
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
        <li><a href="/#landmarks" class="mobile-nav-link">Destinations</a></li>
        <li><a href="/gallery/" class="mobile-nav-link">Gallery</a></li>
        <li><a href="/blog/" class="mobile-nav-link">Blog</a></li>
        <li><a href="http://wa.me/+2347056989224?text=from+google+map+or+site" target="_blank" rel="noopener" class="mobile-nav-link">Contact Us</a></li>
      </ul>
      <div class="mobile-drawer-cta">
        <a href="http://wa.me/+2347056989224?text=from+google+map+or+site" target="_blank" rel="noopener" class="btn btn-primary w-100">Book Your Tour</a>
      </div>
      <div class="drawer-contact-info">
        <p><i class="fa-solid fa-location-dot"></i> Badagry, Lagos, Nigeria</p>
        <p><i class="fa-solid fa-phone"></i> +234 705 698 9224</p>
        <p><i class="fa-solid fa-envelope"></i> info@badagryvisitorsguide.com</p>
      </div>
    </div>
  </aside>

  <!-- Header -->
  <header class="header-wrapper">
    <div class="top-bar">
      <div class="site-container top-bar-container">
        <div class="top-bar-left">
          <span class="info-item"><i class="fa-solid fa-location-dot"></i> Badagry, Lagos, Nigeria.</span>
          <span class="info-item"><i class="fa-regular fa-clock"></i> 9AM - 6PM WAT</span>
        </div>
        <div class="top-bar-right">
          <div class="top-links">
            <a href="/#faq">FAQ</a>
            <span class="divider">|</span>
            <a href="http://wa.me/+2347056989224?text=from+google+map+or+site" target="_blank" rel="noopener">Support</a>
          </div>
        </div>
      </div>
    </div>
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
          <li class="nav-item"><a href="/#tours" class="nav-link active">Expeditions</a></li>
          <li class="nav-item"><a href="/#landmarks" class="nav-link">Destinations</a></li>
          <li class="nav-item"><a href="/gallery/" class="nav-link">Gallery</a></li>
          <li class="nav-item"><a href="/blog/" class="nav-link">Journal</a></li>
          <li class="nav-item"><a href="http://wa.me/+2347056989224?text=from+google+map+or+site" target="_blank" rel="noopener" class="nav-link">Contact</a></li>
        </ul>
        <div class="nav-actions">
          <a href="http://wa.me/+2347056989224?text=from+google+map+or+site" target="_blank" rel="noopener" class="btn btn-primary nav-book-btn">
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

  <!-- Results Hero -->
  <section class="results-hero">
    <div class="site-container results-hero-inner">
      <nav class="results-breadcrumb" aria-label="Breadcrumb">
        <a href="/">Home</a>
        <i class="fa-solid fa-chevron-right"></i>
        <a href="/#search">Expedition Finder</a>
        <i class="fa-solid fa-chevron-right"></i>
        <span>Results</span>
      </nav>
      <h1>
        <?php if ($resultCount > 0): ?>
          <span><?= $resultCount ?></span> Matching Expedition<?= $resultCount !== 1 ? 's' : '' ?> Found
        <?php else: ?>
          No Matching Expeditions
        <?php endif; ?>
      </h1>
      <p class="results-hero-sub">
        <?php if (!empty($activeFilters)): ?>
          Filtered by: <strong style="color:#fff;"><?= implode(' &middot; ', array_map('htmlspecialchars', $activeFilters)) ?></strong>
        <?php else: ?>
          Showing all <?= count($allTours) ?> verified Badagry expedition packages, guided by native historians.
        <?php endif; ?>
      </p>
      <div class="active-chips">
        <?php if (empty($activeFilters)): ?>
          <span class="filter-chip chip-all"><i class="fa-solid fa-border-all"></i> All Expeditions</span>
        <?php else: ?>
          <?php foreach ($activeFilters as $chip): ?>
            <span class="filter-chip"><i class="fa-solid fa-check"></i> <?= htmlspecialchars($chip) ?></span>
          <?php endforeach; ?>
        <?php endif; ?>
      </div>
    </div>
  </section>

  <!-- Sticky Refine Bar -->
  <div class="refine-bar">
    <div class="site-container">
      <div class="refine-inner">
        <span class="refine-label"><i class="fa-solid fa-sliders"></i> Refine:</span>
        <form method="GET" action="/tours/" class="refine-form" id="refineForm">
          <select name="destination" class="refine-select" aria-label="Journey Focus">
            <option value="all"         <?= $f_destination==='all'         ? 'selected':'' ?>>All Journey Focus</option>
            <option value="Badagry"     <?= $f_destination==='Badagry'     ? 'selected':'' ?>>Slave Route &amp; Relics</option>
            <option value="day"         <?= $f_destination==='day'         ? 'selected':'' ?>>Single-Day Excursions</option>
            <option value="weekend"     <?= $f_destination==='weekend'     ? 'selected':'' ?>>Coastal Weekends</option>
            <option value="educational" <?= $f_destination==='educational' ? 'selected':'' ?>>Educational Groups</option>
            <option value="diaspora"    <?= $f_destination==='diaspora'    ? 'selected':'' ?>>Diaspora &amp; Cultural</option>
          </select>
          <select name="duration" class="refine-select" aria-label="Duration">
            <option value="all" <?= $f_duration==='all' ? 'selected':'' ?>>Any Duration</option>
            <option value="1"   <?= $f_duration==='1'   ? 'selected':'' ?>>1 Day</option>
            <option value="2"   <?= $f_duration==='2'   ? 'selected':'' ?>>2 Days</option>
            <option value="3"   <?= $f_duration==='3'   ? 'selected':'' ?>>3 Days</option>
          </select>
          <select name="budget" class="refine-select" aria-label="Budget">
            <option value="all"      <?= $f_budget==='all'      ? 'selected':'' ?>>All Price Bands</option>
            <option value="budget"   <?= $f_budget==='budget'   ? 'selected':'' ?>>Under $100</option>
            <option value="moderate" <?= $f_budget==='moderate' ? 'selected':'' ?>>$100 - $350</option>
            <option value="premium"  <?= $f_budget==='premium'  ? 'selected':'' ?>>$350+</option>
          </select>
          <button type="submit" class="refine-submit">
            <i class="fa-solid fa-magnifying-glass"></i> Update Results
          </button>
        </form>
      </div>
    </div>
  </div>

  <!-- Main Results -->
  <main>
    <section class="section-padding" style="padding-top:0;">
      <div class="site-container">

        <div class="result-count-bar">
          <p class="result-count-text">
            Showing <strong><?= $resultCount ?></strong> of <strong><?= count($allTours) ?></strong> expeditions
          </p>
          <a href="/#search" class="sort-back-link">
            <i class="fa-solid fa-arrow-left"></i> Back to Expedition Finder
          </a>
        </div>

        <?php if ($resultCount > 0): ?>
          <div class="results-grid">
            <?php foreach ($results as $tour): ?>
              <?php
                $waMsg = urlencode('from google map or site - Booking: ' . $tour['title']);
                $waUrl = "http://wa.me/+2347056989224?text={$waMsg}";
                $durationLabel = $tour['duration'] === 1 ? '1 Day' : $tour['duration'] . ' Days';
              ?>
              <article class="tour-card"
                data-category="<?= htmlspecialchars($tour['category']) ?>"
                data-duration="<?= $tour['duration'] ?>"
                data-price="<?= $tour['price'] ?>"
                data-corridor="<?= htmlspecialchars($tour['corridor']) ?>">
                <div class="tour-card-img" style="position:relative;">
                  <img src="<?= htmlspecialchars($tour['image']) ?>"
                    alt="<?= htmlspecialchars($tour['title']) ?>"
                    loading="lazy" />
                  <span class="tour-duration-tag">
                    <i class="fa-regular fa-clock"></i> <?= $durationLabel ?>
                  </span>
                  <span class="tour-card-badge"><?= htmlspecialchars($tour['badge']) ?></span>
                </div>
                <div class="tour-card-body">
                  <div class="tour-location">
                    <i class="fa-solid fa-location-dot"></i> <?= htmlspecialchars($tour['location']) ?>
                  </div>
                  <h2 class="tour-title"><?= htmlspecialchars($tour['title']) ?></h2>
                  <p class="tour-summary"><?= htmlspecialchars($tour['summary']) ?></p>
                  <div class="tour-perks">
                    <?php foreach ($tour['perks'] as $perk): ?>
                      <span class="tour-perk-item">
                        <i class="fa-solid <?= htmlspecialchars($perk['icon']) ?>"></i>
                        <?= htmlspecialchars($perk['label']) ?>
                      </span>
                    <?php endforeach; ?>
                  </div>
                  <div class="tour-card-footer">
                    <div class="tour-price">
                      <span class="price-prefix">From</span>
                      <span class="price-amount"><?= htmlspecialchars($tour['price_fmt']) ?></span>
                      <span class="price-suffix">/<?= htmlspecialchars($tour['unit']) ?></span>
                    </div>
                    <a href="<?= $waUrl ?>" target="_blank" rel="noopener" class="btn btn-sm btn-primary">
                      Reserve <i class="fa-solid fa-arrow-right"></i>
                    </a>
                  </div>
                </div>
              </article>
            <?php endforeach; ?>
          </div>

        <?php else: ?>
          <div class="no-results-wrap">
            <div class="no-results-icon"><i class="fa-solid fa-compass"></i></div>
            <h2>No Expeditions Match Your Filters</h2>
            <p>
              No tour packages match all three selected criteria. Try broadening one filter —
              for example, select <strong>Any Duration</strong> or <strong>All Price Bands</strong>.
            </p>
            <div class="no-results-actions">
              <a href="/tours/" class="btn btn-primary">
                <i class="fa-solid fa-border-all"></i> Show All Expeditions
              </a>
              <a href="http://wa.me/+2347056989224?text=from+google+map+or+site+-+Custom+tour+request"
                target="_blank" rel="noopener" class="btn btn-secondary">
                <i class="fa-brands fa-whatsapp"></i> Request Custom Tour
              </a>
            </div>
          </div>
        <?php endif; ?>

        <!-- CTA Strip -->
        <div class="results-cta-strip">
          <h2>Can't Find the Right Expedition?</h2>
          <p>
            Our native Badagry historians craft fully custom itineraries for families, diaspora groups,
            corporate teams, and school delegations — with zero hidden fees.
          </p>
          <div class="results-cta-btns">
            <a href="http://wa.me/+2347056989224?text=from+google+map+or+site+-+Inquiring+about+custom+itinerary"
              target="_blank" rel="noopener" class="btn btn-primary">
              <i class="fa-brands fa-whatsapp"></i> Speak With a Guide
            </a>
            <a href="/gallery/" class="btn btn-secondary" style="border-color:rgba(255,255,255,0.3);color:#fff;">
              <i class="fa-solid fa-images"></i> Browse Gallery
            </a>
          </div>
        </div>

      </div>
    </section>
  </main>

  <!-- Footer -->
  <footer class="site-footer">
    <div class="footer-top">
      <div class="site-container footer-grid">
        <div class="footer-brand-col">
          <a href="/" class="footer-brand">
            <img src="/assets/logo.jpg" alt="Badagry Visitors Guide" class="footer-logo-img" />
            <span class="footer-brand-name">Badagry <strong>Visitors Guide</strong></span>
          </a>
          <p class="footer-tagline">The leading authority on Badagry heritage and cultural tourism.</p>
        </div>
        <div class="footer-links-col">
          <h3 class="footer-title">Quick Links</h3>
          <ul class="footer-links-list">
            <li><a href="/">Home</a></li>
            <li><a href="/#tours">All Tours</a></li>
            <li><a href="/gallery/">Gallery</a></li>
            <li><a href="/blog/">Blog</a></li>
            <li><a href="/#faq">FAQ</a></li>
          </ul>
        </div>
        <div class="footer-links-col">
          <h3 class="footer-title">Popular Searches</h3>
          <ul class="footer-links-list">
            <li><a href="/tours/?destination=day&duration=1&budget=budget">Day Tours Under $100</a></li>
            <li><a href="/tours/?destination=weekend&duration=2&budget=all">Weekend Getaways</a></li>
            <li><a href="/tours/?destination=educational&duration=all&budget=all">Student Groups</a></li>
            <li><a href="/tours/?destination=diaspora&duration=all&budget=all">Diaspora Pilgrimages</a></li>
            <li><a href="/tours/?destination=all&duration=all&budget=premium">VIP Premium Tours</a></li>
          </ul>
        </div>
        <div class="footer-links-col">
          <h3 class="footer-title">Contact</h3>
          <ul class="footer-links-list">
            <li><i class="fa-solid fa-location-dot"></i> Badagry, Lagos, Nigeria</li>
            <li><a href="tel:+2347056989224"><i class="fa-solid fa-phone"></i> +234 705 698 9224</a></li>
            <li><a href="mailto:info@badagryvisitorsguide.com"><i class="fa-solid fa-envelope"></i> info@badagryvisitorsguide.com</a></li>
          </ul>
        </div>
      </div>
    </div>
    <div class="footer-bottom">
      <div class="site-container footer-bottom-inner">
        <p>&copy; <?= date('Y') ?> Badagry Visitors Guide. All rights reserved.</p>
        <div class="footer-bottom-links">
          <a href="/#faq">FAQs</a>
          <a href="/gallery/">Gallery</a>
          <a href="/blog/">Blog</a>
        </div>
      </div>
    </div>
  </footer>

  <!-- Floating WhatsApp Widget -->
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
          <p>Found an expedition you love? Chat to confirm your reservation!</p>
          <a href="http://wa.me/+2347056989224?text=from+google+map+or+site" target="_blank" rel="noopener"
            class="btn btn-sm btn-primary w-100" style="margin-top:10px;">
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

  <script>
  document.addEventListener('DOMContentLoaded', () => {
    // Mobile Drawer
    const mobileToggleBtn   = document.getElementById('mobileToggleBtn');
    const drawerCloseBtn    = document.getElementById('drawerCloseBtn');
    const mobileMenuDrawer  = document.getElementById('mobileMenuDrawer');
    const mobileMenuOverlay = document.getElementById('mobileMenuOverlay');
    const openDrawer  = () => { mobileMenuDrawer?.classList.add('active'); mobileMenuOverlay?.classList.add('active'); document.body.style.overflow = 'hidden'; };
    const closeDrawer = () => { mobileMenuDrawer?.classList.remove('active'); mobileMenuOverlay?.classList.remove('active'); document.body.style.overflow = ''; };
    mobileToggleBtn?.addEventListener('click', openDrawer);
    drawerCloseBtn?.addEventListener('click', closeDrawer);
    mobileMenuOverlay?.addEventListener('click', closeDrawer);
    document.querySelectorAll('.mobile-nav-link').forEach(l => l.addEventListener('click', closeDrawer));

    // WhatsApp Widget
    const waToggleBtn = document.getElementById('waToggleBtn');
    const waChatBox   = document.getElementById('waChatBox');
    const waCloseBtn  = document.getElementById('waCloseBtn');
    const waInput     = document.getElementById('waInput');
    const waSendBtn   = document.getElementById('waSendBtn');
    if (waToggleBtn && waChatBox) {
      waToggleBtn.addEventListener('click', () => waChatBox.classList.toggle('active'));
      waCloseBtn?.addEventListener('click', () => waChatBox.classList.remove('active'));
      const sendMsg = () => {
        const text = waInput?.value.trim() || '';
        const url = text
          ? `http://wa.me/+2347056989224?text=${encodeURIComponent('from tours page: ' + text)}`
          : 'http://wa.me/+2347056989224?text=from+tours+page';
        window.open(url, '_blank');
        if (waInput) waInput.value = '';
      };
      waSendBtn?.addEventListener('click', sendMsg);
      waInput?.addEventListener('keypress', e => { if (e.key === 'Enter') { e.preventDefault(); sendMsg(); } });
    }

    // Auto-submit refine selects on change
    document.querySelectorAll('.refine-select').forEach(sel => {
      sel.addEventListener('change', () => document.getElementById('refineForm').submit());
    });

    // Escape key
    document.addEventListener('keydown', e => {
      if (e.key === 'Escape') { closeDrawer(); waChatBox?.classList.remove('active'); }
    });
  });
  </script>
</body>
</html>

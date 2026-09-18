<?php
/**
 * sitemap-generator.php
 * Automated Sitemap & Image Sitemap Generator for Badagry Visitors Guide.
 * 
 * Automatically synchronizes:
 * 1. sitemap.xml       (Standard XML sitemap with all URLs + featured images)
 * 2. image-sitemap.xml (Dedicated Google Image Sitemap specification)
 * 
 * Triggered:
 * - On demand via generateSitemaps()
 * - Automatically whenever blog articles are created, edited, or deleted in admin.php
 * - Automatically whenever new images are uploaded or added to assets/images/
 * - Automatically via autoUpdateSitemapsIfModified() when file changes are detected
 */

require_once __DIR__ . '/db.php';

define('SITE_BASE_URL', 'https://badagryvisitorsguide.rav.com.ng');
define('IMAGES_DIR', __DIR__ . '/assets/images');
define('SITEMAP_MAIN_FILE', __DIR__ . '/sitemap.xml');
define('SITEMAP_IMAGE_FILE', __DIR__ . '/image-sitemap.xml');

/**
 * Curated metadata dictionary for known Badagry & corridor landmarks
 */
function getKnownImageMetadata(): array {
    return [
        'Badagry-Slave-Memorial-Statue.jpg' => [
            'title' => 'Badagry Slave Memorial and Freedom Statue',
            'caption' => 'Historic bronze monument commemorating liberation and ancestral resilience during the transatlantic slave trade in Badagry, Lagos State.',
            'geo' => 'Badagry, Lagos State, Nigeria'
        ],
        'Point-of-No-Return-Monument-Badagry.jpg' => [
            'title' => 'Point of No Return Monument on Gberefu Peninsula, Badagry',
            'caption' => 'Historic embarkation monument standing along the Atlantic coast on Gberefu Island where slave ships departed.',
            'geo' => 'Gberefu Peninsula, Badagry, Nigeria'
        ],
        'First-Storey-Building-Badagry.jpg' => [
            'title' => 'First Storey Building in Nigeria (Built 1845)',
            'caption' => 'Historic CMS mission home in Badagry where Bishop Samuel Ajayi Crowther translated the English Bible into Yoruba language.',
            'geo' => 'Marina Badagry, Lagos State, Nigeria'
        ],
        'Seriki-Williams-Abass-Baracoon.jpg' => [
            'title' => 'Seriki Williams Abass Brazilian Baracoon & Slave Relics Museum',
            'caption' => 'Preserved 19th-century Brazilian barracoon rooms, original slave trade chains, and cultural artifacts in Badagry.',
            'geo' => 'Badagry, Lagos State, Nigeria'
        ],
        'Badagry-Heritage-Museum.jpg' => [
            'title' => 'Badagry Heritage Museum',
            'caption' => 'Curated gallery of 19th-century colonial documents, shackles, slave ship manifests, and historical artifacts in Badagry.',
            'geo' => 'Badagry, Lagos, Nigeria'
        ],
        'Badagry-Heritage-Day-Tour.jpg' => [
            'title' => 'Badagry Heritage Day Tour Experience',
            'caption' => 'Tour clients exploring the Badagry slave route, ancient shrines, and local historical museums with native guides.',
            'geo' => 'Badagry, Lagos, Nigeria'
        ],
        'Route-to-Unknown-Destination.jpg' => [
            'title' => 'Point of No Return Trail - Gberefu Island Slave Route',
            'caption' => 'Historical walking pilgrimage along the trail across Gberefu peninsula leading to the Atlantic Ocean.',
            'geo' => 'Gberefu Island, Badagry, Nigeria'
        ],
        'Monument-Amazone-Benin-Republic.jpg' => [
            'title' => 'Monument Amazone Benin Republic',
            'caption' => 'Statue of the Agoodjie Dahomey warrior woman in Cotonou visited during cross-border heritage expeditions.',
            'geo' => 'Cotonou, Littoral, Benin Republic'
        ],
        'Gate-of-No-Return-Ouidah-Benin-Republic.jpg' => [
            'title' => 'Gate of No Return (Porte du Non-Retour) Ouidah',
            'caption' => 'UNESCO World Heritage memorial monument on the Atlantic shore of Ouidah, Benin Republic.',
            'geo' => 'Ouidah, Atlantique, Benin Republic'
        ],
        'Crafts-Market-Ouidah-with-Tayo.jpg' => [
            'title' => 'Ouidah Artisanal Crafts Market Guided Tour',
            'caption' => 'Tour travelers browsing authentic bronze castings, wood sculptures, and handwoven textiles with tour guide Tayo.',
            'geo' => 'Ouidah, Benin Republic'
        ],
        'Ouidah-Snake-Temple.jpg' => [
            'title' => 'Temple of Pythons (Temple des Pythons) Ouidah',
            'caption' => 'Sacred royal python cultural sanctuary and Vodun spiritual immersion in historic Ouidah.',
            'geo' => 'Ouidah, Benin Republic'
        ],
        'Prince-Lorenzo-Beach-Lome-Togo.jpg' => [
            'title' => 'Prince Lorenzo Beach Lome Togo',
            'caption' => 'Relaxing Atlantic coastline, golden palm-fringed sands, and ocean breeze in Lome, Togo.',
            'geo' => 'Lome, Maritime, Togo'
        ],
        '5-Day-Nigeria-Benin-Togo-Tour.jpg' => [
            'title' => '5-Day Tri-Nation West Africa Grand Heritage Expedition',
            'caption' => 'Comprehensive travel route across Badagry, Cotonou, Ouidah, and Lome with seamless border arrangements.',
            'geo' => 'West Africa Coastal Corridor'
        ],
        'Badagry-Beach-Creek-Leisure-Day.jpg' => [
            'title' => 'Badagry Beach & Creek Leisure Day Tour',
            'caption' => 'Waterfront creek boat rides, fresh coconut tasting, and beachside relaxation along Badagry coast.',
            'geo' => 'Badagry, Lagos, Nigeria'
        ],
        'Badagry-Benin-4-Days-Heritage-Tour.jpg' => [
            'title' => 'Badagry & Benin Republic 4-Day Heritage Journey',
            'caption' => 'Four-day cultural journey linking Badagry slave relics with Dahomey kingdom historical sites in Benin Republic.',
            'geo' => 'Nigeria & Benin Republic'
        ],
        'Badagry-Cotonou-Lome-Weekend-Tour.jpg' => [
            'title' => 'Badagry, Cotonou & Lome Weekend Tour',
            'caption' => 'Exciting weekend getaway discovering top heritage landmarks and coastal cuisine across three West African countries.',
            'geo' => 'West Africa Coastal Corridor'
        ],
        'Corporate-Team-Heritage-Weekend.jpg' => [
            'title' => 'Corporate Team Heritage Weekend in Badagry',
            'caption' => 'Company retreats and executive team bonding experiences centered around cultural learning and relaxation.',
            'geo' => 'Badagry, Lagos, Nigeria'
        ],
        'Diaspora-5-Day-Heritage-Homecoming-Tour.jpg' => [
            'title' => 'Diaspora 5-Day Heritage Homecoming Tour',
            'caption' => 'Meaningful pilgrimage for African diaspora travelers tracing ancestral roots through Badagry and Ouidah.',
            'geo' => 'Badagry & Benin Republic'
        ],
        'Babs-Dock-3.jpg' => [
            'title' => 'Babs Dock Lake Ganvie Benin Republic',
            'caption' => 'Scenic boat cruise and lakeside relaxation at Babs Dock near Ganvie stilt city in Benin Republic.',
            'geo' => 'Lake Nokoue, Benin Republic'
        ],
        'Grand-Marche.jpg' => [
            'title' => 'Grand Marche Cultural Fabric and Artisan Market',
            'caption' => 'Exploration of traditional West African textiles, handmade jewelry, and authentic spices in bustling markets.',
            'geo' => 'Cotonou, Benin Republic'
        ],
        'Pendjari-National-Park-Signage.jpg' => [
            'title' => 'Pendjari National Park Wildlife Safari',
            'caption' => 'Wildlife and nature reserve expeditions in the northern savannas of Benin Republic.',
            'geo' => 'Pendjari, Atakora, Benin Republic'
        ],
        'Porto-Novo.jpg' => [
            'title' => 'Porto-Novo Historical Capital Tour',
            'caption' => 'Afro-Brazilian architecture and ancient royal palaces in Porto-Novo, official capital of Benin Republic.',
            'geo' => 'Porto-Novo, Oueme, Benin Republic'
        ],
        'Heritage-Tours-Badagry-Slave-Trade.jpg' => [
            'title' => 'Heritage Tours Badagry Slave Trade',
            'caption' => 'Educational expeditions recounting centuries of historical trade, abolition, and cultural continuity.',
            'geo' => 'Badagry, Lagos, Nigeria'
        ],
        'eYellow-Card-2-e1787431293225-420x347.jpg' => [
            'title' => 'How to Get a Yellow Card in Nigeria in 2026',
            'caption' => 'Step-by-step yellow fever card portal guide and port health vaccination process for Nigerian cross-border travelers.',
            'geo' => 'Lagos, Nigeria'
        ],
        'Point-of-No-Return-420x347.jpg' => [
            'title' => 'What It Feels Like to Stand at the Point of No Return',
            'caption' => 'Reflections along the Gberefu peninsula trail and ocean horizon in Badagry.',
            'geo' => 'Badagry, Lagos, Nigeria'
        ],
        'Central-Lagos-to-Badagry-Map-420x347.jpg' => [
            'title' => 'How to Get from Central Lagos to Badagry in 2026',
            'caption' => 'Travel route map and transportation guide from Ikeja and Victoria Island to Badagry expressway.',
            'geo' => 'Lagos State, Nigeria'
        ],
        'Badagry-Heritage-Museum-1-420x347.jpg' => [
            'title' => 'Badagry Heritage Museum & Complete Badagry Guide',
            'caption' => 'Visitor advice, top landmarks, opening hours, admission, and local customs in Badagry.',
            'geo' => 'Badagry, Lagos, Nigeria'
        ]
    ];
}

/**
 * Formats a clean human-readable title from an image filename
 */
function humanizeImageFilename(string $filename): string {
    $name = pathinfo($filename, PATHINFO_FILENAME);
    // Remove dimension suffixes like -420x347 or -scaled
    $name = preg_replace('/-(\d+x\d+|scaled)$/i', '', $name);
    // Replace hyphens and underscores with spaces
    $name = str_replace(['-', '_'], ' ', $name);
    // Capitalize words
    return ucwords(trim($name));
}

/**
 * Scans assets/images and returns all valid image records with metadata
 */
function getAllProjectImages(): array {
    $images = [];
    $known = getKnownImageMetadata();

    if (is_dir(IMAGES_DIR)) {
        $files = scandir(IMAGES_DIR);
        foreach ($files as $file) {
            if ($file === '.' || $file === '..') continue;
            $ext = strtolower(pathinfo($file, PATHINFO_EXTENSION));
            if (!in_array($ext, ['jpg', 'jpeg', 'png', 'webp'])) continue;

            $meta = $known[$file] ?? null;
            $title = $meta['title'] ?? humanizeImageFilename($file);
            $caption = $meta['caption'] ?? ($title . ' - Authentic tour experience with Badagry Visitors Guide.');
            $geo = $meta['geo'] ?? 'Badagry, Lagos State, Nigeria';

            $images[$file] = [
                'filename' => $file,
                'url' => SITE_BASE_URL . '/assets/images/' . $file,
                'title' => $title,
                'caption' => $caption,
                'geo' => $geo,
                'mtime' => filemtime(IMAGES_DIR . '/' . $file)
            ];
        }
    }

    // Also include logo
    if (file_exists(__DIR__ . '/assets/logo.jpg')) {
        $images['logo.jpg'] = [
            'filename' => 'logo.jpg',
            'url' => SITE_BASE_URL . '/assets/logo.jpg',
            'title' => 'Badagry Visitors Guide Logo',
            'caption' => 'Official logo of Badagry Visitors Guide tour operator.',
            'geo' => 'Badagry, Lagos State, Nigeria',
            'mtime' => filemtime(__DIR__ . '/assets/logo.jpg')
        ];
    }

    return $images;
}

/**
 * Escapes strings specifically for XML 1.0 specifications
 */
function xmlEscape(string $str): string {
    return htmlspecialchars($str, ENT_XML1 | ENT_QUOTES, 'UTF-8');
}

/**
 * Main Function: Generates and writes both sitemap.xml and image-sitemap.xml
 *
 * @return array Status report of generated sitemaps
 */
function generateSitemaps(): array {
    $now = date('Y-m-d');
    $db = getDB();
    $images = getAllProjectImages();

    // Fetch published blog posts from SQLite
    $posts = [];
    try {
        $stmt = $db->query("SELECT id, title, slug, excerpt, featured_image, published_at FROM posts WHERE status = 'published' ORDER BY published_at DESC");
        $posts = $stmt->fetchAll();
    } catch (Exception $e) {
        $posts = [];
    }

    /* -------------------------------------------------------------
       1. GENERATE sitemap.xml (Main XML Sitemap)
       ------------------------------------------------------------- */
    $mainXml = '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
    $mainXml .= '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9"' . "\n";
    $mainXml .= '        xmlns:image="http://www.google.com/schemas/sitemap-image/1.1">' . "\n";

    // Home Page
    $mainXml .= "  <!-- Main Home Page -->\n";
    $mainXml .= "  <url>\n";
    $mainXml .= "    <loc>" . xmlEscape(SITE_BASE_URL . '/') . "</loc>\n";
    $mainXml .= "    <lastmod>{$now}</lastmod>\n";
    $mainXml .= "    <changefreq>weekly</changefreq>\n";
    $mainXml .= "    <priority>1.0</priority>\n";

    // Priority landmarks for main sitemap
    $priorityHomeImages = [
        'Badagry-Slave-Memorial-Statue.jpg',
        'Point-of-No-Return-Monument-Badagry.jpg',
        'First-Storey-Building-Badagry.jpg',
        'Seriki-Williams-Abass-Baracoon.jpg',
        'Monument-Amazone-Benin-Republic.jpg',
        'Gate-of-No-Return-Ouidah-Benin-Republic.jpg',
        'Badagry-Heritage-Museum.jpg',
        'Badagry-Heritage-Day-Tour.jpg',
        'Crafts-Market-Ouidah-with-Tayo.jpg',
        'Route-to-Unknown-Destination.jpg',
        'Ouidah-Snake-Temple.jpg',
        'Prince-Lorenzo-Beach-Lome-Togo.jpg',
        '5-Day-Nigeria-Benin-Togo-Tour.jpg',
        'Babs-Dock-3.jpg',
        'Grand-Marche.jpg'
    ];

    foreach ($priorityHomeImages as $imgName) {
        if (isset($images[$imgName])) {
            $img = $images[$imgName];
            $mainXml .= "    <image:image>\n";
            $mainXml .= "      <image:loc>" . xmlEscape($img['url']) . "</image:loc>\n";
            $mainXml .= "      <image:title>" . xmlEscape($img['title']) . "</image:title>\n";
            $mainXml .= "      <image:caption>" . xmlEscape($img['caption']) . "</image:caption>\n";
            $mainXml .= "    </image:image>\n";
        }
    }
    $mainXml .= "  </url>\n\n";

    // Blog Catalog
    $mainXml .= "  <!-- Blog / Travel Journal Catalog -->\n";
    $mainXml .= "  <url>\n";
    $mainXml .= "    <loc>" . xmlEscape(SITE_BASE_URL . '/blog/') . "</loc>\n";
    $mainXml .= "    <lastmod>{$now}</lastmod>\n";
    $mainXml .= "    <changefreq>daily</changefreq>\n";
    $mainXml .= "    <priority>0.9</priority>\n";
    $mainXml .= "  </url>\n\n";

    // Individual Blog Posts
    $mainXml .= "  <!-- Published Travel Journal Posts -->\n";
    foreach ($posts as $post) {
        $postDate = !empty($post['published_at']) ? date('Y-m-d', strtotime($post['published_at'])) : $now;
        $postUrl = SITE_BASE_URL . '/blog/' . $post['slug'];

        $mainXml .= "  <url>\n";
        $mainXml .= "    <loc>" . xmlEscape($postUrl) . "</loc>\n";
        $mainXml .= "    <lastmod>{$postDate}</lastmod>\n";
        $mainXml .= "    <changefreq>monthly</changefreq>\n";
        $mainXml .= "    <priority>0.8</priority>\n";

        // Featured Image if present
        if (!empty($post['featured_image'])) {
            $featImgFile = basename($post['featured_image']);
            $featImgUrl = SITE_BASE_URL . '/' . ltrim($post['featured_image'], '/');
            $featImgTitle = $images[$featImgFile]['title'] ?? $post['title'];

            $mainXml .= "    <image:image>\n";
            $mainXml .= "      <image:loc>" . xmlEscape($featImgUrl) . "</image:loc>\n";
            $mainXml .= "      <image:title>" . xmlEscape($featImgTitle) . "</image:title>\n";
            $mainXml .= "    </image:image>\n";
        }
        $mainXml .= "  </url>\n";
    }
    $mainXml .= "</urlset>\n";

    /* -------------------------------------------------------------
       2. GENERATE image-sitemap.xml (Dedicated Google Image Sitemap)
       ------------------------------------------------------------- */
    $imgXml = '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
    $imgXml .= '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9"' . "\n";
    $imgXml .= '        xmlns:image="http://www.google.com/schemas/sitemap-image/1.1">' . "\n\n";
    $imgXml .= "  <!-- ========================================================\n";
    $imgXml .= "       Badagry Visitors Guide - Automated Image Sitemap\n";
    $imgXml .= "       Generated: {$now} | Total Images: " . count($images) . "\n";
    $imgXml .= "  ======================================================== -->\n\n";

    // Home Page with all tour and landmark images
    $imgXml .= "  <url>\n";
    $imgXml .= "    <loc>" . xmlEscape(SITE_BASE_URL . '/') . "</loc>\n";
    $imgXml .= "    <lastmod>{$now}</lastmod>\n";
    $imgXml .= "    <changefreq>weekly</changefreq>\n";
    $imgXml .= "    <priority>1.0</priority>\n\n";

    foreach ($images as $img) {
        $imgXml .= "    <image:image>\n";
        $imgXml .= "      <image:loc>" . xmlEscape($img['url']) . "</image:loc>\n";
        $imgXml .= "      <image:title>" . xmlEscape($img['title']) . "</image:title>\n";
        $imgXml .= "      <image:caption>" . xmlEscape($img['caption']) . "</image:caption>\n";
        if (!empty($img['geo'])) {
            $imgXml .= "      <image:geo_location>" . xmlEscape($img['geo']) . "</image:geo_location>\n";
        }
        $imgXml .= "    </image:image>\n";
    }
    $imgXml .= "  </url>\n\n";

    // Travel Journal & Individual Posts
    $imgXml .= "  <url>\n";
    $imgXml .= "    <loc>" . xmlEscape(SITE_BASE_URL . '/blog/') . "</loc>\n";
    $imgXml .= "    <lastmod>{$now}</lastmod>\n";
    $imgXml .= "    <changefreq>daily</changefreq>\n";
    $imgXml .= "    <priority>0.9</priority>\n";
    if (isset($images['Badagry-Heritage-Museum-1-420x347.jpg'])) {
        $mImg = $images['Badagry-Heritage-Museum-1-420x347.jpg'];
        $imgXml .= "    <image:image>\n";
        $imgXml .= "      <image:loc>" . xmlEscape($mImg['url']) . "</image:loc>\n";
        $imgXml .= "      <image:title>" . xmlEscape($mImg['title']) . "</image:title>\n";
        $imgXml .= "      <image:caption>Badagry Visitors Guide Travel Journal &amp; Articles</image:caption>\n";
        $imgXml .= "    </image:image>\n";
    }
    $imgXml .= "  </url>\n\n";

    foreach ($posts as $post) {
        $postDate = !empty($post['published_at']) ? date('Y-m-d', strtotime($post['published_at'])) : $now;
        $postUrl = SITE_BASE_URL . '/blog/' . $post['slug'];

        $imgXml .= "  <url>\n";
        $imgXml .= "    <loc>" . xmlEscape($postUrl) . "</loc>\n";
        $imgXml .= "    <lastmod>{$postDate}</lastmod>\n";
        $imgXml .= "    <changefreq>monthly</changefreq>\n";
        $imgXml .= "    <priority>0.8</priority>\n";

        if (!empty($post['featured_image'])) {
            $featFile = basename($post['featured_image']);
            $featUrl = SITE_BASE_URL . '/' . ltrim($post['featured_image'], '/');
            $featTitle = $images[$featFile]['title'] ?? $post['title'];
            $featCaption = $images[$featFile]['caption'] ?? ($post['title'] . ' - Travel Guide');

            $imgXml .= "    <image:image>\n";
            $imgXml .= "      <image:loc>" . xmlEscape($featUrl) . "</image:loc>\n";
            $imgXml .= "      <image:title>" . xmlEscape($featTitle) . "</image:title>\n";
            $imgXml .= "      <image:caption>" . xmlEscape($featCaption) . "</image:caption>\n";
            $imgXml .= "    </image:image>\n";
        }
        $imgXml .= "  </url>\n";
    }

    $imgXml .= "</urlset>\n";

    // Write both files with exclusive locks
    file_put_contents(SITEMAP_MAIN_FILE, $mainXml, LOCK_EX);
    file_put_contents(SITEMAP_IMAGE_FILE, $imgXml, LOCK_EX);

    return [
        'success' => true,
        'posts_count' => count($posts),
        'images_count' => count($images),
        'generated_at' => date('Y-m-d H:i:s'),
        'sitemap_main' => SITEMAP_MAIN_FILE,
        'sitemap_images' => SITEMAP_IMAGE_FILE
    ];
}

/**
 * Detects if any image in assets/images or SQLite database is newer than the sitemaps.
 * If yes, regenerates sitemaps automatically.
 *
 * @param int $throttleSeconds Minimum seconds between checks to preserve performance
 * @return bool True if sitemaps were updated, false otherwise
 */
function autoUpdateSitemapsIfModified(int $throttleSeconds = 30): bool {
    $lockFile = __DIR__ . '/database/.sitemap_check.lock';
    $now = time();

    if (file_exists($lockFile)) {
        $lastCheck = (int)@file_get_contents($lockFile);
        if (($now - $lastCheck) < $throttleSeconds) {
            return false; // Checked recently, skip to keep response time ultra-fast
        }
    }
    @file_put_contents($lockFile, (string)$now, LOCK_EX);

    $sitemapMtime = file_exists(SITEMAP_IMAGE_FILE) ? filemtime(SITEMAP_IMAGE_FILE) : 0;
    $dbMtime = file_exists(DB_PATH) ? filemtime(DB_PATH) : 0;

    $needsUpdate = false;

    // Check if database was updated after sitemap
    if ($dbMtime > $sitemapMtime) {
        $needsUpdate = true;
    } else {
        // Check if any image was added or edited after sitemap
        if (is_dir(IMAGES_DIR)) {
            $files = scandir(IMAGES_DIR);
            foreach ($files as $file) {
                if ($file === '.' || $file === '..') continue;
                $filePath = IMAGES_DIR . '/' . $file;
                if (filemtime($filePath) > $sitemapMtime) {
                    $needsUpdate = true;
                    break;
                }
            }
        }
    }

    if ($needsUpdate) {
        generateSitemaps();
        return true;
    }

    return false;
}

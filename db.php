<?php
// db.php - SQLite Database Connection and Initialization for Badagry Visitors Guide Blog

define('DB_PATH', __DIR__ . '/database/blog.db');

function getDB(): PDO {
    static $pdo = null;
    if ($pdo === null) {
        $dbDir = dirname(DB_PATH);
        if (!is_dir($dbDir)) {
            mkdir($dbDir, 0777, true);
        }

        $isNew = !file_exists(DB_PATH);
        $pdo = new PDO('sqlite:' . DB_PATH);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);

        // Enable WAL mode for better concurrency and performance
        $pdo->exec('PRAGMA journal_mode = WAL;');

        if ($isNew) {
            initDatabase($pdo);
        }
    }
    return $pdo;
}

function initDatabase(PDO $pdo): void {
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS posts (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            title TEXT NOT NULL,
            slug TEXT UNIQUE NOT NULL,
            excerpt TEXT,
            content TEXT NOT NULL,
            featured_image TEXT,
            author TEXT DEFAULT 'Badagry Visitors Guide',
            category TEXT DEFAULT 'Travel Guide',
            read_time TEXT DEFAULT '5 min read',
            views INTEGER DEFAULT 0,
            published_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            status TEXT DEFAULT 'published'
        );
        CREATE INDEX IF NOT EXISTS idx_posts_slug ON posts(slug);
        CREATE INDEX IF NOT EXISTS idx_posts_status ON posts(status);
        CREATE INDEX IF NOT EXISTS idx_posts_category ON posts(category);
    ");

    seedInitialPosts($pdo);
}

function seedInitialPosts(PDO $pdo): void {
    $posts = [
        [
            'title' => 'How to Get a Yellow Card in Nigeria in 2026: A Simple Travel Guide',
            'slug' => 'how-to-get-yellow-card-in-nigeria-2026',
            'excerpt' => 'A complete walkthrough of the Port Health Services process, online payment portal, vaccination centres, and border clearance verification for cross-border travelers.',
            'category' => 'Border Crossing & Visas',
            'read_time' => '9 min read',
            'published_at' => '2026-08-22 10:00:00',
            'featured_image' => 'assets/images/eYellow-Card-2-e1787431293225-420x347.jpg',
            'content' => '<h2>Understanding the Nigerian e-Yellow Card Requirement</h2>
<p>If you plan to cross the land border from Nigeria into Benin Republic or travel onward to Togo and Ghana, having a valid <strong>e-Yellow Card (International Certificate of Vaccination or Prophylaxis)</strong> is an essential legal requirement. The yellow fever vaccine protects travelers from viral hemorrhagic illness and is scrutinized by Port Health officials at both Seme Border and international airports.</p>

<h3>Step-by-Step Application Process</h3>
<ol>
    <li><strong>Register on the Official Portal:</strong> Visit the official Federal Ministry of Health Port Health Services portal. Fill in your passport information accurately.</li>
    <li><strong>Generate Remita RRR:</strong> Make the statutory payment (approximately ₦2,000 to ₦2,500) online via debit card or bank branch.</li>
    <li><strong>Visit a Port Health Centre:</strong> In Lagos, key centers include the Port Health Office near Muritala Muhammed International Airport, Ikeja, and the Port Health Post near Marina/Apapa.</li>
    <li><strong>Receive the Yellow Fever Vaccine:</strong> The vaccine takes 10 days to become biologically active and legally valid for international border clearance.</li>
    <li><strong>Biometric Card Issuance:</strong> Ensure the unique QR code on the back of your yellow card scans accurately using a smartphone camera.</li>
</ol>

<div class="blog-callout">
    <h4><i class="fa-solid fa-circle-exclamation"></i> Essential Border Tip</h4>
    <p>Do not wait until the day of travel! Seme Border immigration officers enforce the 10-day post-inoculation validity strictly. Book your vaccination at least two weeks before your tour date.</p>
</div>

<h3>What to Expect at Seme Border</h3>
<p>When traveling on our guided cross-border tours to Cotonou or Ouidah, our tour leads assist clients with immigration queues. Having your original passport, stamped yellow card, and travel manifest ready ensures swift clearance in under 15 minutes.</p>'
        ],
        [
            'title' => 'What It Feels Like to Stand at the Point of No Return',
            'slug' => 'what-it-feels-like-to-stand-at-point-of-no-return',
            'excerpt' => 'Personal reflection on walking the somber path through the Gberefu peninsula toward the Atlantic shore where millions took their last steps on native soil.',
            'category' => 'History & Heritage',
            'read_time' => '7 min read',
            'published_at' => '2026-03-27 14:30:00',
            'featured_image' => 'assets/images/Point-of-No-Return-420x347.jpg',
            'content' => '<h2>The Journey Across the Badagry Creek</h2>
<p>The journey to the Point of No Return does not begin on foot. It starts with a quiet wooden boat crossing from the Badagry Marina across the gentle waters of the Ologe lagoon to the Gberefu peninsula. As the boat engines quiet near the shore, the rustling coconut palms signal the start of one of Africa’s most sacred and reflective memorial routes.</p>

<h3>Walking the Historic Slave Route (The Gberefu Trail)</h3>
<p>The route stretches approximately 1.5 kilometers across sandy paths between the inland lagoon and the open Atlantic ocean. Between 1500 and the late 19th century, captured men, women, and children were marched in chains along this exact sandy strip under the tropical sun.</p>

<div class="blog-quote">
    <blockquote>"You can feel the heavy history with every step. The silence between the palms carries stories words cannot describe."</blockquote>
</div>

<h3>The Attenuation Well (The Well of Memory Loss)</h3>
<p>Midway down the trail sits the historical Attenuation Well. Captives were forced to drink from this enchanted water, which slave merchants believed would make them lose their memories and break resistance before boarding European slave vessels.</p>

<h3>Standing at the Atlantic Memorial Arch</h3>
<p>Reaching the Atlantic coast, the roar of the surf breaks the stillness. Where iron ships once waited beyond the breakers, a towering commemorative archway now stands as a monument of remembrance, healing, and African resilience.</p>
<p>Visitors frequently describe this pilgrimage as deeply emotional and spiritually transformative. Many leave flowers or take quiet moments of reflection with our indigenous historian guides.</p>'
        ],
        [
            'title' => 'How to Get from Central Lagos to Badagry in 2026',
            'slug' => 'how-to-get-from-central-lagos-to-badagry-2026',
            'excerpt' => 'Comparing road options, the Lagos-Badagry Expressway progress, water transit via CMS jetty, and private escorted transfers for tourists.',
            'category' => 'Travel Logistics',
            'read_time' => '6 min read',
            'published_at' => '2026-03-26 11:00:00',
            'featured_image' => 'assets/images/Central-Lagos-to-Badagry-Map-420x347.jpg',
            'content' => '<h2>Navigating to Lagos State\'s Most Historic Coastal City</h2>
<p>Badagry sits approximately 65 kilometers west of Lagos Island, right along the historic Gulf of Guinea corridor leading to the Republic of Benin. Over the years, road works and public transport options have evolved dramatically. Here is everything you need to know about traveling to Badagry comfortably in 2026.</p>

<h3>Option 1: Private Tour Escort / Chartered Vehicle (Recommended)</h3>
<p>For visitors, families, and international travelers, booking an escorted tour vehicle through <strong>Badagry Visitors Guide</strong> is the safest, most comfortable choice. Vehicles depart central pickup points (Victoria Island, Ikeja, or Lekki) between 7:00 AM and 7:30 AM to beat early traffic, arriving in Badagry in approximately 90 minutes.</p>

<h3>Option 2: The Modern Lagos-Badagry Expressway</h3>
<p>Significant portions of the 10-lane Lagos-Badagry International Expressway are completed. Driving from Mile 2 through Trade Fair, LASU gate, and Okokomaiko towards Agbara and Badagry is considerably smoother than past years.</p>

<h3>Option 3: Water Transportation (Scenic Boat Ride)</h3>
<p>For adventure enthusiasts, ferry services run from CMS/Marina Jetty and Liverpool Jetty in Apapa directly through the inland waterways to Badagry Marina. The boat trip takes about 60 to 75 minutes and offers breathtaking views of traditional fishing villages, mangrove ecosystems, and coastal waterways.</p>

<div class="blog-callout">
    <h4><i class="fa-solid fa-lightbulb"></i> Recommended Tour Schedule</h4>
    <p>We recommend starting your day trip on a Saturday or Sunday morning by 7:30 AM. This gives you ample time to visit the First Storey Building, Badagry Heritage Museum, and Gberefu Peninsula before sunset.</p>
</div>'
        ],
        [
            'title' => 'Your Complete Badagry Travel Guide for 2026',
            'slug' => 'complete-badagry-travel-guide-2026',
            'excerpt' => 'Where to stay, authentic local dishes to savor, museum opening hours, photo etiquette, and essential regional tips for first-time visitors.',
            'category' => 'Travel Guides',
            'read_time' => '7 min read',
            'published_at' => '2026-03-26 09:15:00',
            'featured_image' => 'assets/images/Badagry-Heritage-Museum-1-420x347.jpg',
            'content' => '<h2>Welcome to Ancient Badagry</h2>
<p>Founded in the early 15th century, Badagry is a town steeped in coastal culture, ancient Awori and Ogu (Egun) traditions, and pivotal historical landmarks that shaped the history of West Africa. Whether you are coming for a weekend getaway or an in-depth academic tour, this guide covers what you need.</p>

<h3>Top 5 Sites You Must Visit</h3>
<ul>
    <li><strong>The First Storey Building in Nigeria (1845):</strong> Walk inside the mission house where Bishop Samuel Ajayi Crowther translated the Holy Bible into Yoruba.</li>
    <li><strong>Badagry Heritage Museum:</strong> Housed in the 1863 British colonial District Officer’s quarters, featuring eight curated galleries of slave relics and trade records.</li>
    <li><strong>Seriki Williams Abass Brazilian Barracoon:</strong> 40 slave cells that illustrate the domestic slave operations and Afro-Brazilian architectural influences.</li>
    <li><strong>Mobee Royal Family Slave Relics Museum:</strong> Preserves original chains, shackles, mouth locks, and punishment collars used during the trade.</li>
    <li><strong>Point of No Return & Gberefu Island:</strong> The final embarkation path on the Atlantic ocean.</li>
</ul>

<h3>Culinary Highlights: What to Eat</h3>
<p>Badagry’s local cuisine features exceptional Ogu delicacies. Do not leave without trying:</p>
<ul>
    <li><strong>Ajaragan (Ogu style fresh seafood fish soup):</strong> Made with freshly caught saltwater fish from the Atlantic or creek.</li>
    <li><strong>Tuwo and local vegetable sauces:</strong> Prepared with authentic local spices.</li>
    <li><strong>Fresh Coconuts & Palm Wine:</strong> Badagry is renowned as the coconut capital of Nigeria.</li>
</ul>

<h3>Best Time to Visit</h3>
<p>Badagry can be visited year-round. The dry season from November to April offers sunny weather ideal for beach walks, lagoon boat rides, and festival celebrations such as the Badagry Diaspora and Heritage Festival.</p>'
        ]
    ];

    $stmt = $pdo->prepare("
        INSERT INTO posts (title, slug, excerpt, content, featured_image, category, read_time, published_at)
        VALUES (:title, :slug, :excerpt, :content, :featured_image, :category, :read_time, :published_at)
    ");

    foreach ($posts as $p) {
        $stmt->execute($p);
    }
}

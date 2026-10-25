<?php
require_once __DIR__ . '/db.php';
require_once __DIR__ . '/sitemap-generator.php';
autoUpdateSitemapsIfModified(60);

try {
    $db = getDB();
    $stmt = $db->query("SELECT * FROM posts WHERE status = 'published' ORDER BY published_at DESC LIMIT 4");
    $topPosts = $stmt ? $stmt->fetchAll() : [];
} catch (Throwable $e) {
    $topPosts = [];
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Badagry Heritage &amp; Cultural Tours - Historical Slave Routes &amp; Museums | Badagry Visitors Guide</title>
  <meta name="description"
    content="Discover Nigeria's most powerful historical landmarks with Badagry Visitors Guide. Guided tours of the 1845 First Storey Building, Point of No Return on Gberefu Island, Brazilian Slave Barracoons, and serene creek boat cruises." />
  <meta name="keywords"
    content="Badagry tour, Badagry slave route, Point of No Return Badagry, First Storey Building Nigeria, Seriki Williams Abass Barracoon, Badagry Heritage Museum, Mobee Slave Relics, Gberefu Island boat ride, Whispering Palms Badagry, Badagry tour guide, Lagos heritage tourism" />
  <meta name="author" content="Badagry Visitors Guide" />

  <!-- Canonical URL -->
  <link rel="canonical" href="https://badagryvisitorsguide.com.ng/" />

  <!-- Google Search & Crawler Directives: Enable Large Image Previews in SERP -->
  <meta name="robots" content="index, follow, max-image-preview:large, max-snippet:-1, max-video-preview:-1" />
  <meta name="googlebot" content="index, follow, max-snippet:-1, max-image-preview:large, max-video-preview:-1" />
  <meta name="bingbot" content="index, follow, max-snippet:-1, max-image-preview:large, max-video-preview:-1" />
  <meta name="google-site-verification" content="google1c708dda37599c2a" />

  <!-- RSS Feed Discovery -->
  <link rel="alternate" type="application/rss+xml" title="Badagry Visitors Guide - Heritage Chronicle RSS Feed"
    href="https://badagryvisitorsguide.com.ng/feed.xml" />

  <!-- Google Mobile & Desktop Search Image Thumbnail -->
  <meta name="thumbnail"
    content="https://badagryvisitorsguide.com.ng/assets/images/Badagry-Slave-Memorial-Statue.jpg" />

  <!-- Geo & Regional Tags -->
  <meta name="geo.region" content="NG-LA" />
  <meta name="geo.placename" content="Badagry, Lagos, Nigeria" />
  <meta name="geo.position" content="6.4253;2.8824" />
  <meta name="ICBM" content="6.4253, 2.8824" />
  <meta name="theme-color" content="#0D5C3A" />

  <!-- Open Graph / Facebook / WhatsApp Tour Image Previews -->
  <meta property="og:type" content="website" />
  <meta property="og:site_name" content="Badagry Visitors Guide" />
  <meta property="og:title"
    content="Badagry Heritage &amp; Cultural Tours - Historical Slave Routes &amp; Landmarks | Badagry Visitors Guide" />
  <meta property="og:description"
    content="Experience Badagry's rich history and coastal serenity with expert local historians. Guided tours to the First Storey Building, Point of No Return, slave barracoons, and boat cruises." />
  <meta property="og:url" content="https://badagryvisitorsguide.com.ng/" />
  <meta property="og:locale" content="en_US" />

  <!-- Primary Tour Experience Images for Google & Social Snippets -->
  <meta property="og:image"
    content="https://badagryvisitorsguide.com.ng/assets/images/Badagry-Slave-Memorial-Statue.jpg" />
  <meta property="og:image:secure_url"
    content="https://badagryvisitorsguide.com.ng/assets/images/Badagry-Slave-Memorial-Statue.jpg" />
  <meta property="og:image:type" content="image/jpeg" />
  <meta property="og:image:width" content="1200" />
  <meta property="og:image:height" content="800" />
  <meta property="og:image:alt" content="Badagry Slave Memorial Statue and Historical Heritage Monument" />

  <meta property="og:image"
    content="https://badagryvisitorsguide.com.ng/assets/images/Point-of-No-Return-Monument-Badagry.jpg" />
  <meta property="og:image:secure_url"
    content="https://badagryvisitorsguide.com.ng/assets/images/Point-of-No-Return-Monument-Badagry.jpg" />
  <meta property="og:image:type" content="image/jpeg" />
  <meta property="og:image:width" content="1200" />
  <meta property="og:image:height" content="800" />
  <meta property="og:image:alt" content="Point of No Return Ocean Monument on Gberefu Island Badagry" />

  <meta property="og:image"
    content="https://badagryvisitorsguide.com.ng/assets/images/First-Storey-Building-in-Nigeria.jpg" />
  <meta property="og:image:secure_url"
    content="https://badagryvisitorsguide.com.ng/assets/images/First-Storey-Building-in-Nigeria.jpg" />
  <meta property="og:image:type" content="image/jpeg" />
  <meta property="og:image:width" content="1200" />
  <meta property="og:image:height" content="800" />
  <meta property="og:image:alt" content="Historic First Storey Building in Nigeria Badagry Built 1845" />

  <!-- Twitter / X Card Tags -->
  <meta name="twitter:card" content="summary_large_image" />
  <meta name="twitter:site" content="@badagry_v_guide" />
  <meta name="twitter:creator" content="@badagry_v_guide" />
  <meta name="twitter:title" content="Badagry Heritage &amp; Cultural Tours | Badagry Visitors Guide" />
  <meta name="twitter:description"
    content="Guided historical expeditions through Badagry slave routes, First Storey Building, and coastal creek boat tours. See client tour photos and budget packages." />
  <meta name="twitter:image"
    content="https://badagryvisitorsguide.com.ng/assets/images/Badagry-Slave-Memorial-Statue.jpg" />
  <meta name="twitter:image:alt" content="Badagry Tours with Badagry Visitors Guide" />

  <!-- Icons -->
  <link rel="icon" type="image/jpeg" href="assets/logo.jpg" />
  <link rel="apple-touch-icon" href="assets/logo.jpg" />
  <link rel="shortcut icon" href="assets/logo.jpg" type="image/jpeg">
  <link rel="shortcut icon" href="favicon.ico" type="image/x-icon">
  <!-- ===========================================================================================
       3. CANONICAL URL (SUPER IMPORTANT for Google) ✅
       =========================================================================================== -->
  <link rel="canonical" href="https://badagryvisitorsguide.com.ng/" />

  <!-- Schema.org JSON-LD Structured Data for Google Search Rich Snippets & Image Carousels -->
  <script type="application/ld+json">
{
  "@context": "https://schema.org",
  "@graph": [
    {
      "@type": "TravelAgency",
      "@id": "https://badagryvisitorsguide.com.ng/#travelagency",
      "name": "Badagry Visitors Guide",
      "alternateName": [
        "Badagry Visitors Guide",
        "Badagry Heritage & Cultural Tours"
      ],
      "url": "https://badagryvisitorsguide.com.ng/",
      "logo": {
        "@type": "ImageObject",
        "@id": "https://badagryvisitorsguide.com.ng/#logo",
        "url": "https://badagryvisitorsguide.com.ng/assets/logo.jpg",
        "contentUrl": "https://badagryvisitorsguide.com.ng/assets/logo.jpg",
        "caption": "Badagry Visitors Guide Logo",
        "width": "500",
        "height": "500"
      },
      "image": [
        "https://badagryvisitorsguide.com.ng/assets/images/Badagry-Heritage-Day-Tour.jpg",
        "https://badagryvisitorsguide.com.ng/assets/images/First-Storey-Building-in-Nigeria.jpg",
        "https://badagryvisitorsguide.com.ng/assets/images/Point-of-No-Return-Monument-Badagry.jpg",
        "https://badagryvisitorsguide.com.ng/assets/images/Route-to-Unknown-Destination.jpg",
        "https://badagryvisitorsguide.com.ng/assets/images/Badagry-Heritage-Museum.jpg",
        "https://badagryvisitorsguide.com.ng/assets/images/Badagry-Slave-Memorial-Statue.jpg",
        "https://badagryvisitorsguide.com.ng/assets/images/Seriki-Williams-Abass-Baracoon.jpg",
        "https://badagryvisitorsguide.com.ng/assets/images/Badagry-Beach-Creek-Leisure-Day.jpg",
        "https://badagryvisitorsguide.com.ng/assets/images/Badagry-Heritage-Beach-Weekend.jpg"
      ],
      "description": "Authentic heritage and coastal tour operator providing personalized budget-friendly journeys across Badagry, Lagos State, Nigeria led by indigenous historians.",
      "telephone": "+2347056989224",
      "email": "info@badagryvisitorsguide.com",
      "priceRange": "$",
      "currenciesAccepted": "USD, NGN",
      "paymentAccepted": "Cash, Credit Card, Bank Transfer",
      "address": {
        "@type": "PostalAddress",
        "streetAddress": "Badagry Roundabout",
        "addressLocality": "Badagry",
        "addressRegion": "Lagos State",
        "postalCode": "103101",
        "addressCountry": "NG"
      },
      "geo": {
        "@type": "GeoCoordinates",
        "latitude": 6.4253,
        "longitude": 2.8824
      },
      "openingHoursSpecification": [
        {
          "@type": "OpeningHoursSpecification",
          "dayOfWeek": [
            "Monday",
            "Tuesday",
            "Wednesday",
            "Thursday",
            "Friday",
            "Saturday",
            "Sunday"
          ],
          "opens": "09:00",
          "closes": "18:00"
        }
      ],
      "sameAs": [
        "https://www.instagram.com/badagryvisitorsguide",
        "https://www.facebook.com/share/1EnBcsZgqb/",
        "https://x.com/badagry_v_guide",
        "https://www.tiktok.com/@vezeltour",
        "https://www.linkedin.com/in/ezekiel-viavonu-105a15111/"
      ],
      "aggregateRating": {
        "@type": "AggregateRating",
        "ratingValue": "4.9",
        "bestRating": "5",
        "worstRating": "1",
        "ratingCount": "128",
        "reviewCount": "128"
      },
      "review": [
        {
          "@type": "Review",
          "author": {
            "@type": "Person",
            "name": "Olayinka Oyeku"
          },
          "datePublished": "2026-02-15",
          "reviewBody": "Taking the Badagry Heritage Day Tour opened my eyes to history in a way books never could. Walking through the 1845 First Storey Building and taking the boat across the creek to Gberefu Island was deeply touching. Our guide was patient, knowledgeable, and treated our heritage with the utmost reverence.",
          "reviewRating": {
            "@type": "Rating",
            "ratingValue": "5",
            "bestRating": "5"
          }
        },
        {
          "@type": "Review",
          "author": {
            "@type": "Person",
            "name": "Adedoyin Dosunmu-Ogunbi"
          },
          "datePublished": "2026-02-18",
          "reviewBody": "Taking the 3-day Badagry Heritage immersion tour was an extraordinary educational experience. Walking the solemn slave route across Gberefu Island to the Point of No Return and examining the preserved relics in the Brazilian Barracoons gave me profound historical insight. Flawless organization from pickup to drop-off.",
          "reviewRating": {
            "@type": "Rating",
            "ratingValue": "5",
            "bestRating": "5"
          }
        },
        {
          "@type": "Review",
          "author": {
            "@type": "Person",
            "name": "Ashola Moyinoluwa"
          },
          "datePublished": "2026-02-24",
          "reviewBody": "As an African-American walking the path toward the Point of No Return, words truly fail me. Badagry Visitors Guide provided not just an itinerary, but a sacred, respectful homecoming space for reflection. The local historians answered every question with honesty, grace, and deep cultural warmth.",
          "reviewRating": {
            "@type": "Rating",
            "ratingValue": "5",
            "bestRating": "5"
          }
        },
        {
          "@type": "Review",
          "author": {
            "@type": "Person",
            "name": "Ade Sofola"
          },
          "datePublished": "2026-03-01",
          "reviewBody": "Our weekend getaway to Badagry exceeded every expectation! The peaceful Badagry creek boat cruise, the 1845 First Storey Building tour, and relaxing along the coastal waterfront made this the highlight of our visit to Nigeria. Outstanding local guides and warm hospitality throughout.",
          "reviewRating": {
            "@type": "Rating",
            "ratingValue": "5",
            "bestRating": "5"
          }
        },
        {
          "@type": "Review",
          "author": {
            "@type": "Person",
            "name": "Tutor Saheed"
          },
          "datePublished": "2026-03-05",
          "reviewBody": "I coordinated a corporate retreat for 16 colleagues and Badagry Visitors Guide built our itinerary around our exact budget and schedule. Every logistics checkpoint, meal, and guided stop ran like clockwork. Our entire management team came back inspired and energized.",
          "reviewRating": {
            "@type": "Rating",
            "ratingValue": "5",
            "bestRating": "5"
          }
        },
        {
          "@type": "Review",
          "author": {
            "@type": "Person",
            "name": "Segun Opayele"
          },
          "datePublished": "2026-03-08",
          "reviewBody": "I travelled from Abuja specifically to connect with Badagry's coastal slave routes and relics. Our guide's storytelling brought the 19th-century barracoons and heritage artifacts alive with incredible historical depth. Professional, safe, and worth every kilometer travelled.",
          "reviewRating": {
            "@type": "Rating",
            "ratingValue": "5",
            "bestRating": "5"
          }
        }
      ]
    },
    {
      "@type": "WebSite",
      "@id": "https://badagryvisitorsguide.com.ng/#website",
      "url": "https://badagryvisitorsguide.com.ng/",
      "name": "Badagry Visitors Guide",
      "description": "Discover Badagry's most powerful heritage, slave route landmarks, and coastal experiences in Lagos State, Nigeria.",
      "publisher": {
        "@id": "https://badagryvisitorsguide.com.ng/#travelagency"
      },
      "inLanguage": "en-US"
    },
    {
      "@type": "ImageGallery",
      "@id": "https://badagryvisitorsguide.com.ng/#tour-gallery",
      "name": "Past Tour Moments & Client Experiences - Badagry Heritage Tours",
      "description": "Authentic photography captured during guided heritage, cultural, and coastal tours for clients across Badagry, Lagos State, Nigeria.",
      "url": "https://badagryvisitorsguide.com.ng/#gallery",
      "associatedMedia": [
        {
          "@type": "ImageObject",
          "name": "Badagry Heritage Museum & Slave Relics Tour",
          "caption": "Visitors and tour clients viewing historical slave trade records and artifacts at the Badagry Heritage Museum.",
          "description": "Educational heritage tour session inside the Badagry Heritage Museum established in 1863.",
          "contentUrl": "https://badagryvisitorsguide.com.ng/assets/images/Badagry-Heritage-Museum.jpg",
          "representativeOfPage": true,
          "contentLocation": "Badagry, Lagos, Nigeria"
        },
        {
          "@type": "ImageObject",
          "name": "First Storey Building in Nigeria, Badagry",
          "caption": "Tour clients at the historic First Storey Building in Nigeria constructed in 1845 by CMS missionaries.",
          "description": "Historic mission home where Bishop Samuel Ajayi Crowther translated the English Bible into Yoruba.",
          "contentUrl": "https://badagryvisitorsguide.com.ng/assets/images/First-Storey-Building-in-Nigeria.jpg",
          "contentLocation": "Badagry, Lagos, Nigeria"
        },
        {
          "@type": "ImageObject",
          "name": "Seriki Williams Abass Brazilian Barracoon",
          "caption": "Tour clients examining preserved 19th-century slave cells and iron relic chains in Badagry.",
          "description": "Historical 1840 Brazilian-style barracoon with 40 slave rooms built by Chief Seriki Williams Abass.",
          "contentUrl": "https://badagryvisitorsguide.com.ng/assets/images/Seriki-Williams-Abass-Baracoon.jpg",
          "contentLocation": "Badagry, Lagos, Nigeria"
        },
        {
          "@type": "ImageObject",
          "name": "Point of No Return Trail, Gberefu Peninsula",
          "caption": "Tour group walking the solemn Point of No Return trail on Gberefu Island toward the Atlantic Ocean.",
          "description": "Historical pilgrimage retracing the 1473 slave route steps to the Atlantic coast in Badagry.",
          "contentUrl": "https://badagryvisitorsguide.com.ng/assets/images/Route-to-Unknown-Destination.jpg",
          "contentLocation": "Gberefu Island, Badagry, Nigeria"
        },
        {
          "@type": "ImageObject",
          "name": "Point of No Return Atlantic Memorial Monument",
          "caption": "The solemn Atlantic Ocean memorial monument at the Point of No Return on Gberefu Island.",
          "description": "Historical landmark marking where enslaved Africans departed across the Atlantic Ocean.",
          "contentUrl": "https://badagryvisitorsguide.com.ng/assets/images/Point-of-No-Return-Monument-Badagry.jpg",
          "contentLocation": "Badagry, Lagos, Nigeria"
        },
        {
          "@type": "ImageObject",
          "name": "Badagry Slave Memorial Statue & Heritage Quarter",
          "caption": "Badagry Slave Memorial Statue standing as an enduring tribute in the historic heritage quarter.",
          "description": "Artistic memorial commemorating freedom and honoring the resilience of African ancestors.",
          "contentUrl": "https://badagryvisitorsguide.com.ng/assets/images/Badagry-Slave-Memorial-Statue.jpg",
          "contentLocation": "Badagry, Lagos, Nigeria"
        },
        {
          "@type": "ImageObject",
          "name": "Badagry Beach & Creek Leisure Day Tour",
          "caption": "Tour clients taking a scenic boat ride across the calm Badagry creek toward the coastal peninsula.",
          "description": "Relaxing coastal boat cruise and waterfront eco-tourism experience in Badagry.",
          "contentUrl": "https://badagryvisitorsguide.com.ng/assets/images/Badagry-Beach-Creek-Leisure-Day.jpg",
          "contentLocation": "Badagry, Lagos, Nigeria"
        },
        {
          "@type": "ImageObject",
          "name": "Badagry Heritage Weekend Experience",
          "caption": "Tour clients enjoying an immersive weekend exploring Badagry's coastal charm and historic monuments.",
          "description": "Weekend cultural retreat exploring royal palaces, artisan markets, and colonial landmarks in Badagry.",
          "contentUrl": "https://badagryvisitorsguide.com.ng/assets/images/Badagry-Heritage-Beach-Weekend.jpg",
          "contentLocation": "Badagry, Lagos, Nigeria"
        }
      ]
    },
    {
      "@type": "TouristTrip",
      "@id": "https://badagryvisitorsguide.com.ng/#tour-badagry-day",
      "name": "Badagry Heritage Day Tour",
      "description": "Discover the first storey building in Nigeria, slave relics, Mobee museum, and boat ride across the creek to the Point of No Return.",
      "touristType": [
        "Cultural Tourist",
        "Historian",
        "Student Group",
        "Solo Traveler"
      ],
      "image": [
        "https://badagryvisitorsguide.com.ng/assets/images/Badagry-Heritage-Day-Tour.jpg",
        "https://badagryvisitorsguide.com.ng/assets/images/First-Storey-Building-in-Nigeria.jpg",
        "https://badagryvisitorsguide.com.ng/assets/images/Route-to-Unknown-Destination.jpg"
      ],
      "provider": {
        "@id": "https://badagryvisitorsguide.com.ng/#travelagency"
      },
      "offers": {
        "@type": "Offer",
        "price": "75",
        "priceCurrency": "USD",
        "availability": "https://schema.org/InStock",
        "url": "https://badagryvisitorsguide.com.ng/#tours"
      },
      "itinerary": {
        "@type": "ItemList",
        "itemListElement": [
          {
            "@type": "ListItem",
            "position": 1,
            "name": "First Storey Building in Nigeria (1845)"
          },
          {
            "@type": "ListItem",
            "position": 2,
            "name": "Badagry Heritage Museum & Seriki Williams Abass Brazilian Barracoon"
          },
          {
            "@type": "ListItem",
            "position": 3,
            "name": "Boat Ride across Badagry Creek to Gberefu Island"
          },
          {
            "@type": "ListItem",
            "position": 4,
            "name": "Walk the Historic Slave Route to the Point of No Return"
          }
        ]
      }
    },
    {
      "@type": "TouristTrip",
      "@id": "https://badagryvisitorsguide.com.ng/#tour-badagry-immersion",
      "name": "3-Day Badagry Heritage & Coastal Immersion Tour",
      "description": "Comprehensive 3-day Badagry pilgrimage: First Storey Building, Seriki Abass Barracoons, Gberefu Island Point of No Return, Badagry Creek boat cruise, and coastal beach relaxation.",
      "touristType": [
        "International Tourist",
        "Diaspora",
        "Heritage Pilgrim",
        "Weekend Traveler"
      ],
      "image": [
        "https://badagryvisitorsguide.com.ng/assets/images/Badagry-Heritage-Beach-Weekend.jpg",
        "https://badagryvisitorsguide.com.ng/assets/images/Seriki-Williams-Abass-Baracoon.jpg",
        "https://badagryvisitorsguide.com.ng/assets/images/Badagry-Beach-Creek-Leisure-Day.jpg"
      ],
      "provider": {
        "@id": "https://badagryvisitorsguide.com.ng/#travelagency"
      },
      "offers": {
        "@type": "Offer",
        "price": "350",
        "priceCurrency": "USD",
        "availability": "https://schema.org/InStock",
        "url": "https://badagryvisitorsguide.com.ng/#tours"
      }
    },
    {
      "@type": "TouristTrip",
      "@id": "https://badagryvisitorsguide.com.ng/#tour-diaspora",
      "name": "Badagry Diaspora Roots & Homecoming Tour",
      "description": "Emotional pilgrimage for descendants connecting with ancestral roots, traditional Yoruba welcome rites, Badagry heritage quarter visits, and healing dialogues at the Point of No Return.",
      "touristType": [
        "African Diaspora",
        "Heritage Pilgrim",
        "Genealogy Traveler"
      ],
      "image": [
        "https://badagryvisitorsguide.com.ng/assets/images/Point-of-No-Return-Monument-Badagry.jpg",
        "https://badagryvisitorsguide.com.ng/assets/images/Route-to-Unknown-Destination.jpg"
      ],
      "provider": {
        "@id": "https://badagryvisitorsguide.com.ng/#travelagency"
      },
      "offers": {
        "@type": "Offer",
        "price": "550",
        "priceCurrency": "USD",
        "availability": "https://schema.org/InStock",
        "url": "https://badagryvisitorsguide.com.ng/#tours"
      }
    },
    {
      "@type": "TouristTrip",
      "@id": "https://badagryvisitorsguide.com.ng/#tour-zangbeto-festival",
      "name": "Zangbeto Festival Tour",
      "description": "Witness the mystical Zangbeto masquerade — the traditional guardian of the night. Featuring authentic Ogu drumming, folklore, and sacred performances. Zangbeto remains an important symbol of Badagry’s cultural identity and a living heritage.",
      "touristType": [
        "African Diaspora",
        "Cultural Tourist",
        "Heritage Pilgrim",
        "Traditional Heritage"
      ],
      "image": [
        "https://badagryvisitorsguide.com.ng/assets/images/Zangbeto-Festival-Tour.jpg"
      ],
      "provider": {
        "@id": "https://badagryvisitorsguide.com.ng/#travelagency"
      },
      "offers": {
        "@type": "Offer",
        "price": "95",
        "priceCurrency": "USD",
        "availability": "https://schema.org/InStock",
        "url": "https://badagryvisitorsguide.com.ng/#tours"
      }
    },
    {
      "@type": "TouristAttraction",
      "@id": "https://badagryvisitorsguide.com.ng/#attraction-point-no-return",
      "name": "Point of No Return Badagry (Gberefu Peninsula)",
      "description": "Historic Atlantic embarkation point where hundreds of thousands of enslaved Africans departed during the transatlantic slave trade.",
      "image": "https://badagryvisitorsguide.com.ng/assets/images/Route-to-Unknown-Destination.jpg",
      "address": {
        "@type": "PostalAddress",
        "addressLocality": "Badagry",
        "addressRegion": "Lagos",
        "addressCountry": "NG"
      },
      "touristType": [
        "History",
        "Memorial",
        "Cultural"
      ]
    },
    {
      "@type": "TouristAttraction",
      "@id": "https://badagryvisitorsguide.com.ng/#attraction-first-storey",
      "name": "First Storey Building in Nigeria",
      "description": "Constructed in 1845 by Christian Missionary Society missionaries, the building where the English Bible was translated to Yoruba.",
      "image": "https://badagryvisitorsguide.com.ng/assets/images/First-Storey-Building-in-Nigeria.jpg",
      "address": {
        "@type": "PostalAddress",
        "streetAddress": "Marina Badagry",
        "addressLocality": "Badagry",
        "addressRegion": "Lagos",
        "addressCountry": "NG"
      }
    },
    {
      "@type": "TouristAttraction",
      "@id": "https://badagryvisitorsguide.com.ng/#attraction-barracoon",
      "name": "Seriki Williams Abass Brazilian Barracoon",
      "description": "Historical 1840 Brazilian-style slave barracks containing 40 preserved slave cells and historical iron relic chains in Badagry.",
      "image": "https://badagryvisitorsguide.com.ng/assets/images/Seriki-Williams-Abass-Baracoon.jpg",
      "address": {
        "@type": "PostalAddress",
        "streetAddress": "Marina Badagry",
        "addressLocality": "Badagry",
        "addressRegion": "Lagos",
        "addressCountry": "NG"
      }
    },
    {
      "@type": "BreadcrumbList",
      "@id": "https://badagryvisitorsguide.com.ng/#breadcrumbs",
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
          "name": "Tours & Experiences",
          "item": "https://badagryvisitorsguide.com.ng/#tours"
        },
        {
          "@type": "ListItem",
          "position": 3,
          "name": "Past Tour Photo Gallery",
          "item": "https://badagryvisitorsguide.com.ng/#gallery"
        },
        {
          "@type": "ListItem",
          "position": 4,
          "name": "Client Reviews",
          "item": "https://badagryvisitorsguide.com.ng/#testimonials"
        }
      ]
    }
  ]
}

  </script>

  <!-- Google Fonts: Plus Jakarta Sans & Playfair Display -->
  <link rel="preconnect" href="https://fonts.googleapis.com" />
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
  <link
    href="https://fonts.googleapis.com/css2?family=Playfair+Display:ital,wght@0,600;0,700;1,400&family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&display=swap"
    rel="stylesheet" />

  <!-- Font Awesome 6 Icons -->
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" />

  <link rel="stylesheet" href="/style.css?v=3.04" />
</head>

<body>

  <!-- Mobile Offcanvas Menu Drawer -->
  <div class="mobile-menu-overlay" id="mobileMenuOverlay"></div>
  <aside class="mobile-menu-drawer" id="mobileMenuDrawer">
    <div class="drawer-header">
      <a href="#" class="drawer-logo">
        <img src="assets/logo.jpg" alt="Badagry Visitors Guide" class="drawer-logo-img" />
        <span class="drawer-logo-text">Badagry <strong>Visitors Guide</strong></span>
      </a>
      <button class="drawer-close-btn" id="drawerCloseBtn" aria-label="Close menu">
        <i class="fa-solid fa-xmark"></i>
      </button>
    </div>
    <div class="drawer-content">
      <ul class="mobile-nav-list">
        <li><a href="#hero" class="mobile-nav-link active">Home</a></li>
        <li><a href="#about" class="mobile-nav-link">About Us</a></li>
        <li><a href="#tours" class="mobile-nav-link">Expeditions</a></li>
        <li><a href="#landmarks" class="mobile-nav-link">Landmarks</a></li>
        <li><a href="#stewardship" class="mobile-nav-link">Our Philosophy</a></li>
        <li><a href="#styles" class="mobile-nav-link">Travel Styles</a></li>
        <li><a href="/gallery/" class="mobile-nav-link">Gallery</a></li>
        <li><a href="/blog/" class="mobile-nav-link">Blog</a></li>
        <li><a href="http://wa.me/+2347056989224?text=from+google+map+or+site" target="_blank" rel="noopener"
            class="mobile-nav-link">Contact Us</a></li>
      </ul>
      <div class="mobile-drawer-cta">
        <a href="http://wa.me/+2347056989224?text=from+google+map+or+site" target="_blank" rel="noopener"
          class="btn btn-primary w-100 open-booking-btn">Book Your Tour</a>
      </div>
      <div class="drawer-contact-info">
        <p><i class="fa-solid fa-location-dot"></i> Badagry, Lagos, Nigeria</p>
        <p><i class="fa-solid fa-phone"></i> +234 705 698 9224</p>
        <p><i class="fa-solid fa-phone"></i> +234 802 593 7044</p>
        <p><i class="fa-solid fa-envelope"></i> info@badagryvisitorsguide.com</p>
        <div class="drawer-social-links">
          <a href="https://www.instagram.com/badagryvisitorsguide" target="_blank" rel="noopener"
            aria-label="Instagram"><i class="fa-brands fa-instagram"></i></a>
          <a href="https://www.tiktok.com/@vezeltour" target="_blank" rel="noopener" aria-label="TikTok"><i
              class="fa-brands fa-tiktok"></i></a>
          <a href="http://wa.me/+2347056989224?text=from+google+map+or+site" target="_blank" rel="noopener"
            aria-label="WhatsApp"><i class="fa-brands fa-whatsapp"></i></a>
          <a href="https://www.facebook.com/share/1EnBcsZgqb/" target="_blank" rel="noopener" aria-label="Facebook"><i
              class="fa-brands fa-facebook-f"></i></a>
          <a href="https://x.com/badagry_v_guide" target="_blank" rel="noopener" aria-label="X (Twitter)"><i
              class="fa-brands fa-x-twitter"></i></a>
          <a href="https://www.linkedin.com/in/ezekiel-viavonu-105a15111/" target="_blank" rel="noopener"
            aria-label="LinkedIn"><i class="fa-brands fa-linkedin-in"></i></a>
        </div>
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
            <i class="fa-regular fa-clock"></i> 9AM - 6PM WAT
          </span>
        </div>
        <div class="top-bar-right">
          <div class="lang-dropdown">
            <i class="fa-solid fa-globe"></i>
            <span>Language: English</span>
            <i class="fa-solid fa-chevron-down caret-icon"></i>
          </div>
          <div class="top-links">
            <a href="#faq">FAQ</a>
            <span class="divider">|</span>
            <a href="http://wa.me/+2347056989224?text=from+google+map+or+site" target="_blank"
              rel="noopener">Support</a>
          </div>
        </div>
      </div>
    </div>

    <!-- Main Navigation Bar -->
    <nav class="main-navbar" id="mainNavbar">
      <div class="site-container nav-container">
        <a href="#" class="brand-logo">
          <img src="assets/logo.jpg" alt="Badagry Visitors Guide Logo" class="brand-logo-img" />
          <div class="brand-text">
            <span class="logo-main">BADAGRY <strong>VISITORS GUIDE</strong></span>
            <span class="logo-sub">Heritage &amp; Cultural Tours</span>
          </div>
        </a>

        <ul class="nav-menu">
          <li class="nav-item"><a href="#hero" class="nav-link active">Home</a></li>
          <li class="nav-item"><a href="#tours" class="nav-link">Expeditions</a></li>
          <li class="nav-item"><a href="#landmarks" class="nav-link">Landmarks</a></li>
          <li class="nav-item"><a href="#stewardship" class="nav-link">Our Philosophy</a></li>
          <li class="nav-item"><a href="#styles" class="nav-link">Travel Styles</a></li>
          <li class="nav-item"><a href="/gallery/" class="nav-link">Archive</a></li>
          <li class="nav-item"><a href="/blog/" class="nav-link">Chronicle</a></li>
          <li class="nav-item"><a href="http://wa.me/+2347056989224?text=from+google+map+or+site" target="_blank"
              rel="noopener" class="nav-link">Contact</a></li>
        </ul>

        <div class="nav-actions">
          <a href="http://wa.me/+2347056989224?text=from+google+map+or+site" target="_blank" rel="noopener"
            class="btn btn-primary nav-book-btn open-booking-btn">
            <span>Book Your Tour</span>
            <i class="fa-solid fa-arrow-right"></i>
          </a>
          <button class="mobile-toggle-btn" id="mobileToggleBtn" aria-label="Open mobile menu">
            <i class="fa-solid fa-bars"></i>
          </button>
        </div>
      </div>
    </nav>
  </header>

  <!-- Hero Slider Section -->
  <section class="hero-section" id="hero">
    <div class="hero-slider-container">
      <!-- Slide 1 -->
      <div class="hero-slide active" data-slide="0">
        <div class="hero-bg"
          style="background-image: linear-gradient(rgba(17, 24, 39, 0.52), rgba(17, 24, 39, 0.65)), url('assets/images/Badagry-Slave-Memorial-Statue.jpg');">
        </div>
        <div class="site-container hero-content">
          <span class="hero-badge animate-up">Indigenous Historians • Curated Heritage &amp; Coastal Circuits</span>
          <h1 class="hero-title animate-up delay-1">Discover Badagry's Timeless Heritage.</h1>
          <p class="hero-desc animate-up delay-2">Unmatched historical expeditions through Badagry slave routes, 1845 missionary landmarks, serene creeks, and coastal getaways with dedicated local historians.</p>
          <div class="hero-btn-group animate-up delay-3">
            <a href="#tours" class="btn btn-primary">Explore Expeditions <i class="fa-solid fa-compass"></i></a>
            <a href="#stewardship" class="btn btn-outline-white">Our Philosophy <i class="fa-solid fa-shield-halved"></i></a>
          </div>
        </div>
      </div>

      <!-- Slide 2 -->
      <div class="hero-slide" data-slide="1">
        <div class="hero-bg"
          style="background-image: linear-gradient(rgba(17, 24, 39, 0.52), rgba(17, 24, 39, 0.65)), url('assets/images/Point-of-No-Return-Monument-Badagry.jpg');">
        </div>
        <div class="site-container hero-content">
          <span class="hero-badge animate-up">Historic Gberefu Peninsula</span>
          <h1 class="hero-title animate-up delay-1">Walk the Historic Path of No Return.</h1>
          <p class="hero-desc animate-up delay-2">Retrace the solemn steps across Gberefu Island to the Atlantic ocean
            memorial, standing where history was made with our expert indigenous guides.</p>
          <div class="hero-btn-group animate-up delay-3">
            <a href="#tours" class="btn btn-primary">Explore Tours <i class="fa-solid fa-compass"></i></a>
            <a href="#landmarks" class="btn btn-outline-white">Historic Landmarks <i
                class="fa-solid fa-arrow-right"></i></a>
          </div>
        </div>
      </div>

      <!-- Slide 3 -->
      <div class="hero-slide" data-slide="2">
        <div class="hero-bg"
          style="background-image: linear-gradient(rgba(17, 24, 39, 0.52), rgba(17, 24, 39, 0.65)), url('assets/images/First-Storey-Building-Badagry.jpg');">
        </div>
        <div class="site-container hero-content">
          <span class="hero-badge animate-up">Nigeria's Earliest Architectural Landmark</span>
          <h1 class="hero-title animate-up delay-1">First Storey Building in Nigeria — Built 1845.</h1>
          <p class="hero-desc animate-up delay-2">Step into living history at the historic CMS mission house where the
            English Bible was first translated into Yoruba language by Bishop Ajayi Crowther.</p>
          <div class="hero-btn-group animate-up delay-3">
            <a href="#tours" class="btn btn-primary">Explore Tours <i class="fa-solid fa-compass"></i></a>
            <a href="#about" class="btn btn-outline-white">How It Works <i class="fa-solid fa-play"></i></a>
          </div>
        </div>
      </div>

      <!-- Slide 4 -->
      <div class="hero-slide" data-slide="3">
        <div class="hero-bg"
          style="background-image: linear-gradient(rgba(17, 24, 39, 0.52), rgba(17, 24, 39, 0.65)), url('assets/images/Seriki-Williams-Abass-Baracoon.jpg?v=1.0');">
        </div>
        <div class="site-container hero-content">
          <span class="hero-badge animate-up">Preserved Slave Relics &amp; Barracoons</span>
          <h1 class="hero-title animate-up delay-1">Seriki Williams Abass Baracoon &amp; Heritage Sites.</h1>
          <p class="hero-desc animate-up delay-2">Tour preserved 19th-century slave barracoons, museum relics, and
            cultural heritage on personalized tours tailored to your schedule and budget.</p>
          <div class="hero-btn-group animate-up delay-3">
            <a href="#tours" class="btn btn-primary">Explore Tours <i class="fa-solid fa-compass"></i></a>
            <a href="#landmarks" class="btn btn-outline-white">Historic Landmarks <i
                class="fa-solid fa-arrow-right"></i></a>
          </div>
        </div>
      </div>
    </div>

    <!-- Slider Navigation Controls -->
    <button class="slider-control prev" id="sliderPrevBtn" aria-label="Previous Slide">
      <i class="fa-solid fa-arrow-left"></i>
    </button>
    <button class="slider-control next" id="sliderNextBtn" aria-label="Next Slide">
      <i class="fa-solid fa-arrow-right"></i>
    </button>

    <!-- Slider Pagination Dots -->
    <div class="slider-dots" id="sliderDots">
      <span class="dot active" data-dot="0"></span>
      <span class="dot" data-dot="1"></span>
      <span class="dot" data-dot="2"></span>
      <span class="dot" data-dot="3"></span>
    </div>
  </section>

<!-- Interactive Expedition Explorer Console (Independent Architecture) -->
  <section class="expedition-finder-section" id="search">
    <div class="site-container">
      <div class="finder-console-card">
        <div class="finder-header">
          <div class="finder-badge">
            <i class="fa-solid fa-compass"></i> Expedition Explorer Console
          </div>
          <span class="finder-tagline">Filter verified Badagry routes by duration, investment tier, and cultural focus</span>
        </div>
        <form id="tripSearchForm" class="finder-form" method="GET" action="/tours/">
          <div class="finder-col">
            <label for="destinationSelect" class="finder-label">
              <i class="fa-solid fa-landmark"></i> Journey Focus
            </label>
            <select id="destinationSelect" name="destination" class="finder-select">
              <option value="all">All Heritage &amp; Coastal Circuits</option>
              <option value="Badagry">Transatlantic Slave Route &amp; Relics</option>
              <option value="day">Single-Day Cultural Excursions</option>
              <option value="weekend">Coastal Lagoon &amp; Beach Weekends</option>
              <option value="educational">Educational &amp; Student Delegations</option>
              <option value="diaspora">Diaspora Roots &amp; Sacred Masquerade</option>
            </select>
          </div>

          <div class="finder-col">
            <label for="durationSelect" class="finder-label">
              <i class="fa-regular fa-clock"></i> Time Commitment
            </label>
            <select id="durationSelect" name="duration" class="finder-select">
              <option value="all">Any Tour Duration</option>
              <option value="1">1 Day Guided Circuit</option>
              <option value="2">2 Days Weekend Getaway</option>
              <option value="3">3 Days Deep Cultural Immersion</option>
            </select>
          </div>

          <div class="finder-col">
            <label for="budgetSelect" class="finder-label">
              <i class="fa-solid fa-naira-sign"></i> Transparent Price Band
            </label>
            <select id="budgetSelect" name="budget" class="finder-select">
              <option value="all">All Price Bands (NGN / USD)</option>
              <option value="budget">Community Standard (Under $100 / &#8358;150k)</option>
              <option value="moderate">Guided Immersion ($100 - $350 / &#8358;150k - &#8358;500k)</option>
              <option value="premium">Private VIP &amp; Diaspora ($350+ / &#8358;500k+)</option>
            </select>
          </div>

          <button type="submit" class="finder-submit-btn">
            <i class="fa-solid fa-magnifying-glass"></i>
            <span>Find Expeditions</span>
          </button>
        </form>
      </div>
    </div>
  </section>

  <!-- Section 1: Signature Guided Expeditions (Moved upfront for distinct sequence) -->
  <section class="tours-catalog-section section-padding bg-light" id="tours">
    <div class="site-container">
      <div class="section-title-wrap text-center">
        <span class="sub-title">Flagship Routes</span>
        <h2 class="sec-title">Signature Guided Expeditions</h2>
        <p class="section-lead-text">
          Managed end-to-end by licensed native Badagry historians. All admission passes, boat crossings, and logistics are fully coordinated.
        </p>
      </div>

      <!-- Quick Category Filter Tabs -->
      <div class="tour-tabs" id="tourTabs">
        <button class="tour-tab-btn active" data-filter="all">All Expeditions (11)</button>
        <button class="tour-tab-btn" data-filter="day">Day Trips</button>
        <button class="tour-tab-btn" data-filter="weekend">Weekend Getaways</button>
        <button class="tour-tab-btn" data-filter="educational">Educational &amp; Groups</button>
        <button class="tour-tab-btn" data-filter="diaspora">Diaspora &amp; Cultural</button>
      </div>

      <div class="tours-grid" id="toursGrid">
        <!-- Tour 1 -->
        <article class="tour-card" data-category="day" data-duration="1" data-price="75" data-corridor="Badagry">
          <div class="tour-card-img">
            <img src="assets/images/Badagry-Heritage-Day-Tour.jpg" alt="Badagry Heritage Day Tour" loading="lazy" />
            <span class="tour-duration-tag"><i class="fa-regular fa-clock"></i> 1 Day</span>
          </div>
          <div class="tour-card-body">
            <div class="tour-location"><i class="fa-solid fa-location-dot"></i> Badagry Historic District</div>
            <h3 class="tour-title">Badagry Heritage Day Tour</h3>
            <p class="tour-summary">Discover the first storey building in Nigeria, slave relics, Mobee museum, and boat
              ride across the creek to the Point of No Return.</p>
            <div class="tour-perks">
              <span class="tour-perk-item"><i class="fa-solid fa-sailboat"></i> Boat Cruise</span>
              <span class="tour-perk-item"><i class="fa-solid fa-ticket"></i> All Passes</span>
              <span class="tour-perk-item"><i class="fa-solid fa-user-tie"></i> Native Guide</span>
            </div>
            <div class="tour-card-footer">
              <div class="tour-price">
                <span class="price-prefix">From</span>
                <span class="price-amount">$75</span>
                <span class="price-suffix">/Person</span>
              </div>
              <button class="btn btn-sm btn-primary open-booking-btn" data-tour="Badagry Heritage Day Tour"
                data-price="$75">Reserve Expedition</button>
            </div>
          </div>
        </article>

        <!-- Tour 2 -->
        <article class="tour-card" data-category="day" data-duration="1" data-price="55" data-corridor="Badagry">
          <div class="tour-card-img">
            <img src="assets/images/Badagry-Beach-Creek-Leisure-Day.jpg" alt="Badagry Beach &amp; Creek Leisure Day"
              loading="lazy" />
            <span class="tour-duration-tag"><i class="fa-regular fa-clock"></i> 1 Day</span>
          </div>
          <div class="tour-card-body">
            <div class="tour-location"><i class="fa-solid fa-location-dot"></i> Badagry Marina &amp; Creeks</div>
            <h3 class="tour-title">Badagry Beach &amp; Creek Leisure Day</h3>
            <p class="tour-summary">A relaxed coastal day featuring canoe cruising along serene creeks, coconut groves,
              seafood lunch, and Atlantic shore relaxation.</p>
            <div class="tour-perks">
              <span class="tour-perk-item"><i class="fa-solid fa-water"></i> Lagoon Canoe</span>
              <span class="tour-perk-item"><i class="fa-solid fa-umbrella-beach"></i> Beach Access</span>
              <span class="tour-perk-item"><i class="fa-solid fa-fish"></i> Fresh Seafood</span>
            </div>
            <div class="tour-card-footer">
              <div class="tour-price">
                <span class="price-prefix">From</span>
                <span class="price-amount">$55</span>
                <span class="price-suffix">/Person</span>
              </div>
              <button class="btn btn-sm btn-primary open-booking-btn" data-tour="Badagry Beach &amp; Creek Leisure Day"
                data-price="$55">Reserve Expedition</button>
            </div>
          </div>
        </article>

        <!-- Tour 3 -->
        <article class="tour-card" data-category="educational" data-duration="1" data-price="45" data-corridor="Badagry">
          <div class="tour-card-img">
            <img src="assets/images/Badagry-for-Schools-and-Student-Groups.jpg"
              alt="Badagry for Schools and Student Groups" loading="lazy" />
            <span class="tour-duration-tag"><i class="fa-regular fa-clock"></i> 1 Day</span>
          </div>
          <div class="tour-card-body">
            <div class="tour-location"><i class="fa-solid fa-location-dot"></i> Educational Heritage Circuit</div>
            <h3 class="tour-title">Badagry for Schools &amp; Student Groups</h3>
            <p class="tour-summary">Curriculum-aligned educational excursion offering young learners real engagement
              with Nigerian and West African historical milestones.</p>
            <div class="tour-perks">
              <span class="tour-perk-item"><i class="fa-solid fa-book-open"></i> Study Materials</span>
              <span class="tour-perk-item"><i class="fa-solid fa-bus"></i> Escorted Transit</span>
              <span class="tour-perk-item"><i class="fa-solid fa-chalkboard-user"></i> Certified Docent</span>
            </div>
            <div class="tour-card-footer">
              <div class="tour-price">
                <span class="price-prefix">From</span>
                <span class="price-amount">$45</span>
                <span class="price-suffix">/Student</span>
              </div>
              <button class="btn btn-sm btn-primary open-booking-btn"
                data-tour="Badagry for Schools &amp; Student Groups" data-price="$45">Reserve Group</button>
            </div>
          </div>
        </article>

        <!-- Tour 4 -->
        <article class="tour-card" data-category="day" data-duration="1" data-price="180" data-corridor="Badagry">
          <div class="tour-card-img">
            <img src="assets/images/Private-Group-Badagry-Heritage-Day-Tour.jpg"
              alt="Private Group Badagry Heritage Day Tour" loading="lazy" />
            <span class="tour-duration-tag"><i class="fa-regular fa-clock"></i> 1 Day</span>
          </div>
          <div class="tour-card-body">
            <div class="tour-location"><i class="fa-solid fa-location-dot"></i> Exclusive Private Charter</div>
            <h3 class="tour-title">Private Group Badagry Day Tour</h3>
            <p class="tour-summary">Customized itinerary for families, associations, or private circles with dedicated
              luxury transport and personalized private historian.</p>
            <div class="tour-perks">
              <span class="tour-perk-item"><i class="fa-solid fa-car"></i> Private Van</span>
              <span class="tour-perk-item"><i class="fa-solid fa-crown"></i> VIP Fast-Track</span>
              <span class="tour-perk-item"><i class="fa-solid fa-camera"></i> Tour Photos</span>
            </div>
            <div class="tour-card-footer">
              <div class="tour-price">
                <span class="price-prefix">From</span>
                <span class="price-amount">$180</span>
                <span class="price-suffix">/Group</span>
              </div>
              <button class="btn btn-sm btn-primary open-booking-btn" data-tour="Private Group Badagry Day Tour"
                data-price="$180">Reserve Private</button>
            </div>
          </div>
        </article>

        <!-- Tour 5 -->
        <article class="tour-card" data-category="weekend" data-duration="2" data-price="195" data-corridor="Badagry">
          <div class="tour-card-img">
            <img src="assets/images/Badagry-Heritage-Beach-Weekend.jpg" alt="Badagry Heritage &amp; Beach Weekend"
              loading="lazy" />
            <span class="tour-duration-tag"><i class="fa-regular fa-clock"></i> 2 Days</span>
          </div>
          <div class="tour-card-body">
            <div class="tour-location"><i class="fa-solid fa-location-dot"></i> Beach Resort &amp; Museums</div>
            <h3 class="tour-title">Badagry Heritage &amp; Beach Weekend</h3>
            <p class="tour-summary">Overnight stay at a scenic beach resort in Badagry coupled with deep museum
              explorations, bonfire storytelling, and fresh ocean dining.</p>
            <div class="tour-perks">
              <span class="tour-perk-item"><i class="fa-solid fa-bed"></i> Resort Lodging</span>
              <span class="tour-perk-item"><i class="fa-solid fa-fire"></i> Evening Bonfire</span>
              <span class="tour-perk-item"><i class="fa-solid fa-utensils"></i> Meals Covered</span>
            </div>
            <div class="tour-card-footer">
              <div class="tour-price">
                <span class="price-prefix">From</span>
                <span class="price-amount">$195</span>
                <span class="price-suffix">/Person</span>
              </div>
              <button class="btn btn-sm btn-primary open-booking-btn" data-tour="Badagry Heritage &amp; Beach Weekend"
                data-price="$195">Reserve Weekend</button>
            </div>
          </div>
        </article>

        <!-- Tour 6 -->
        <article class="tour-card" data-category="day" data-duration="1" data-price="85" data-corridor="Badagry">
          <div class="tour-card-img">
            <img src="assets/images/Seriki-Williams-Abass-Baracoon.jpg" alt="Seriki Abass Barracoon and Slave Relics Tour"
              loading="lazy" />
            <span class="tour-duration-tag"><i class="fa-regular fa-clock"></i> 1 Day</span>
          </div>
          <div class="tour-card-body">
            <div class="tour-location"><i class="fa-solid fa-location-dot"></i> Brazilian Compound &amp; Vlekete</div>
            <h3 class="tour-title">Seriki Abass Barracoon &amp; Relics Tour</h3>
            <p class="tour-summary">In-depth exploration of the 1840 Brazilian-style slave barracks, Chief Seriki Abass's tomb, authentic shackles, and the ancient Vlekete Slave Market.</p>
            <div class="tour-perks">
              <span class="tour-perk-item"><i class="fa-solid fa-door-open"></i> 40 Cells Access</span>
              <span class="tour-perk-item"><i class="fa-solid fa-link"></i> Authentic Shackles</span>
              <span class="tour-perk-item"><i class="fa-solid fa-landmark"></i> Vlekete Market</span>
            </div>
            <div class="tour-card-footer">
              <div class="tour-price">
                <span class="price-prefix">From</span>
                <span class="price-amount">$85</span>
                <span class="price-suffix">/Person</span>
              </div>
              <button class="btn btn-sm btn-primary open-booking-btn" data-tour="Seriki Abass Barracoon &amp; Relics Tour"
                data-price="$85">Reserve Tour</button>
            </div>
          </div>
        </article>

        <!-- Tour 7 -->
        <article class="tour-card" data-category="weekend" data-duration="2" data-price="220" data-corridor="Badagry">
          <div class="tour-card-img">
            <img src="assets/images/Badagry-Beach-Creek-Leisure-Day.jpg" alt="Whispering Palms &amp; Marina Coastal Escape"
              loading="lazy" />
            <span class="tour-duration-tag"><i class="fa-regular fa-clock"></i> 2 Days</span>
          </div>
          <div class="tour-card-body">
            <div class="tour-location"><i class="fa-solid fa-location-dot"></i> Whispering Palms Waterfront</div>
            <h3 class="tour-title">Whispering Palms &amp; Marina Escape</h3>
            <p class="tour-summary">Relaxing 2-day waterfront getaway at Whispering Palms Resort with calm Badagry creek boat cruise, coconut palms, and authentic Lagos coastal cuisine.</p>
            <div class="tour-perks">
              <span class="tour-perk-item"><i class="fa-solid fa-hotel"></i> Waterfront Stay</span>
              <span class="tour-perk-item"><i class="fa-solid fa-ship"></i> Creek Cruise</span>
              <span class="tour-perk-item"><i class="fa-solid fa-cocktail"></i> Coconut Lounge</span>
            </div>
            <div class="tour-card-footer">
              <div class="tour-price">
                <span class="price-prefix">From</span>
                <span class="price-amount">$220</span>
                <span class="price-suffix">/Person</span>
              </div>
              <button class="btn btn-sm btn-primary open-booking-btn" data-tour="Whispering Palms &amp; Marina Escape"
                data-price="$220">Reserve Escape</button>
            </div>
          </div>
        </article>

        <!-- Tour 8 -->
        <article class="tour-card" data-category="weekend" data-duration="2" data-price="380" data-corridor="Badagry">
          <div class="tour-card-img">
            <img src="assets/images/Corporate-Team-Heritage-Weekend.jpg" alt="Corporate Team Heritage Weekend"
              loading="lazy" />
            <span class="tour-duration-tag"><i class="fa-regular fa-clock"></i> 2 Days</span>
          </div>
          <div class="tour-card-body">
            <div class="tour-location"><i class="fa-solid fa-location-dot"></i> Executive Retreat Logistics</div>
            <h3 class="tour-title">Corporate Team Heritage Retreat</h3>
            <p class="tour-summary">Curated executive retreat combining team bonding, cultural enlightenment, premium
              coastal accommodation, and smooth VIP logistics across Badagry.</p>
            <div class="tour-perks">
              <span class="tour-perk-item"><i class="fa-solid fa-users-gear"></i> Team Workshop</span>
              <span class="tour-perk-item"><i class="fa-solid fa-shield"></i> Security Escort</span>
              <span class="tour-perk-item"><i class="fa-solid fa-champagne-glasses"></i> Executive Dining</span>
            </div>
            <div class="tour-card-footer">
              <div class="tour-price">
                <span class="price-prefix">From</span>
                <span class="price-amount">$380</span>
                <span class="price-suffix">/Person</span>
              </div>
              <button class="btn btn-sm btn-primary open-booking-btn" data-tour="Corporate Team Heritage Retreat"
                data-price="$380">Reserve Retreat</button>
            </div>
          </div>
        </article>

        <!-- Tour 9 -->
        <article class="tour-card" data-category="weekend" data-duration="3" data-price="350" data-corridor="Badagry">
          <div class="tour-card-img">
            <img src="assets/images/Badagry.jpg" alt="3-Day Badagry Heritage &amp; Coastal Immersion"
              loading="lazy" />
            <span class="tour-duration-tag"><i class="fa-regular fa-clock"></i> 3 Days</span>
          </div>
          <div class="tour-card-body">
            <div class="tour-location"><i class="fa-solid fa-location-dot"></i> Comprehensive All-Circuit Pass</div>
            <h3 class="tour-title">3-Day Badagry Heritage Immersion</h3>
            <p class="tour-summary">The ultimate Badagry circuit: 1845 First Storey Building, Gberefu Point of No Return, Seriki Abass Barracoons, Mobee Museum, and beachside relaxation.</p>
            <div class="tour-perks">
              <span class="tour-perk-item"><i class="fa-solid fa-compass"></i> Complete Circuit</span>
              <span class="tour-perk-item"><i class="fa-solid fa-hotel"></i> 2 Nights Hotel</span>
              <span class="tour-perk-item"><i class="fa-solid fa-utensils"></i> All Meals</span>
            </div>
            <div class="tour-card-footer">
              <div class="tour-price">
                <span class="price-prefix">From</span>
                <span class="price-amount">$350</span>
                <span class="price-suffix">/Person</span>
              </div>
              <button class="btn btn-sm btn-primary open-booking-btn"
                data-tour="3-Day Badagry Heritage Immersion" data-price="$350">Reserve 3-Day</button>
            </div>
          </div>
        </article>

        <!-- Tour 10 -->
        <article class="tour-card" data-category="diaspora" data-duration="3" data-price="550" data-corridor="Badagry">
          <div class="tour-card-img">
            <img src="assets/images/Diaspora-5-Day-Heritage-Homecoming-Tour.jpg"
              alt="Badagry Diaspora Roots &amp; Homecoming Pilgrimage" loading="lazy" />
            <span class="tour-duration-tag"><i class="fa-regular fa-clock"></i> 3 Days</span>
          </div>
          <div class="tour-card-body">
            <div class="tour-location"><i class="fa-solid fa-location-dot"></i> Diaspora Ancestral Homecoming</div>
            <h3 class="tour-title">Badagry Diaspora Roots Pilgrimage</h3>
            <p class="tour-summary">Sacred pilgrimage for African diaspora descendants connecting with ancestral roots, traditional welcome rites, ancestral reflection, and Atlantic healing sessions.</p>
            <div class="tour-perks">
              <span class="tour-perk-item"><i class="fa-solid fa-hand-holding-heart"></i> Ancestral Rites</span>
              <span class="tour-perk-item"><i class="fa-solid fa-dove"></i> Atlantic Healing</span>
              <span class="tour-perk-item"><i class="fa-solid fa-certificate"></i> Roots Certificate</span>
            </div>
            <div class="tour-card-footer">
              <div class="tour-price">
                <span class="price-prefix">From</span>
                <span class="price-amount">$550</span>
                <span class="price-suffix">/Person</span>
              </div>
              <button class="btn btn-sm btn-primary open-booking-btn" data-tour="Badagry Diaspora Roots Pilgrimage"
                data-price="$550">Inquire Pilgrimage</button>
            </div>
          </div>
        </article>

        <!-- Tour 11 -->
        <article class="tour-card" data-category="diaspora" data-duration="1" data-price="95" data-corridor="Badagry">
          <div class="tour-card-img">
            <img src="assets/images/Zangbeto-Festival-Tour.jpg"
              alt="Zangbeto Festival Tour - Traditional Masquerade and Cultural Heritage Badagry" loading="lazy" />
            <span class="tour-duration-tag"><i class="fa-regular fa-clock"></i> 1 Day</span>
          </div>
          <div class="tour-card-body">
            <div class="tour-location"><i class="fa-solid fa-location-dot"></i> Ogu Living Cultural Heritage</div>
            <h3 class="tour-title">Zangbeto Festival Tour</h3>
            <p class="tour-summary">Experience the mystical Zangbeto masquerade - the traditional guardian of the night. Featuring energetic Ogu drumming, traditional chants, and sacred dances.</p>
            <div class="tour-perks">
              <span class="tour-perk-item"><i class="fa-solid fa-drum"></i> Ogu Drumming</span>
              <span class="tour-perk-item"><i class="fa-solid fa-masks-theater"></i> Sacred Rites</span>
              <span class="tour-perk-item"><i class="fa-solid fa-circle-nodes"></i> Native Elder Docent</span>
            </div>
            <div class="tour-card-footer">
              <div class="tour-price">
                <span class="price-prefix">From</span>
                <span class="price-amount">$95</span>
                <span class="price-suffix">/Person</span>
              </div>
              <button class="btn btn-sm btn-primary open-booking-btn" data-tour="Zangbeto Festival Tour"
                data-price="$95">Reserve Festival</button>
            </div>
          </div>
        </article>
      </div>

      <div class="text-center mt-5">
        <button class="btn btn-secondary open-booking-btn" data-tour="Custom Tailored Tour">
          Request a Custom Tour Package <i class="fa-solid fa-wand-magic-sparkles"></i>
        </button>
      </div>
    </div>
  </section>

  <!-- Section 2: Badagry Historical Corridors & Landmark Dossiers -->
  <section class="corridor-section section-padding" id="landmarks">
    <div class="site-container">
      <div class="section-title-wrap text-center">
        <span class="sub-title">Monuments of Global Memory</span>
        <h2 class="sec-title">Badagry Historical Corridors</h2>
        <p class="section-lead-text">
          Explore the monumental landmarks that define West African heritage, missionary history, and transatlantic memory, led by accredited native custodians.
        </p>
      </div>

      <div class="dossier-grid">
        <!-- Landmark 1: Point of No Return & Gberefu Island -->
        <article class="dossier-card">
          <div class="dossier-img-box">
            <img src="assets/images/Point-of-No-Return-Monument-Badagry.jpg" alt="Point of No Return Ocean Monument on Gberefu Peninsula" loading="lazy" />
            <div class="dossier-era-pill">Atlantic Gateway</div>
          </div>
          <div class="dossier-body">
            <h3 class="dossier-title">Point of No Return</h3>
            <span class="dossier-designation">Gberefu Peninsula Memorial</span>
            <p class="dossier-desc">
              Solemn pilgrimage path across the sand peninsula between the calm Badagry lagoon and the roaring Atlantic Ocean, where captive Africans boarded European slave vessels.
            </p>
            <div class="dossier-relic-tags">
              <span class="relic-tag">Attenuation Memory Well</span>
              <span class="relic-tag">Atlantic Commemorative Arch</span>
              <span class="relic-tag">Lagoon Canoe Crossing</span>
            </div>
            <a href="#tours" class="dossier-link">Explore Expedition Access <i class="fa-solid fa-arrow-right"></i></a>
          </div>
        </article>

        <!-- Landmark 2: First Storey Building -->
        <article class="dossier-card">
          <div class="dossier-img-box">
            <img src="assets/images/First-Storey-Building-in-Nigeria.jpg" alt="First Storey Building in Nigeria Badagry Built 1845" loading="lazy" />
            <div class="dossier-era-pill">Built 1845</div>
          </div>
          <div class="dossier-body">
            <h3 class="dossier-title">First Storey Building</h3>
            <span class="dossier-designation">CMS Missionary Headquarters</span>
            <p class="dossier-desc">
              Nigeria's earliest multi-storey architectural landmark, built by Rev. C.A. Gollmer. Houses Bishop Samuel Ajayi Crowther's original 1845 Yoruba Bible translation.
            </p>
            <div class="dossier-relic-tags">
              <span class="relic-tag">1845 Yoruba Bible</span>
              <span class="relic-tag">First Western Classroom</span>
              <span class="relic-tag">19th-Century Safe Vaults</span>
            </div>
            <a href="#tours" class="dossier-link">Explore Expedition Access <i class="fa-solid fa-arrow-right"></i></a>
          </div>
        </article>

        <!-- Landmark 3: Seriki Williams Abass Barracoon -->
        <article class="dossier-card">
          <div class="dossier-img-box">
            <img src="assets/images/Seriki-Williams-Abass-Baracoon.jpg" alt="Seriki Williams Abass Brazilian Barracoon Badagry" loading="lazy" />
            <div class="dossier-era-pill">Est. 1840</div>
          </div>
          <div class="dossier-body">
            <h3 class="dossier-title">Seriki Abass Barracoon</h3>
            <span class="dossier-designation">Brazilian-Style Slave Quarters</span>
            <p class="dossier-desc">
              Preserved 19th-century transatlantic slave compound featuring 40 tiny holding cells, personal relics, trade ledgers, and Chief Seriki Williams Abass's historic tomb.
            </p>
            <div class="dossier-relic-tags">
              <span class="relic-tag">40 Original Slave Cells</span>
              <span class="relic-tag">Iron Neck Chains &amp; Shackles</span>
              <span class="relic-tag">Ancient Vlekete Market Route</span>
            </div>
            <a href="#tours" class="dossier-link">Explore Expedition Access <i class="fa-solid fa-arrow-right"></i></a>
          </div>
        </article>

        <!-- Landmark 4: Badagry Heritage Museum -->
        <article class="dossier-card">
          <div class="dossier-img-box">
            <img src="assets/images/Badagry-Heritage-Museum.jpg" alt="Badagry Heritage Museum British District Quarter" loading="lazy" />
            <div class="dossier-era-pill">Est. 1863</div>
          </div>
          <div class="dossier-body">
            <h3 class="dossier-title">Badagry Heritage Museum</h3>
            <span class="dossier-designation">District Officer's Colonial Archive</span>
            <p class="dossier-desc">
              Housed in the former 1863 British District Officer's administrative complex, showcasing eight thematic galleries tracing pre-colonial trade to the abolition movement.
            </p>
            <div class="dossier-relic-tags">
              <span class="relic-tag">8 Curated Gallery Wings</span>
              <span class="relic-tag">Colonial Government Vault</span>
              <span class="relic-tag">Original Trade Manillas</span>
            </div>
            <a href="#tours" class="dossier-link">Explore Expedition Access <i class="fa-solid fa-arrow-right"></i></a>
          </div>
        </article>

        <!-- Landmark 5: Badagry Marina & Whispering Creeks -->
        <article class="dossier-card">
          <div class="dossier-img-box">
            <img src="assets/images/Badagry-Beach-Creek-Leisure-Day.jpg" alt="Badagry Marina and Coastal Lagoon Waters" loading="lazy" />
            <div class="dossier-era-pill">Natural Marine Corridor</div>
          </div>
          <div class="dossier-body">
            <h3 class="dossier-title">Badagry Marina &amp; Creeks</h3>
            <span class="dossier-designation">Inland Waterways &amp; Coastal Palms</span>
            <p class="dossier-desc">
              Tranquil mangrove water systems, indigenous canoe wharfs, and peaceful waterfront resorts connecting Badagry with the Gulf of Guinea coastline.
            </p>
            <div class="dossier-relic-tags">
              <span class="relic-tag">Whispering Palms Shore</span>
              <span class="relic-tag">Traditional Fishing Wharfs</span>
              <span class="relic-tag">Lagoon Boat Cruises</span>
            </div>
            <a href="#tours" class="dossier-link">Explore Expedition Access <i class="fa-solid fa-arrow-right"></i></a>
          </div>
        </article>
      </div>
    </div>
  </section>

  <!-- Section 3: Native Guidance & Heritage Stewardship (Replaces Budget Section) -->
  <section class="stewardship-section section-padding" id="stewardship">
    <div class="site-container">
      <div class="stewardship-grid">
        <!-- Left: Philosophy & Pillars -->
        <div class="stewardship-narrative">
          <span class="sub-title">Our Heritage Stewardship</span>
          <h2>Authentic Heritage. Honest Stewardship.</h2>
          <p class="stewardship-lead">
            We believe Badagry's sacred history and coastal beauty should be experienced with reverence, deep cultural context, and complete financial clarity. Every expedition is guided by native community historians with zero hidden surcharges.
          </p>

          <div class="stewardship-pillars-list">
            <div class="pillar-item">
              <div class="pillar-icon-box">
                <i class="fa-solid fa-people-roof"></i>
              </div>
              <div class="pillar-content">
                <h4>100% Native Badagry Historians</h4>
                <p>Born, raised, and certified in Badagry. Our docents carry generations of oral tradition, royal heritage, and historical documentation.</p>
              </div>
            </div>

            <div class="pillar-item">
              <div class="pillar-icon-box">
                <i class="fa-solid fa-receipt"></i>
              </div>
              <div class="pillar-content">
                <h4>Transparent Tiered Pricing</h4>
                <p>From single-day student educational walks to comprehensive multi-day diaspora homecomings, our rates are upfront with zero surprise fees.</p>
              </div>
            </div>

            <div class="pillar-item">
              <div class="pillar-icon-box">
                <i class="fa-solid fa-hands-holding-circle"></i>
              </div>
              <div class="pillar-content">
                <h4>Community &amp; Site Preservation</h4>
                <p>A direct portion of every tour package supports local monument preservation, boat operators, and youth docent education in Badagry.</p>
              </div>
            </div>
          </div>
        </div>

        <!-- Right: Verified Assurance Panel -->
        <div class="stewardship-assurance-card">
          <div class="assurance-badge">
            <i class="fa-solid fa-shield-halved"></i> Verified Travel Quality
          </div>
          <div>
            <h3 style="font-size: 1.6rem; font-weight: 800; margin-bottom: 8px;">The Visitor Assurance</h3>
            <p style="font-size: 0.92rem; color: rgba(255,255,255,0.75); line-height: 1.55;">
              Whether arriving from Lagos Island, Abuja, or traveling across the Atlantic from the African Diaspora, you travel with vetted local custodians.
            </p>
          </div>

          <div class="assurance-metrics-grid">
            <div class="metric-box">
              <div class="metric-number">100%</div>
              <div class="metric-label">Native Historians</div>
            </div>
            <div class="metric-box">
              <div class="metric-number">11+</div>
              <div class="metric-label">Verified Routes</div>
            </div>
            <div class="metric-box">
              <div class="metric-number">4.9★</div>
              <div class="metric-label">Visitor Rating</div>
            </div>
            <div class="metric-box">
              <div class="metric-number">0</div>
              <div class="metric-label">Hidden Surcharges</div>
            </div>
          </div>

          <div class="assurance-cta-box">
            <a href="http://wa.me/+2347056989224?text=from+google+map+or+site+-+Inquiring+about+custom+itinerary" target="_blank" rel="noopener" class="btn btn-primary w-100">
              <i class="fa-brands fa-whatsapp"></i> Speak With an Indigenous Guide
            </a>
            <span style="font-size: 0.8rem; text-align: center; color: rgba(255,255,255,0.6);">Direct WhatsApp concierge support 7 days a week</span>
          </div>
        </div>
      </div>
    </div>
  </section>

  <!-- Section 4: Curated Travel Styles (Replaces Categories) -->
  <section class="travel-styles-section section-padding" id="styles">
    <div class="site-container">
      <div class="section-title-wrap text-center">
        <span class="sub-title">Bespoke Travel Formats</span>
        <h2 class="sec-title">Curated Travel Styles</h2>
        <p class="section-lead-text">
          Choose an expedition format that aligns with your party size, pace, and depth of historical inquiry.
        </p>
      </div>

      <div class="travel-styles-grid">
        <!-- Style 1 -->
        <div class="travel-style-card">
          <div class="style-icon-wrap">
            <i class="fa-solid fa-person-walking-luggage"></i>
          </div>
          <h3 class="style-title">Historical Pilgrimage</h3>
          <p class="style-desc">Solemn, reflective exploration of the slave trade corridors, First Storey Building, Seriki Abass barracoons, and the Gberefu Atlantic trail.</p>
          <div class="style-footer">
            <span class="style-duration-chip">1 - 3 Days</span>
            <a href="#tours" class="style-explore-btn">View Expeditions <i class="fa-solid fa-arrow-right"></i></a>
          </div>
        </div>

        <!-- Style 2 -->
        <div class="travel-style-card">
          <div class="style-icon-wrap">
            <i class="fa-solid fa-water"></i>
          </div>
          <h3 class="style-title">Coastal Leisure &amp; Waterways</h3>
          <p class="style-desc">Unwind along Badagry's serene lagoon waterways, mangrove channels, coconut shorelines, and waterfront resort sanctuaries.</p>
          <div class="style-footer">
            <span class="style-duration-chip">1 - 2 Days</span>
            <a href="#tours" class="style-explore-btn">View Expeditions <i class="fa-solid fa-arrow-right"></i></a>
          </div>
        </div>

        <!-- Style 3 -->
        <div class="travel-style-card">
          <div class="style-icon-wrap">
            <i class="fa-solid fa-graduation-cap"></i>
          </div>
          <h3 class="style-title">Educational &amp; Student Groups</h3>
          <p class="style-desc">Curriculum-aligned historical excursions designed for schools, universities, and youth delegations with interactive learning docents.</p>
          <div class="style-footer">
            <span class="style-duration-chip">Full Day</span>
            <a href="#tours" class="style-explore-btn">View Expeditions <i class="fa-solid fa-arrow-right"></i></a>
          </div>
        </div>

        <!-- Style 4 -->
        <div class="travel-style-card">
          <div class="style-icon-wrap">
            <i class="fa-solid fa-earth-africa"></i>
          </div>
          <h3 class="style-title">Diaspora Roots Homecoming</h3>
          <p class="style-desc">Deeply personal ancestral pilgrimages featuring traditional welcome rites, genealogical reflection, and Atlantic healing ceremonies.</p>
          <div class="style-footer">
            <span class="style-duration-chip">3 - 5 Days</span>
            <a href="#tours" class="style-explore-btn">View Expeditions <i class="fa-solid fa-arrow-right"></i></a>
          </div>
        </div>

        <!-- Style 5 -->
        <div class="travel-style-card">
          <div class="style-icon-wrap">
            <i class="fa-solid fa-briefcase"></i>
          </div>
          <h3 class="style-title">Corporate &amp; Executive Retreats</h3>
          <p class="style-desc">Premium team retreats combining coastal reflection, cultural grounding, VIP transport logistics, and private beachfront venues.</p>
          <div class="style-footer">
            <span class="style-duration-chip">2 - 3 Days</span>
            <a href="#tours" class="style-explore-btn">View Expeditions <i class="fa-solid fa-arrow-right"></i></a>
          </div>
        </div>

        <!-- Style 6 -->
        <div class="travel-style-card">
          <div class="style-icon-wrap">
            <i class="fa-solid fa-masks-theater"></i>
          </div>
          <h3 class="style-title">Living Traditions &amp; Festivals</h3>
          <p class="style-desc">Witness living Ogu culture and mystical ceremonies, including the sacred Zangbeto masquerade, traditional drumming, and community dances.</p>
          <div class="style-footer">
            <span class="style-duration-chip">Seasonal Excursions</span>
            <a href="#tours" class="style-explore-btn">View Expeditions <i class="fa-solid fa-arrow-right"></i></a>
          </div>
        </div>
      </div>
    </div>
  </section>

  <!-- Section 5: The Badagry Heritage Chronicle -->
  <section class="journal-section section-padding bg-light" id="chronicle">
    <div class="site-container">
      <div class="journal-header-wrap">
        <div>
          <span class="sub-title">Research &amp; Field Guides</span>
          <h2 class="sec-title">The Badagry Heritage Chronicle</h2>
          <p style="color: var(--text-muted); font-size: 0.95rem; margin-top: 6px;">Authentic historical perspectives, transit walkthroughs, and visitor counsel curated by our local guides.</p>
        </div>
        <a href="/blog/" class="btn btn-outline-primary">Browse All Chronicle Essays <i class="fa-solid fa-arrow-right"></i></a>
      </div>

      <div class="journal-grid">
        <?php if (!empty($topPosts)): ?>
          <?php foreach ($topPosts as $post): ?>
            <?php
              $postSlug = $post['slug'] ?? '';
              $postUrl = '/blog/' . urlencode($postSlug);
              $postImg = !empty($post['featured_image']) ? $post['featured_image'] : 'assets/images/Point-of-No-Return-420x347.jpg';
              $isVid = (bool) preg_match('/\.(mp4|webm|ogg|mov|m4v)$/i', $postImg);
              $mediaSrc = preg_match('#^https?://#i', $postImg) ? htmlspecialchars($postImg) : '/' . ltrim(htmlspecialchars($postImg), '/');
              $pubDate = !empty($post['published_at']) ? date('M d, Y', strtotime($post['published_at'])) : date('M d, Y');
              $readTime = !empty($post['read_time']) ? htmlspecialchars($post['read_time']) : '5 min read';
              $postTitle = htmlspecialchars($post['title'] ?? '');
              $postExcerpt = htmlspecialchars($post['excerpt'] ?? '');
            ?>
            <!-- Article <?= (int)($post['id'] ?? 0) ?> -->
            <article class="journal-card">
              <div class="journal-img-wrap">
                <a href="<?= $postUrl ?>">
                  <?php if ($isVid): ?>
                    <video src="<?= $mediaSrc ?>#t=0.5" muted playsinline preload="metadata" style="width: 100%; height: 100%; object-fit: cover;"></video>
                  <?php else: ?>
                    <img src="<?= $mediaSrc ?>" alt="<?= $postTitle ?>" loading="lazy" />
                  <?php endif; ?>
                </a>
              </div>
              <div class="journal-card-body">
                <div class="journal-meta">
                  <span><i class="fa-regular fa-calendar"></i> <?= $pubDate ?></span>
                  <span><i class="fa-regular fa-clock"></i> <?= $readTime ?></span>
                </div>
                <h3 class="journal-title">
                  <a href="<?= $postUrl ?>"><?= $postTitle ?></a>
                </h3>
                <p class="journal-excerpt"><?= $postExcerpt ?></p>
                <a href="<?= $postUrl ?>" class="btn-text-link">Read Chronicle Essay <i class="fa-solid fa-arrow-right-long"></i></a>
              </div>
            </article>
          <?php endforeach; ?>
        <?php else: ?>
          <p class="text-muted" style="grid-column: 1 / -1; text-align: center; padding: 2rem 0;">No articles published yet. Check back soon!</p>
        <?php endif; ?>
      </div>
    </div>
  </section>

  <!-- Section 6: Field Moments: Badagry Visual Archive (Replaces Gallery) -->
  <section class="gallery-section section-padding" id="gallery">
    <div class="site-container">
      <div class="section-title-wrap text-center">
        <span class="sub-title">Living Documentation</span>
        <h2 class="sec-title">Field Moments: Badagry Visual Archive</h2>
        <p class="sec-intro text-muted" style="max-width: 680px; margin: 12px auto 0;">
          Authentic moments captured across our guided heritage routes, sacred memorials, mangrove waterways, and cultural celebrations.
        </p>
      </div>

      <div class="gallery-preview-grid">
        <a href="/gallery/" class="gallery-preview-card" title="Badagry Slave Memorial and Freedom Statue">
          <div class="preview-img-wrap">
            <img src="assets/images/Badagry-Slave-Memorial-Statue.jpg" alt="Badagry Slave Memorial and Freedom Statue"
              loading="lazy" />
            <span class="preview-country-badge">Memorial Monument</span>
            <div class="preview-hover-overlay">
              <span class="preview-zoom-icon"><i class="fa-solid fa-arrow-up-right-from-square"></i></span>
              <span class="preview-overlay-title">Badagry Slave Memorial</span>
              <span class="preview-overlay-action">Open Visual Archive <i class="fa-solid fa-arrow-right"></i></span>
            </div>
          </div>
        </a>

        <a href="/gallery/" class="gallery-preview-card" title="First Storey Building in Nigeria (1845)">
          <div class="preview-img-wrap">
            <img src="assets/images/First-Storey-Building-Badagry.jpg" alt="First Storey Building in Nigeria (1845)"
              loading="lazy" />
            <span class="preview-country-badge">Built 1845</span>
            <div class="preview-hover-overlay">
              <span class="preview-zoom-icon"><i class="fa-solid fa-arrow-up-right-from-square"></i></span>
              <span class="preview-overlay-title">First Storey Building</span>
              <span class="preview-overlay-action">Open Visual Archive <i class="fa-solid fa-arrow-right"></i></span>
            </div>
          </div>
        </a>

        <a href="/gallery/" class="gallery-preview-card" title="Point of No Return Monument on Gberefu Peninsula">
          <div class="preview-img-wrap">
            <img src="assets/images/Point-of-No-Return-Monument-Badagry.jpg"
              alt="Point of No Return Monument on Gberefu Peninsula" loading="lazy" />
            <span class="preview-country-badge">Gberefu Peninsula</span>
            <div class="preview-hover-overlay">
              <span class="preview-zoom-icon"><i class="fa-solid fa-arrow-up-right-from-square"></i></span>
              <span class="preview-overlay-title">Point of No Return</span>
              <span class="preview-overlay-action">Open Visual Archive <i class="fa-solid fa-arrow-right"></i></span>
            </div>
          </div>
        </a>

        <a href="/gallery/" class="gallery-preview-card" title="Seriki Williams Abass Brazilian Barracoon">
          <div class="preview-img-wrap">
            <img src="assets/images/Seriki-Williams-Abass-Baracoon.jpg"
              alt="Seriki Williams Abass Brazilian Barracoon Badagry" loading="lazy" />
            <span class="preview-country-badge">1840 Barracoon</span>
            <div class="preview-hover-overlay">
              <span class="preview-zoom-icon"><i class="fa-solid fa-arrow-up-right-from-square"></i></span>
              <span class="preview-overlay-title">Seriki Abass Barracoon</span>
              <span class="preview-overlay-action">Open Visual Archive <i class="fa-solid fa-arrow-right"></i></span>
            </div>
          </div>
        </a>

        <a href="/gallery/" class="gallery-preview-card" title="Badagry Heritage Museum &amp; Slave Relics">
          <div class="preview-img-wrap">
            <img src="assets/images/Badagry-Heritage-Museum.jpg" alt="Badagry Heritage Museum"
              loading="lazy" />
            <span class="preview-country-badge">Heritage Museum</span>
            <div class="preview-hover-overlay">
              <span class="preview-zoom-icon"><i class="fa-solid fa-arrow-up-right-from-square"></i></span>
              <span class="preview-overlay-title">Badagry Heritage Museum</span>
              <span class="preview-overlay-action">Open Visual Archive <i class="fa-solid fa-arrow-right"></i></span>
            </div>
          </div>
        </a>

        <a href="/gallery/" class="gallery-preview-card" title="Route to Unknown Destination (Slave Trail)">
          <div class="preview-img-wrap">
            <img src="assets/images/Route-to-Unknown-Destination.jpg" alt="Route to Unknown Destination Badagry" loading="lazy" />
            <span class="preview-country-badge">Slave Trail</span>
            <div class="preview-hover-overlay">
              <span class="preview-zoom-icon"><i class="fa-solid fa-arrow-up-right-from-square"></i></span>
              <span class="preview-overlay-title">Route to Unknown Destination</span>
              <span class="preview-overlay-action">Open Visual Archive <i class="fa-solid fa-arrow-right"></i></span>
            </div>
          </div>
        </a>

        <a href="/gallery/" class="gallery-preview-card" title="Badagry Beach &amp; Creek Leisure Cruise">
          <div class="preview-img-wrap">
            <img src="assets/images/Badagry-Beach-Creek-Leisure-Day.jpg" alt="Badagry Beach and Creek Boat Cruise"
              loading="lazy" />
            <span class="preview-country-badge">Coastal Marina</span>
            <div class="preview-hover-overlay">
              <span class="preview-zoom-icon"><i class="fa-solid fa-arrow-up-right-from-square"></i></span>
              <span class="preview-overlay-title">Beach &amp; Creek Leisure</span>
              <span class="preview-overlay-action">Open Visual Archive <i class="fa-solid fa-arrow-right"></i></span>
            </div>
          </div>
        </a>

        <a href="/gallery/" class="gallery-preview-card" title="Private Group Tour on Badagry Heritage Route">
          <div class="preview-img-wrap">
            <img src="assets/images/Private-Group-Badagry-Heritage-Day-Tour.jpg"
              alt="Private Group Tour on Badagry Heritage Route" loading="lazy" />
            <span class="preview-country-badge">Guided Group</span>
            <div class="preview-hover-overlay">
              <span class="preview-zoom-icon"><i class="fa-solid fa-arrow-up-right-from-square"></i></span>
              <span class="preview-overlay-title">Private Group Tour</span>
              <span class="preview-overlay-action">Open Visual Archive <i class="fa-solid fa-arrow-right"></i></span>
            </div>
          </div>
        </a>
      </div>

      <!-- Gallery CTA to View All -->
      <div class="gallery-view-more-cta text-center" style="margin-top: 40px;">
        <a href="/gallery/" class="btn btn-primary btn-lg">
          <span>Explore All 50+ Photos &amp; Videos in Full Archive</span>
          <i class="fa-solid fa-arrow-right"></i>
        </a>
        <div class="gallery-preview-badges"
          style="margin-top: 15px; display: flex; justify-content: center; gap: 20px; flex-wrap: wrap;">
          <span class="gallery-badge-pill"><i class="fa-solid fa-images"></i> 50+ High-Resolution Photos</span>
          <span class="gallery-badge-pill"><i class="fa-solid fa-video"></i> Video Highlights</span>
          <span class="gallery-badge-pill"><i class="fa-solid fa-landmark"></i> Historical Archives</span>
        </div>
      </div>
    </div>
  </section>

  <!-- Lightbox Modal -->
  <div class="lightbox-modal" id="lightboxModal">
    <button class="lightbox-close" id="lightboxClose" aria-label="Close Lightbox">&times;</button>
    <div class="lightbox-content">
      <img src="" alt="" id="lightboxImg" />
      <p class="lightbox-caption" id="lightboxCaption"></p>
    </div>
  </div>

  <!-- Section 7: Traveler Perspectives & Field Dispatches (Replaces Testimonials) -->
  <section class="testimonials-section section-padding bg-light" id="testimonials">
    <div class="site-container">
      <div class="section-title-wrap text-center">
        <span class="sub-title">Traveler Dispatches</span>
        <h2 class="sec-title">Traveler Perspectives &amp; Field Dispatches</h2>
        <p style="color: var(--text-muted); font-size: 0.95rem; margin-top: 6px;">
          Unfiltered reflections and verified reviews from visitors who walked Badagry's coastal routes and historical trails with us.
        </p>
      </div>

      <div class="testimonials-slider-wrap">
        <div class="testimonials-grid" id="testimonialsGrid">
          <!-- Testimonial 1 -->
          <div class="testi-card">
            <div class="testi-header">
              <div class="testi-profile">
                <img src="assets/images/Olayinka-Oyeku.jpg" alt="Olayinka Oyeku" class="testi-avatar"
                  loading="lazy" />
                <div class="testi-meta">
                  <h3 class="testi-name">Olayinka Oyeku</h3>
                  <span class="testi-location">Lagos, Nigeria</span>
                </div>
              </div>
              <div class="testi-stars">
                <i class="fa-solid fa-star"></i><i class="fa-solid fa-star"></i><i class="fa-solid fa-star"></i><i
                  class="fa-solid fa-star"></i><i class="fa-solid fa-star"></i>
              </div>
            </div>
            <p class="testi-quote">
              "Taking the Badagry Heritage Day Tour opened my eyes to history in a way books never could. Walking through
              the 1845 First Storey Building and taking the boat across the creek to Gberefu Island was deeply touching.
              Our guide was patient, knowledgeable, and treated our heritage with the utmost reverence."
            </p>
            <div class="testi-icon-mark"><i class="fa-solid fa-quote-right"></i></div>
          </div>

          <!-- Testimonial 2 -->
          <div class="testi-card">
            <div class="testi-header">
              <div class="testi-profile">
                <img src="assets/images/Adedoyin-Dosunmu-Ogunbi.jpg" alt="Adedoyin Dosunmu-Ogunbi" class="testi-avatar"
                  loading="lazy" />
                <div class="testi-meta">
                  <h3 class="testi-name">Adedoyin Dosunmu-Ogunbi</h3>
                  <span class="testi-location">Lagos, Nigeria</span>
                </div>
              </div>
              <div class="testi-stars">
                <i class="fa-solid fa-star"></i><i class="fa-solid fa-star"></i><i class="fa-solid fa-star"></i><i
                  class="fa-solid fa-star"></i><i class="fa-solid fa-star"></i>
              </div>
            </div>
            <p class="testi-quote">
              "Taking the 3-day Badagry Heritage immersion tour was an extraordinary educational experience. Walking the solemn slave route across Gberefu Island to the Point of No Return and examining the preserved relics in the Brazilian Barracoons gave me profound historical insight. Flawless organization from pickup to drop-off."
            </p>
            <div class="testi-icon-mark"><i class="fa-solid fa-quote-right"></i></div>
          </div>

          <!-- Testimonial 3 -->
          <div class="testi-card">
            <div class="testi-header">
              <div class="testi-profile">
                <img src="assets/images/Ashola-Moyinoluwa.jpg" alt="Ashola Moyinoluwa" class="testi-avatar"
                  loading="lazy" />
                <div class="testi-meta">
                  <h3 class="testi-name">Ashola Moyinoluwa</h3>
                  <span class="testi-location">Atlanta, USA (African Diaspora)</span>
                </div>
              </div>
              <div class="testi-stars">
                <i class="fa-solid fa-star"></i><i class="fa-solid fa-star"></i><i class="fa-solid fa-star"></i><i
                  class="fa-solid fa-star"></i><i class="fa-solid fa-star"></i>
              </div>
            </div>
            <p class="testi-quote">
              "As an African-American walking the path toward the Point of No Return, words truly fail me. Badagry Visitors
              Guide provided not just an itinerary, but a sacred, respectful homecoming space for reflection. The local
              historians answered every question with honesty, grace, and deep cultural warmth."
            </p>
            <div class="testi-icon-mark"><i class="fa-solid fa-quote-right"></i></div>
          </div>

          <!-- Testimonial 4 -->
          <div class="testi-card">
            <div class="testi-header">
              <div class="testi-profile">
                <img src="assets/images/Ade-Sofola.jpg" alt="Ade Sofola" class="testi-avatar"
                  loading="lazy" />
                <div class="testi-meta">
                  <h3 class="testi-name">Ade Sofola</h3>
                  <span class="testi-location">Lagos, Nigeria</span>
                </div>
              </div>
              <div class="testi-stars">
                <i class="fa-solid fa-star"></i><i class="fa-solid fa-star"></i><i class="fa-solid fa-star"></i><i
                  class="fa-solid fa-star"></i><i class="fa-solid fa-star"></i>
              </div>
            </div>
            <p class="testi-quote">
              "Our weekend getaway to Badagry exceeded every expectation! The peaceful Badagry creek boat cruise, the 1845 First Storey Building tour, and relaxing along the coastal waterfront made this the highlight of our visit to Nigeria. Outstanding local guides and warm hospitality throughout."
            </p>
            <div class="testi-icon-mark"><i class="fa-solid fa-quote-right"></i></div>
          </div>

          <!-- Testimonial 5 -->
          <div class="testi-card">
            <div class="testi-header">
              <div class="testi-profile">
                <img src="assets/images/Tutor-Saheed.png" alt="Tutor Saheed" class="testi-avatar" loading="lazy" />
                <div class="testi-meta">
                  <h3 class="testi-name">Tutor Saheed</h3>
                  <span class="testi-location">Victoria Island, Lagos</span>
                </div>
              </div>
              <div class="testi-stars">
                <i class="fa-solid fa-star"></i><i class="fa-solid fa-star"></i><i class="fa-solid fa-star"></i><i
                  class="fa-solid fa-star"></i><i class="fa-solid fa-star"></i>
              </div>
            </div>
            <p class="testi-quote">
              "I coordinated a corporate retreat for 16 colleagues and Badagry Visitors Guide built our itinerary around our
              exact budget and schedule. Every logistics checkpoint, meal, and guided stop ran like clockwork. Our entire
              management team came back inspired and energized."
            </p>
            <div class="testi-icon-mark"><i class="fa-solid fa-quote-right"></i></div>
          </div>

          <!-- Testimonial 6 -->
          <div class="testi-card">
            <div class="testi-header">
              <div class="testi-profile">
                <img src="assets/images/SEGUN-OPAYELE.png" alt="Segun Opayele" class="testi-avatar"
                  loading="lazy" />
                <div class="testi-meta">
                  <h3 class="testi-name">Segun Opayele</h3>
                  <span class="testi-location">Abuja, FCT, Nigeria</span>
                </div>
              </div>
              <div class="testi-stars">
                <i class="fa-solid fa-star"></i><i class="fa-solid fa-star"></i><i class="fa-solid fa-star"></i><i
                  class="fa-solid fa-star"></i><i class="fa-solid fa-star"></i>
              </div>
            </div>
            <p class="testi-quote">
              "I travelled from Abuja specifically to connect with Badagry's coastal slave routes and relics. Our guide's
              storytelling brought the 19th-century barracoons and heritage artifacts alive with incredible historical depth.
              Professional, safe, and worth every kilometer travelled."
            </p>
            <div class="testi-icon-mark"><i class="fa-solid fa-quote-right"></i></div>
          </div>
        </div>

        <!-- Cloned track for seamless infinite auto-scroll loop (hidden from assistive tech) -->
        <div class="testimonials-grid testimonials-grid-clone" aria-hidden="true">
          <!-- Testimonial 1 Clone -->
          <div class="testi-card">
            <div class="testi-header">
              <div class="testi-profile">
                <img src="assets/images/Olayinka-Oyeku.jpg" alt="Olayinka Oyeku" class="testi-avatar" loading="lazy" />
                <div class="testi-meta">
                  <span class="testi-name">Olayinka Oyeku</span>
                  <span class="testi-location">Lagos, Nigeria</span>
                </div>
              </div>
              <div class="testi-stars">
                <i class="fa-solid fa-star"></i><i class="fa-solid fa-star"></i><i class="fa-solid fa-star"></i><i class="fa-solid fa-star"></i><i class="fa-solid fa-star"></i>
              </div>
            </div>
            <p class="testi-quote">
              "Taking the Badagry Heritage Day Tour opened my eyes to history in a way books never could. Walking through
              the 1845 First Storey Building and taking the boat across the creek to Gberefu Island was deeply touching.
              Our guide was patient, knowledgeable, and treated our heritage with the utmost reverence."
            </p>
            <div class="testi-icon-mark"><i class="fa-solid fa-quote-right"></i></div>
          </div>

          <!-- Testimonial 2 Clone -->
          <div class="testi-card">
            <div class="testi-header">
              <div class="testi-profile">
                <img src="assets/images/Adedoyin-Dosunmu-Ogunbi.jpg" alt="Adedoyin Dosunmu-Ogunbi" class="testi-avatar" loading="lazy" />
                <div class="testi-meta">
                  <span class="testi-name">Adedoyin Dosunmu-Ogunbi</span>
                  <span class="testi-location">Lagos, Nigeria</span>
                </div>
              </div>
              <div class="testi-stars">
                <i class="fa-solid fa-star"></i><i class="fa-solid fa-star"></i><i class="fa-solid fa-star"></i><i class="fa-solid fa-star"></i><i class="fa-solid fa-star"></i>
              </div>
            </div>
            <p class="testi-quote">
              "Taking the 3-day Badagry Heritage immersion tour was an extraordinary educational experience. Walking the solemn slave route across Gberefu Island to the Point of No Return and examining the preserved relics in the Brazilian Barracoons gave me profound historical insight. Flawless organization from pickup to drop-off."
            </p>
            <div class="testi-icon-mark"><i class="fa-solid fa-quote-right"></i></div>
          </div>

          <!-- Testimonial 3 Clone -->
          <div class="testi-card">
            <div class="testi-header">
              <div class="testi-profile">
                <img src="assets/images/Ashola-Moyinoluwa.jpg" alt="Ashola Moyinoluwa" class="testi-avatar" loading="lazy" />
                <div class="testi-meta">
                  <span class="testi-name">Ashola Moyinoluwa</span>
                  <span class="testi-location">Atlanta, USA (African Diaspora)</span>
                </div>
              </div>
              <div class="testi-stars">
                <i class="fa-solid fa-star"></i><i class="fa-solid fa-star"></i><i class="fa-solid fa-star"></i><i class="fa-solid fa-star"></i><i class="fa-solid fa-star"></i>
              </div>
            </div>
            <p class="testi-quote">
              "As an African-American walking the path toward the Point of No Return, words truly fail me. Badagry Visitors
              Guide provided not just an itinerary, but a sacred, respectful homecoming space for reflection. The local
              historians answered every question with honesty, grace, and deep cultural warmth."
            </p>
            <div class="testi-icon-mark"><i class="fa-solid fa-quote-right"></i></div>
          </div>

          <!-- Testimonial 4 Clone -->
          <div class="testi-card">
            <div class="testi-header">
              <div class="testi-profile">
                <img src="assets/images/Ade-Sofola.jpg" alt="Ade Sofola" class="testi-avatar" loading="lazy" />
                <div class="testi-meta">
                  <span class="testi-name">Ade Sofola</span>
                  <span class="testi-location">Lagos, Nigeria</span>
                </div>
              </div>
              <div class="testi-stars">
                <i class="fa-solid fa-star"></i><i class="fa-solid fa-star"></i><i class="fa-solid fa-star"></i><i class="fa-solid fa-star"></i><i class="fa-solid fa-star"></i>
              </div>
            </div>
            <p class="testi-quote">
              "Our weekend getaway to Badagry exceeded every expectation! The peaceful Badagry creek boat cruise, the 1845 First Storey Building tour, and relaxing along the coastal waterfront made this the highlight of our visit to Nigeria. Outstanding local guides and warm hospitality throughout."
            </p>
            <div class="testi-icon-mark"><i class="fa-solid fa-quote-right"></i></div>
          </div>

          <!-- Testimonial 5 Clone -->
          <div class="testi-card">
            <div class="testi-header">
              <div class="testi-profile">
                <img src="assets/images/Tutor-Saheed.png" alt="Tutor Saheed" class="testi-avatar" loading="lazy" />
                <div class="testi-meta">
                  <span class="testi-name">Tutor Saheed</span>
                  <span class="testi-location">Victoria Island, Lagos</span>
                </div>
              </div>
              <div class="testi-stars">
                <i class="fa-solid fa-star"></i><i class="fa-solid fa-star"></i><i class="fa-solid fa-star"></i><i class="fa-solid fa-star"></i><i class="fa-solid fa-star"></i>
              </div>
            </div>
            <p class="testi-quote">
              "I coordinated a corporate retreat for 16 colleagues and Badagry Visitors Guide built our itinerary around our
              exact budget and schedule. Every logistics checkpoint, meal, and guided stop ran like clockwork. Our entire
              management team came back inspired and energized."
            </p>
            <div class="testi-icon-mark"><i class="fa-solid fa-quote-right"></i></div>
          </div>

          <!-- Testimonial 6 Clone -->
          <div class="testi-card">
            <div class="testi-header">
              <div class="testi-profile">
                <img src="assets/images/SEGUN-OPAYELE.png" alt="Segun Opayele" class="testi-avatar" loading="lazy" />
                <div class="testi-meta">
                  <span class="testi-name">Segun Opayele</span>
                  <span class="testi-location">Abuja, FCT, Nigeria</span>
                </div>
              </div>
              <div class="testi-stars">
                <i class="fa-solid fa-star"></i><i class="fa-solid fa-star"></i><i class="fa-solid fa-star"></i><i class="fa-solid fa-star"></i><i class="fa-solid fa-star"></i>
              </div>
            </div>
            <p class="testi-quote">
              "I travelled from Abuja specifically to connect with Badagry's coastal slave routes and relics. Our guide's
              storytelling brought the 19th-century barracoons and heritage artifacts alive with incredible historical depth.
              Professional, safe, and worth every kilometer travelled."
            </p>
            <div class="testi-icon-mark"><i class="fa-solid fa-quote-right"></i></div>
          </div>
        </div>
      </div>
    </div>
  </section>

  <!-- Section 8: The Badagry Heritage Dispatch (Replaces Newsletter) -->
  <section class="newsletter-section" id="newsletter">
    <div class="site-container">
      <div class="newsletter-box">
        <div class="newsletter-left">
          <div class="newsletter-icon">
            <i class="fa-regular fa-envelope-open"></i>
          </div>
          <div>
            <span class="newsletter-badge">Cultural Circle &amp; Guides</span>
            <h2 class="newsletter-title">The Badagry Heritage Dispatch</h2>
            <p class="newsletter-desc">
              Subscribe to receive new historical research, festival announcements (including Zangbeto &amp; Gboyo), and seasonal expedition dates.
            </p>
          </div>
        </div>
        <form class="newsletter-form" id="newsletterForm">
          <input type="email" placeholder="Enter your email address" class="newsletter-input" required />
          <button type="submit" class="btn btn-dark newsletter-btn">
            <span>Join Dispatch Circle</span>
            <i class="fa-solid fa-paper-plane"></i>
          </button>
        </form>
      </div>
    </div>
  </section>

  <!-- Section 9: Independent Bespoke Footer -->
  <footer class="footer-wrapper" id="contact">
    <div class="site-container">
      <div class="footer-grid">
        <!-- Col 1: Identity & Stewardship -->
        <div class="footer-col footer-col-about">
          <a href="#" class="footer-logo">
            <img src="assets/logo.jpg" alt="Badagry Visitors Guide" class="footer-logo-img" />
            <div class="footer-logo-text">BADAGRY <strong>VISITORS GUIDE</strong></div>
          </a>
          <p class="footer-about-text">
            Independent tourism and cultural heritage guide dedicated to preserving and sharing the living memory of Badagry, Lagos State, Nigeria. Guided by indigenous historians.
          </p>
          <div class="footer-social-links">
            <a href="https://www.instagram.com/badagryvisitorsguide" target="_blank" rel="noopener"
              aria-label="Instagram"><i class="fa-brands fa-instagram"></i></a>
            <a href="https://www.tiktok.com/@vezeltour" target="_blank" rel="noopener" aria-label="TikTok"><i
                class="fa-brands fa-tiktok"></i></a>
            <a href="http://wa.me/+2347056989224?text=from+google+map+or+site" target="_blank" rel="noopener"
              aria-label="WhatsApp"><i class="fa-brands fa-whatsapp"></i></a>
            <a href="https://www.facebook.com/share/1EnBcsZgqb/" target="_blank" rel="noopener" aria-label="Facebook"><i
                class="fa-brands fa-facebook-f"></i></a>
            <a href="https://x.com/badagry_v_guide" target="_blank" rel="noopener" aria-label="X (Twitter)"><i
                class="fa-brands fa-x-twitter"></i></a>
            <a href="https://www.linkedin.com/in/ezekiel-viavonu-105a15111/" target="_blank" rel="noopener"
              aria-label="LinkedIn"><i class="fa-brands fa-linkedin-in"></i></a>
          </div>
        </div>

        <!-- Col 2: Historical Corridors & Sites -->
        <div class="footer-col">
          <h3 class="footer-title">Historical Corridors</h3>
          <ul class="footer-menu">
            <li><a href="#landmarks">Point of No Return &amp; Gberefu</a></li>
            <li><a href="#landmarks">First Storey Building (1845)</a></li>
            <li><a href="#landmarks">Seriki Abass Slave Barracoon</a></li>
            <li><a href="#landmarks">Badagry Heritage Museum</a></li>
            <li><a href="#landmarks">Vlekete Slave Market Route</a></li>
            <li><a href="#landmarks">Badagry Marina &amp; Creeks</a></li>
          </ul>
        </div>

        <!-- Col 3: Expedition Styles -->
        <div class="footer-col">
          <h3 class="footer-title">Expedition Portfolios</h3>
          <ul class="footer-menu">
            <li><a href="#tours">Single-Day Cultural Circuits</a></li>
            <li><a href="#tours">Coastal &amp; Beach Weekends</a></li>
            <li><a href="#tours">Educational &amp; School Groups</a></li>
            <li><a href="#tours">Diaspora Roots Pilgrimages</a></li>
            <li><a href="#tours">Executive &amp; Corporate Retreats</a></li>
            <li><a href="/gallery/">Visual Archive &amp; Media</a></li>
            <li><a href="/blog/">Heritage Chronicle</a></li>
          </ul>
        </div>

        <!-- Col 4: Badagry Tourism Desk -->
        <div class="footer-col">
          <h3 class="footer-title">Badagry Tourism Desk</h3>
          <div class="footer-contact-items">
            <div class="contact-item">
              <i class="fa-solid fa-phone"></i>
              <div>
                <span class="item-label">Direct Concierge Line</span>
                <a href="http://wa.me/+2347056989224?text=from+google+map+or+site" target="_blank" rel="noopener">+234 705 698 9224</a>
              </div>
            </div>
            <div class="contact-item">
              <i class="fa-solid fa-phone"></i>
              <div>
                <span class="item-label">Tour Operations Support</span>
                <a href="http://wa.me/+2348025937044?text=from+google+map+or+site" target="_blank" rel="noopener">+234 802 593 7044</a>
              </div>
            </div>
            <div class="contact-item">
              <i class="fa-solid fa-envelope"></i>
              <div>
                <span class="item-label">Inquiry Desk</span>
                <a href="mailto:info@badagryvisitorsguide.com">info@badagryvisitorsguide.com</a>
              </div>
            </div>
            <div class="contact-item">
              <i class="fa-solid fa-location-dot"></i>
              <div>
                <span class="item-label">Badagry Information Office</span>
                <p>Badagry Roundabout, Badagry, Lagos State, Nigeria.</p>
              </div>
            </div>
          </div>
        </div>
      </div>

      <div class="footer-bottom">
        <p class="copyright-text">
          &copy; Copyright 2026 <strong>Badagry Visitors Guide Ltd</strong>. All Rights Reserved.
          <span style="display: block; font-size: 0.78rem; color: #64748b; margin-top: 4px;">
            Badagry Visitors Guide is an independent tourism and cultural heritage operator. All packages, original historical texts, and multimedia assets are independently owned and operated.
          </span>
        </p>
        <div class="payment-badges">
          <span class="badge-pay"><i class="fa-brands fa-cc-visa"></i> Visa</span>
          <span class="badge-pay"><i class="fa-brands fa-cc-mastercard"></i> Mastercard</span>
          <span class="badge-pay"><i class="fa-solid fa-credit-card"></i> Verve</span>
          <span class="badge-pay"><i class="fa-solid fa-money-bill-transfer"></i> Bank Transfer</span>
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
            <img src="assets/logo.jpg" alt="Badagry Visitors Guide" class="wa-avatar-img" />
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
          <p>Planning a trip to Badagry? Click below to chat directly with our local tour
            guides on WhatsApp.</p>
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

    <a href="http://wa.me/+2347056989224?text=from+google+map+or+site" target="_blank" rel="noopener"
      class="wa-toggle-btn" id="waToggleBtn" aria-label="Chat on WhatsApp">
      <i class="fa-brands fa-whatsapp"></i>
      <span class="wa-tooltip">Chat with us!</span>
    </a>
  </div>

  <!-- Interactive Tour Booking Modal -->
  <div class="booking-modal-overlay" id="bookingModalOverlay">
    <div class="booking-modal">
      <button class="modal-close-btn" id="modalCloseBtn" aria-label="Close modal">&times;</button>
      <div class="modal-header">
        <span class="modal-badge">Plan Your Adventure</span>
        <h3 class="modal-title" id="modalTitle">Book Your Tour</h3>
        <p class="modal-subtitle">Tell us what you need and our local travel specialists will reach out promptly.</p>
      </div>
      <form id="bookingForm" class="booking-form">
        <div class="form-group">
          <label for="formSelectedTour">Selected Experience</label>
          <input type="text" id="formSelectedTour" class="form-input" readonly />
        </div>

        <div class="form-row">
          <div class="form-group">
            <label for="formFullName">Full Name *</label>
            <input type="text" id="formFullName" class="form-input" placeholder="e.g. Adeola Johnson" required />
          </div>
          <div class="form-group">
            <label for="formEmail">Email Address *</label>
            <input type="email" id="formEmail" class="form-input" placeholder="e.g. name@example.com" required />
          </div>
        </div>

        <div class="form-row">
          <div class="form-group">
            <label for="formPhone">Phone / WhatsApp Number *</label>
            <input type="tel" id="formPhone" class="form-input" placeholder="e.g. +234 800 000 0000" required />
          </div>
          <div class="form-group">
            <label for="formDate">Preferred Tour Date *</label>
            <input type="date" id="formDate" class="form-input" required />
          </div>
        </div>

        <div class="form-row">
          <div class="form-group">
            <label for="formGuests">Number of Travelers</label>
            <select id="formGuests" class="form-select">
              <option value="1">1 Traveler (Solo)</option>
              <option value="2" selected>2 Travelers (Couple)</option>
              <option value="3-5">3 - 5 Travelers (Small Group / Family)</option>
              <option value="6-10">6 - 10 Travelers</option>
              <option value="11+">11+ Travelers (Large Group / School)</option>
            </select>
          </div>
          <div class="form-group">
            <label for="formBudget">Approximate Budget (NGN / USD)</label>
            <input type="text" id="formBudget" class="form-input" placeholder="e.g. #150,000 or $250" />
          </div>
        </div>

        <div class="form-group">
          <label for="formNotes">Special Requests or Questions</label>
          <textarea id="formNotes" class="form-textarea" rows="3"
            placeholder="Dietary needs, preferred pickup location in Lagos, group size queries, etc."></textarea>
        </div>

        <button type="submit" class="btn btn-primary w-100 modal-submit-btn">
          Confirm Reservation Request <i class="fa-solid fa-check"></i>
        </button>

        <div class="booking-confirmation-msg" id="bookingConfirmationMsg" style="display: none;">
          <i class="fa-solid fa-circle-check"></i>
          <div>
            <strong>Booking Request Received!</strong>
            <p>Our tour coordinator will reach out to you via WhatsApp &amp; Email within 30 minutes.</p>
          </div>
        </div>
      </form>
    </div>
  </div>

  <!-- Scroll To Top Progress Button -->
  <button class="scroll-top-btn" id="scrollTopBtn" aria-label="Scroll to top">
    <svg class="progress-ring" width="46" height="46">
      <circle class="progress-ring__circle" stroke="#238843" stroke-width="3" fill="transparent" r="20" cx="23"
        cy="23" />
    </svg>
    <i class="fa-solid fa-arrow-up"></i>
  </button>

  <script src="app.js"></script>
</body>

</html>
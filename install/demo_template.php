<?php
/**
 * Modern Business Template
 * 
 * Modernes, schlankes Business-Template mit CSS Custom Properties
 * Ohne Framework-Abhängigkeiten, barrierefrei und performant
 * 
 * DOMAIN_SETTINGS
 * tm_company_name: text|Firmenname|Muster GmbH|Offizieller Firmenname
 * tm_slogan: textarea|Slogan|Ihre Experten für...|Kurzer Werbe-Slogan (2-3 Zeilen)
 * tm_primary_color: colorselect|Akzentfarbe|#005d40:#005d40 Brand-Grün,#7D192C:#7D192C Brand-Rot,#b98b73:#b98b73 Braun,#c9a088:#c9a088 Beige,#d4ab94:#d4ab94 Sand,#1e87f0:#1e87f0 UIKit Blau,#32d296:#32d296 Grün,#faa05a:#faa05a Orange|Akzentfarbe für Design
 * tm_logo: media|Logo||Firmenlogo (Header)
 * tm_contact_email: email|Kontakt E-Mail|info@beispiel.de|Hauptkontakt E-Mail
 * tm_contact_phone: tel|Telefon|+49 123 456789|Kontakt-Telefonnummer
 * tm_opening_hours: opening_hours|Öffnungszeiten||Strukturierte Öffnungszeiten mit Pausen und Feiertagen
 * tm_footer_links: linklist|Footer-Links||Artikel-IDs für Footer-Navigation
 * tm_header_images: medialist|Header-Bilder||Bilder für Header-Slideshow
 * tm_start_article: link|Startseite||Link zur Startseite (für Logo-Klick)
 * tm_employee_count: number|Mitarbeiteranzahl|50|Anzahl der Mitarbeiter
 * tm_founded_year: number|Gründungsjahr|2000|Jahr der Firmengründung
 * tm_main_category: category|Hauptkategorie||Artikel-Kategorie für Hauptnavigation
 * tm_service_categories: categorylist|Service-Kategorien||Mehrere Kategorien für Services/Leistungen
 * tm_show_breadcrumbs: checkbox|Breadcrumbs anzeigen||Breadcrumb-Navigation aktivieren
 * tm_show_contact_info: checkbox|Kontaktinfo im Header||Telefon/E-Mail im Header anzeigen
 * tm_social_links: social_links|Social Media Links|fa|Verlinkte Social-Media-Profile (sortierbar, nur Font Awesome Icons)
 */

use FriendsOfRedaxo\TemplateManager\TemplateManager;
?>
<!DOCTYPE html>
<html lang="<?= rex_clang::getCurrent()->getCode() ?>">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= rex_escape($this->getValue('name')) ?> | <?= rex_escape(TemplateManager::get('tm_company_name', 'Muster GmbH')) ?></title>
    
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" integrity="sha512-DTOQO9RWCH3ppGqcWaEA1BIZOC6xxalwEsw9c2QQeAIftl+Vegovlnee1c9QX4TctnWMn13TZye+giMm8e2LwA==" crossorigin="anonymous" referrerpolicy="no-referrer" />
    
    <style>
        :root {
            --primary-color: <?= TemplateManager::get('tm_primary_color', '#005d40') ?>;
            --primary-dark: color-mix(in srgb, var(--primary-color) 80%, black);
            --primary-light: color-mix(in srgb, var(--primary-color) 90%, white);
            --max-width: 1200px;
            --border-radius: 8px;
            --transition: 0.3s ease;
        }
        
        /* Light Mode (Default) */
        @media (prefers-color-scheme: light) {
            :root {
                --text-color: #333;
                --bg-color: #fff;
                --nav-bg: #fff;
                --gray-100: #f8f9fa;
                --gray-200: #e9ecef;
                --gray-300: #dee2e6;
                --shadow: 0 2px 8px rgba(0,0,0,0.1);
            }
        }
        
        /* Dark Mode */
        @media (prefers-color-scheme: dark) {
            :root {
                --text-color: #e9ecef;
                --bg-color: #1a1d23;
                --nav-bg: #324050;
                --gray-100: #2d3139;
                --gray-200: #3a3f4b;
                --gray-300: #4a5162;
                --shadow: 0 2px 8px rgba(0,0,0,0.3);
            }
        }
        
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif;
            line-height: 1.6;
            color: var(--text-color);
            background: var(--bg-color);
        }
        
        /* Header */
        header {
            background: var(--nav-bg);
            color: var(--text-color);
            padding: 1rem 0;
            box-shadow: var(--shadow);
            border-bottom: 3px solid var(--primary-color);
            position: sticky;
            top: 0;
            z-index: 1000;
        }
        
        header .container {
            max-width: var(--max-width);
            margin: 0 auto;
            padding: 0 1rem;
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 1rem;
        }
        
        header h1 {
            font-size: 1.5rem;
            font-weight: 600;
            margin: 0;
        }
        
        /* Navigation */
        nav ul {
            list-style: none;
            display: flex;
            gap: 0.5rem;
            flex-wrap: wrap;
            margin: 0;
            padding: 0;
        }
        
        nav a {
            color: var(--text-color);
            text-decoration: none;
            transition: background var(--transition);
            font-weight: 500;
            padding: 0.5rem 1rem;
            border-radius: var(--border-radius);
            display: block;
        }
        
        nav a:hover,
        nav a:focus {
            background: var(--gray-200);
        }
        
        nav a[aria-current="page"] {
            background: var(--gray-200);
            border-bottom: 2px solid var(--primary-color);
        }
        
        /* Breadcrumbs */
        .breadcrumb {
            background: var(--gray-100);
            padding: 0.75rem 0;
            margin-bottom: 2rem;
        }
        
        .breadcrumb .container {
            max-width: var(--max-width);
            margin: 0 auto;
            padding: 0 1rem;
        }
        
        .breadcrumb ol {
            list-style: none;
            display: flex;
            gap: 0.5rem;
            flex-wrap: wrap;
        }
        
        .breadcrumb li:not(:last-child)::after {
            content: "›";
            margin-left: 0.5rem;
            color: var(--gray-700);
        }
        
        .breadcrumb a {
            color: var(--primary-color);
            text-decoration: none;
        }
        
        .breadcrumb a:hover {
            text-decoration: underline;
        }
        
        /* Main Content */
        main {
            min-height: 50vh;
        }
        
        .main-layout {
            max-width: var(--max-width);
            margin: 2rem auto;
            padding: 0 1rem;
        }
        
        /* Footer */
        footer {
            background: var(--gray-100);
            margin-top: 4rem;
            padding: 2rem 0;
            border-top: 1px solid var(--gray-200);
        }
        
        footer .container {
            max-width: var(--max-width);
            margin: 0 auto;
            padding: 0 1rem;
        }
        
        footer .footer-nav {
            display: flex;
            gap: 2rem;
            margin-bottom: 1.5rem;
            flex-wrap: wrap;
        }
        
        footer .footer-nav ul {
            list-style: none;
        }
        
        footer .footer-nav li {
            margin-bottom: 0.5rem;
        }
        
        footer .footer-nav a {
            color: var(--primary-color);
            text-decoration: none;
        }
        
        footer .footer-nav a:hover {
            text-decoration: underline;
        }
        
        footer .footer-bottom {
            padding-top: 1.5rem;
            border-top: 1px solid var(--gray-200);
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 1rem;
            font-size: 0.9rem;
            color: var(--gray-700);
        }
        
        footer a.email {
            color: var(--primary-color);
            text-decoration: none;
        }
        
        footer a.email:hover {
            text-decoration: underline;
        }
        
        /* Settings Info Box */
        .settings-info-box {
            padding: 1rem;
            border-radius: var(--border-radius);
            margin: 1rem 0;
            font-size: 0.9rem;
        }
        
        @media (prefers-color-scheme: light) {
            .settings-info-box {
                background: #fff;
                border: 1px solid var(--gray-200);
                color: var(--text-color);
            }
            .settings-info-box p {
                color: #333;
            }
        }
        
        @media (prefers-color-scheme: dark) {
            .settings-info-box {
                background: var(--gray-200);
                border: 1px solid var(--gray-300);
                color: var(--text-color);
            }
            .settings-info-box p {
                color: #e9ecef;
            }
        }
        
        .settings-info-box p {
            margin: 0.5rem 0;
        }
        
        .settings-info-box p:first-child {
            margin-top: 0;
        }
        
        .settings-info-box p:last-child {
            margin-bottom: 0;
        }
        
        .color-swatch {
            display: inline-block;
            width: 16px;
            height: 16px;
            border: 1px solid var(--gray-300);
            vertical-align: middle;
            border-radius: 3px;
            margin-right: 0.25rem;
        }
        
        /* Pulsierender Status-Badge */
        @keyframes pulse-open {
            0%, 100% { opacity: 1; }
            50% { opacity: 0.6; }
        }
        
        .status-badge {
            display: inline-flex;
            align-items: center;
            gap: 0.3rem;
            font-size: 0.8rem;
            font-weight: 500;
            margin-top: 0.3rem;
        }
        
        .status-badge.is-open {
            color: #28a745;
            animation: pulse-open 2s ease-in-out infinite;
        }
        
        .status-badge.is-closed {
            color: #dc3545;
        }
        
        .status-badge .dot {
            display: inline-block;
            width: 8px;
            height: 8px;
            border-radius: 50%;
            background: currentColor;
        }
        
        /* Responsive */
        @media (max-width: 768px) {
            header .container {
                flex-direction: column;
                align-items: flex-start;
            }
            
            nav ul {
                flex-direction: column;
                gap: 0.5rem;
            }
            
            footer .footer-bottom {
                flex-direction: column;
                align-items: flex-start;
            }
            
            /* Sidebar auf Mobil unter Content */
            .main-layout {
                grid-template-columns: 1fr !important;
            }
            
            .sidebar {
                position: static !important;
                order: -1; /* Sidebar oben auf mobil */
            }
        }
        
        @media (max-width: 1024px) and (min-width: 769px) {
            .main-layout {
                grid-template-columns: 1fr 260px !important;
                gap: 1.5rem !important;
            }
        }
    </style>
</head>
<body>

<!-- Header mit Navigation -->
<header>
    <div class="container">
        <?php 
        $logoMedia = TemplateManager::get('tm_logo');
        $startArticleId = TemplateManager::get('tm_start_article') ?: rex_article::getSiteStartArticleId();
        ?>
        
        <?php if ($logoMedia): ?>
            <a href="<?= rex_getUrl($startArticleId) ?>" style="display: block; max-width: 200px;">
                <img src="<?= rex_url::media($logoMedia) ?>" alt="<?= rex_escape(TemplateManager::get('tm_company_name', 'Logo')) ?>" style="max-width: 100%; height: auto;">
            </a>
        <?php else: ?>
            <h1><a href="<?= rex_getUrl($startArticleId) ?>" style="color: inherit; text-decoration: none;"><?= rex_escape(TemplateManager::get('tm_company_name', 'Muster GmbH')) ?></a></h1>
        <?php endif; ?>
        
        <?php if (TemplateManager::get('tm_show_contact_info')): ?>
        <div style="text-align: right; font-size: 0.9rem;">
            <?php if (TemplateManager::get('tm_contact_phone')): ?>
                <div style="margin-bottom: 0.25rem;">
                    <strong>Tel:</strong> <a href="tel:<?= rex_escape(TemplateManager::get('tm_contact_phone')) ?>" style="color: var(--primary-color); text-decoration: none;">
                        <?= rex_escape(TemplateManager::get('tm_contact_phone')) ?>
                    </a>
                </div>
            <?php endif; ?>
            <?php if (TemplateManager::get('tm_contact_email')): ?>
                <div>
                    <strong>E-Mail:</strong> <a href="mailto:<?= rex_escape(TemplateManager::get('tm_contact_email')) ?>" style="color: var(--primary-color); text-decoration: none;">
                        <?= rex_escape(TemplateManager::get('tm_contact_email')) ?>
                    </a>
                </div>
            <?php endif; ?>
        </div>
        <?php endif; ?>
        
        <nav aria-label="Hauptnavigation">
            <?php
            $nav = rex_navigation::factory();
            echo $nav->get(0, 2, true, true);
            ?>
        </nav>
    </div>
</header>

<!-- Breadcrumbs -->
<?php 
$showBreadcrumbs = TemplateManager::get('tm_show_breadcrumbs');
$currentArticle = rex_article::getCurrent();
$isStartArticle = $currentArticle && $currentArticle->isStartArticle();

if ($showBreadcrumbs && !$isStartArticle): 
?>
<div class="breadcrumb" aria-label="Breadcrumb">
    <div class="container">
        <ol>
            <li><a href="<?= rex_getUrl(rex_article::getSiteStartArticleId()) ?>">Home</a></li>
            <?php
            // Breadcrumb-Pfad
            $trail = [];
            $article = rex_article::getCurrent();
            
            // Alle Eltern-Artikel sammeln
            while ($article && $article->getId() !== rex_article::getSiteStartArticleId()) {
                array_unshift($trail, $article);
                $article = $article->getParent();
            }
            
            // Ausgabe
            foreach ($trail as $item) {
                if ($item->getId() === rex_article::getCurrentId()) {
                    echo '<li aria-current="page">' . rex_escape($item->getName()) . '</li>';
                } else {
                    echo '<li><a href="' . $item->getUrl() . '">' . rex_escape($item->getName()) . '</a></li>';
                }
            }
            ?>
        </ol>
    </div>
</div>
<?php endif; ?>

<!-- Main Content with Sidebar Layout -->
<?php 
// Öffnungszeiten für Sidebar vorbereiten
use FriendsOfRedaxo\TemplateManager\OpeningHoursHelper;

$openingHoursHelper = new OpeningHoursHelper(
    TemplateManager::get('tm_opening_hours'),
    rex_clang::getCurrent()->getCode()
);
$hasSidebar = $openingHoursHelper->hasData();
?>

<div class="main-layout" style="display: grid; grid-template-columns: <?= $hasSidebar ? '1fr 300px' : '1fr' ?>; gap: 2rem; align-items: start;">

<main id="content">
    <article>
        <?php 
        $articleContent = $this->getArticle();
        $hasContent = trim(strip_tags($articleContent)) !== '';
        
        // Header-Slideshow anzeigen
        $headerImages = TemplateManager::get('tm_header_images');
        
        if ($headerImages && trim($headerImages) !== ''): 
        ?>
        <div style="margin: -2rem -1rem 2rem; position: relative; background: var(--gray-100); padding: 3rem 1rem;">
            <?php
            $images = array_filter(array_map('trim', explode(',', $headerImages)));
            
            if (count($images) === 1):
                // Einzelnes Bild
                ?>
                <img src="<?= rex_url::media($images[0]) ?>" alt="Header" style="max-width: 100%; height: auto; border-radius: var(--border-radius);">
            <?php elseif (count($images) > 1): 
                // Mehrere Bilder als Grid
                ?>
                <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 1rem;">
                    <?php foreach ($images as $image): ?>
                        <img src="<?= rex_url::media($image) ?>" alt="Header Bild" style="width: 100%; height: 250px; object-fit: cover; border-radius: var(--border-radius);">
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
            
            <?php if (TemplateManager::get('tm_slogan')): ?>
            <div style="text-align: center; margin-top: 1.5rem;">
                <p style="font-size: 1.5rem; color: var(--primary-color); font-weight: 600; margin: 0; white-space: pre-line;">
                    <?= nl2br(rex_escape(TemplateManager::get('tm_slogan'))) ?>
                </p>
            </div>
            <?php endif; ?>
        </div>
        <?php endif; ?>
        
        <?php if ($hasContent): ?>
            <?= $articleContent ?>
        <?php else: ?>
            <!-- Demo Content wenn noch kein Content vorhanden -->
            <div style="background: var(--gray-100); padding: 2rem; border-radius: var(--border-radius); margin-bottom: 2rem;">
                <h2 style="margin-top: 0; color: var(--primary-color);">
                    <i style="font-style: normal;">⚠️</i> Demo-Template
                </h2>
                <p><strong>Dies ist ein Demo-Template des Template Managers.</strong></p>
                <p>Dieses Template dient als <strong>Ausgangspunkt</strong> für eigene Projekte und zeigt die Funktionsweise der Template Manager Settings. Es kann als Basis verwendet werden, allerdings steht noch einige Arbeit an, um es an die individuellen Anforderungen Ihres Projekts anzupassen.</p>
                
                <h3>Features:</h3>
                <ul>
                    <li>✅ Dark/Light Mode Unterstützung (automatisch basierend auf System-Einstellungen)</li>
                    <li>✅ 16+ konfigurierbare Einstellungen über Template Manager</li>
                    <li>✅ Alle Feldtypen demonstriert (text, textarea, number, email, tel, media, medialist, link, linklist, category, categorylist, colorselect, checkbox)</li>
                    <li>✅ Modernes, responsives Design ohne Framework</li>
                    <li>✅ CSS Custom Properties für einfaches Theming</li>
                    <li>✅ Barrierefrei (WCAG 2.1 AA)</li>
                </ul>
                
                <h3>Konfigurierte Einstellungen:</h3>
                <div class="settings-info-box">
                    <p><strong>Firmenname:</strong> <?= rex_escape(TemplateManager::get('tm_company_name', 'Nicht konfiguriert')) ?></p>
                    <?php if (TemplateManager::get('tm_slogan')): ?>
                    <p><strong>Slogan:</strong> <?= rex_escape(TemplateManager::get('tm_slogan')) ?></p>
                    <?php endif; ?>
                    <p><strong>Primärfarbe:</strong> <span class="color-swatch" style="background:<?= TemplateManager::get('tm_primary_color', '#005d40') ?>"></span> <?= TemplateManager::get('tm_primary_color', '#005d40') ?></p>
                    <?php if (TemplateManager::get('tm_employee_count')): ?>
                    <p><strong>Mitarbeiter:</strong> <?= (int)TemplateManager::get('tm_employee_count') ?></p>
                    <?php endif; ?>
                    <?php if (TemplateManager::get('tm_founded_year')): ?>
                    <p><strong>Gegründet:</strong> <?= (int)TemplateManager::get('tm_founded_year') ?></p>
                    <?php endif; ?>
                    <?php 
                    $openingHoursJson = TemplateManager::get('tm_opening_hours');
                    if ($openingHoursJson): 
                        $openingHours = json_decode($openingHoursJson, true);
                        if ($openingHours && isset($openingHours['regular'])):
                            $weekdays = [
                                'monday' => 'Mo', 'tuesday' => 'Di', 'wednesday' => 'Mi', 
                                'thursday' => 'Do', 'friday' => 'Fr', 'saturday' => 'Sa', 'sunday' => 'So'
                            ];
                    ?>
                    <p><strong>Öffnungszeiten:</strong></p>
                    <ul style="margin: 0.5rem 0; list-style: none; padding: 0;">
                    <?php foreach ($openingHours['regular'] as $day => $data): 
                        $label = $weekdays[$day] ?? $day;
                        $status = $data['status'] ?? 'closed';
                    ?>
                        <li style="display: flex; gap: 0.5rem;">
                            <span style="width: 30px; font-weight: 600;"><?= $label ?>:</span>
                            <?php if ($status === 'closed'): ?>
                                <span style="color: var(--gray-400, #999);">Geschlossen</span>
                            <?php elseif ($status === '24h'): ?>
                                <span style="color: var(--primary-color);">24h geöffnet</span>
                            <?php else: 
                                $times = array_map(fn($t) => $t['open'] . '–' . $t['close'], $data['times'] ?? []);
                            ?>
                                <span><?= implode(', ', $times) ?> Uhr</span>
                            <?php endif; ?>
                        </li>
                    <?php endforeach; ?>
                    </ul>
                    <?php 
                        endif;
                    endif; 
                    ?>
                    <?php 
                    $mainCategory = TemplateManager::get('tm_main_category');
                    if ($mainCategory && is_numeric($mainCategory)):
                        $category = rex_category::get((int)$mainCategory);
                        if ($category):
                    ?>
                    <p><strong>Hauptkategorie:</strong> <a href="<?= $category->getUrl() ?>"><?= rex_escape($category->getName()) ?></a></p>
                    <?php 
                        endif;
                    endif; 
                    ?>
                    <?php 
                    $serviceCategories = TemplateManager::get('tm_service_categories');
                    if ($serviceCategories):
                        $categoryIds = array_filter(array_map('intval', explode(',', $serviceCategories)));
                        if (!empty($categoryIds)):
                    ?>
                    <p><strong>Service-Kategorien:</strong></p>
                    <ul style="margin: 0.5rem 0;">
                    <?php foreach ($categoryIds as $catId):
                        $category = rex_category::get($catId);
                        if ($category):
                    ?>
                        <li><a href="<?= $category->getUrl() ?>"><?= rex_escape($category->getName()) ?></a></li>
                    <?php 
                        endif;
                    endforeach; ?>
                    </ul>
                    <?php 
                        endif;
                    endif; 
                    ?>
                </div>
                
                <h3>Nächste Schritte:</h3>
                <ol>
                    <li>Konfigurieren Sie die Template-Einstellungen über <strong>Template Manager → Konfigurieren</strong></li>
                    <li>Fügen Sie Inhalte über das REDAXO Backend hinzu</li>
                    <li>Passen Sie die Farben und Texte an Ihre Marke an</li>
                    <li>Erweitern Sie das Template nach Ihren Bedürfnissen</li>
                </ol>
                
                <p style="margin-top: 2rem; padding-top: 1rem; border-top: 1px solid var(--gray-300); font-size: 0.9rem; color: var(--gray-300);">
                    Sobald Sie eigenen Content hinzufügen, wird dieser Demo-Text automatisch ersetzt.
                </p>
            </div>
        <?php endif; ?>
    </article>
</main>

<?php if ($hasSidebar): 
    $groupedHours = $openingHoursHelper->getRegularGrouped();
    $specialHours = $openingHoursHelper->getSpecial(10, true); // Max 10 Sonderzeiten anzeigen
    $currentStatus = $openingHoursHelper->getCurrentStatus();
?>
<!-- Sidebar with Opening Hours -->
<aside class="sidebar" style="position: sticky; top: 1rem;">
    <div class="opening-hours-card" style="padding: 1rem; background: var(--bg-color); border-radius: var(--border-radius); box-shadow: var(--shadow); border: 1px solid var(--gray-200);">
        <!-- Header -->
        <div style="margin-bottom: 0.75rem; padding-bottom: 0.5rem; border-bottom: 2px solid var(--primary-color);">
            <span style="font-weight: 700; font-size: 0.95rem; display: flex; align-items: center; gap: 0.4rem;">
                <i class="fa-solid fa-clock" style="color: var(--primary-color);"></i> 
                <?= rex_escape($openingHoursHelper->translate('labels.opening_hours')) ?>
            </span>
            <span class="status-badge <?= $currentStatus['is_open'] ? 'is-open' : 'is-closed' ?>">
                <span class="dot"></span>
                <?= $currentStatus['is_open'] ? 'Aktuell geöffnet' : 'Aktuell geschlossen' ?>
            </span>
        </div>
        
        <!-- Gruppierte Öffnungszeiten -->
        <div style="font-size: 0.85rem;">
            <?php foreach ($groupedHours as $group): ?>
            <div style="display: flex; justify-content: space-between; align-items: baseline; padding: 0.35rem 0.4rem; margin: 0 -0.4rem; border-radius: 3px; <?= $group['contains_today'] ? 'background: var(--primary-color); color: #fff;' : '' ?><?= !$group['contains_today'] ? 'border-bottom: 1px solid var(--gray-100);' : '' ?>">
                <span style="font-weight: <?= $group['contains_today'] ? '700' : '500' ?>;">
                    <?= rex_escape($group['label']) ?>
                </span>
                <span style="text-align: right; font-size: 0.8rem; <?= $group['is_closed'] ? 'color: ' . ($group['contains_today'] ? 'rgba(255,255,255,0.8)' : 'var(--gray-400, #999)') . '; font-style: italic;' : '' ?>">
                    <?php if ($group['is_24h']): ?>
                        <strong>24h</strong>
                    <?php elseif ($group['is_closed']): ?>
                        <?= rex_escape($group['status_label']) ?>
                    <?php else: ?>
                        <?= rex_escape($group['times_formatted']) ?>
                    <?php endif; ?>
                </span>
            </div>
            <?php endforeach; ?>
        </div>
        
        <?php if (!empty($specialHours)): ?>
        <!-- Sonderzeiten -->
        <div style="margin-top: 0.75rem; padding-top: 0.5rem; border-top: 1px dashed var(--gray-300); font-size: 0.8rem;">
            <p style="margin: 0 0 0.3rem; font-weight: 600; font-size: 0.7rem; color: var(--primary-color); text-transform: uppercase; letter-spacing: 0.3px;">
                <i class="fa-solid fa-calendar-day"></i> Sonderzeiten
            </p>
            <?php foreach ($specialHours as $special): ?>
            <div style="display: flex; justify-content: space-between; padding: 0.2rem 0; gap: 0.3rem;">
                <span style="overflow: hidden; text-overflow: ellipsis; white-space: nowrap;" title="<?= rex_escape($special['date_formatted']) ?>">
                    <?= rex_escape($special['display_name']) ?>
                    <?php if (!empty($special['date_formatted'])): ?>
                    <small style="color: var(--gray-400);">(<?= rex_escape($special['date_formatted']) ?>)</small>
                    <?php endif; ?>
                </span>
                <span style="white-space: nowrap; <?= $special['is_closed'] ? 'color: var(--gray-400); font-style: italic;' : '' ?>">
                    <?= rex_escape($special['formatted']) ?>
                </span>
            </div>
            <?php endforeach; ?>
        </div>
        <?php endif; ?>
        
        <?php if ($openingHoursHelper->hasNote()): ?>
        <div style="margin-top: 0.75rem; padding: 0.5rem; background: var(--gray-100); border-radius: 3px; font-size: 0.8rem; color: var(--text-color); font-style: italic;">
            <i class="fa-solid fa-info-circle" style="color: var(--primary-color); margin-right: 0.3rem;"></i>
            <?= rex_escape($openingHoursHelper->getNote()) ?>
        </div>
        <?php endif; ?>
        
        <?php if ($currentStatus['next_change_label']): ?>
        <div style="margin-top: 0.5rem; padding: 0.3rem; background: var(--gray-100); border-radius: 3px; text-align: center; font-size: 0.7rem; color: var(--gray-400);">
            → <?= rex_escape($currentStatus['next_change_label']) ?>
        </div>
        <?php endif; ?>
    </div>
    
    <!-- Kontakt-Schnellinfo in Sidebar -->
    <?php if (TemplateManager::get('tm_phone') || TemplateManager::get('tm_email')): ?>
    <div class="contact-card" style="margin-top: 1rem; padding: 1rem; background: var(--bg-color); border-radius: var(--border-radius); box-shadow: var(--shadow); border: 1px solid var(--gray-200);">
        <span style="font-weight: 700; font-size: 0.95rem; display: flex; align-items: center; gap: 0.4rem; margin-bottom: 0.75rem; padding-bottom: 0.5rem; border-bottom: 2px solid var(--primary-color);">
            <i class="fa-solid fa-address-book" style="color: var(--primary-color);"></i> 
            Kontakt
        </span>
        <?php if (TemplateManager::get('tm_phone')): ?>
        <a href="tel:<?= rex_escape(preg_replace('/[^0-9+]/', '', TemplateManager::get('tm_phone'))) ?>" 
           style="display: flex; align-items: center; gap: 0.5rem; margin-bottom: 0.5rem; color: var(--text-color); text-decoration: none; font-size: 0.85rem;">
            <i class="fa-solid fa-phone" style="color: var(--primary-color);"></i>
            <?= rex_escape(TemplateManager::get('tm_phone')) ?>
        </a>
        <?php endif; ?>
        <?php if (TemplateManager::get('tm_email')): ?>
        <a href="mailto:<?= rex_escape(TemplateManager::get('tm_email')) ?>" 
           style="display: flex; align-items: center; gap: 0.5rem; color: var(--text-color); text-decoration: none; font-size: 0.85rem;">
            <i class="fa-solid fa-envelope" style="color: var(--primary-color);"></i>
            <?= rex_escape(TemplateManager::get('tm_email')) ?>
        </a>
        <?php endif; ?>
    </div>
    <?php endif; ?>
</aside>
<?php endif; ?>

</div><!-- /.main-layout -->

<!-- Footer -->
<footer>
    <div class="container">
        
        <?php 
        // Social Links ausgeben
        $socialLinksJson = TemplateManager::get('tm_social_links');
        $socialLinks = $socialLinksJson ? json_decode($socialLinksJson, true) : [];
        
        if (!empty($socialLinks)): 
        ?>
        <div class="social-links" style="margin-bottom: 1.5rem; text-align: center;">
            <?php foreach ($socialLinks as $link): 
                if (empty($link['url'])) continue;
                $icon = $link['icon'] ?? '';
                $url = $link['url'];
                $label = $link['label'] ?? '';
                
                // Icon-Klasse ermitteln
                $iconHtml = '';
                if (str_starts_with($icon, 'uk-icon-')) {
                    $ukIcon = str_replace('uk-icon-', '', $icon);
                    $iconHtml = '<span uk-icon="icon: ' . rex_escape($ukIcon) . '; ratio: 1.2"></span>';
                } elseif ($icon) {
                    // Font Awesome: fa-brand wird zu fa-brands fa-brand
                    $faClass = $icon;
                    // Brands-Icons erkennen und korrekte Klasse setzen
                    $brandIcons = ['fa-facebook', 'fa-facebook-f', 'fa-twitter', 'fa-x-twitter', 'fa-instagram', 'fa-linkedin', 'fa-linkedin-in', 'fa-xing', 'fa-youtube', 'fa-tiktok', 'fa-pinterest', 'fa-whatsapp', 'fa-telegram', 'fa-github', 'fa-gitlab', 'fa-discord', 'fa-slack', 'fa-mastodon', 'fa-threads', 'fa-bluesky', 'fa-reddit', 'fa-snapchat', 'fa-vimeo', 'fa-dribbble', 'fa-behance', 'fa-flickr', 'fa-spotify', 'fa-soundcloud', 'fa-twitch'];
                    if (in_array($icon, $brandIcons, true)) {
                        $faClass = 'fa-brands ' . $icon;
                    } else {
                        $faClass = 'fa-solid ' . $icon;
                    }
                    $iconHtml = '<i class="' . rex_escape($faClass) . '"></i>';
                }
            ?>
            <a href="<?= rex_escape($url) ?>" 
               target="_blank" 
               rel="noopener noreferrer" 
               class="social-link"
               <?= $label ? 'title="' . rex_escape($label) . '"' : '' ?>
               style="display: inline-flex; align-items: center; justify-content: center; width: 40px; height: 40px; margin: 0 5px; border-radius: 50%; background: var(--gray-200); color: var(--text-color); text-decoration: none; transition: background 0.3s, transform 0.3s;"
               onmouseover="this.style.background='var(--primary-color)'; this.style.color='#fff'; this.style.transform='translateY(-3px)';"
               onmouseout="this.style.background='var(--gray-200)'; this.style.color='var(--text-color)'; this.style.transform='translateY(0)';">
                <?= $iconHtml ?>
            </a>
            <?php endforeach; ?>
        </div>
        <?php endif; ?>
        
        <?php 
        $footerLinks = TemplateManager::get('tm_footer_links');
        
        if ($footerLinks && trim($footerLinks) !== ''): 
        ?>
        <nav class="footer-nav" aria-label="Footer-Navigation">
            <ul>
                <?php
                $links = explode(',', $footerLinks);
                foreach ($links as $articleId) {
                    $articleId = (int) trim($articleId);
                    if ($articleId > 0) {
                        $article = rex_article::get($articleId);
                        if ($article) {
                            echo '<li><a href="' . $article->getUrl() . '">' . 
                                 rex_escape($article->getName()) . '</a></li>';
                        }
                    }
                }
                ?>
            </ul>
        </nav>
        <?php endif; ?>
        
        <div class="footer-bottom">
            <div>
                &copy; <?= date('Y') ?> <?= rex_escape(TemplateManager::get('tm_company_name', 'Muster GmbH')) ?>
                <?php if (TemplateManager::get('tm_founded_year')): ?>
                    | Seit <?= (int)TemplateManager::get('tm_founded_year') ?>
                <?php endif; ?>
                . Alle Rechte vorbehalten.
            </div>
            
            <div>
                <?php if (TemplateManager::get('tm_contact_email')): ?>
                <a href="mailto:<?= rex_escape(TemplateManager::get('tm_contact_email')) ?>" class="email">
                    <?= rex_escape(TemplateManager::get('tm_contact_email')) ?>
                </a>
                <?php endif; ?>
                
                <?php if (TemplateManager::get('tm_contact_phone') && TemplateManager::get('tm_contact_email')): ?>
                <span style="margin: 0 0.5rem;">|</span>
                <?php endif; ?>
                
                <?php if (TemplateManager::get('tm_contact_phone')): ?>
                <a href="tel:<?= rex_escape(TemplateManager::get('tm_contact_phone')) ?>" style="color: var(--primary-color); text-decoration: none;">
                    <?= rex_escape(TemplateManager::get('tm_contact_phone')) ?>
                </a>
                <?php endif; ?>
            </div>
        </div>
    </div>
</footer>

<?php
// Inline JavaScript mit REDAXO Nonce für CSP
$nonce = rex_response::getNonce();
?>
<script nonce="<?= $nonce ?>">
// Beispiel: Smooth Scrolling für Anker-Links
document.addEventListener('DOMContentLoaded', function() {
    document.querySelectorAll('a[href^="#"]').forEach(anchor => {
        anchor.addEventListener('click', function(e) {
            const href = this.getAttribute('href');
            if (href === '#') return;
            
            const target = document.querySelector(href);
            if (target) {
                e.preventDefault();
                target.scrollIntoView({
                    behavior: 'smooth',
                    block: 'start'
                });
            }
        });
    });
    
    // Aktuelle Seite in Navigation markieren
    const currentUrl = window.location.pathname;
    document.querySelectorAll('nav a').forEach(link => {
        if (link.getAttribute('href') === currentUrl) {
            link.setAttribute('aria-current', 'page');
        }
    });
});
</script>

</body>
</html>

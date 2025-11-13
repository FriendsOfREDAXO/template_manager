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
 * tm_opening_hours: text|Öffnungszeiten|Mo-Fr 9-18 Uhr|Öffnungszeiten Kurzform
 * tm_footer_links: linklist|Footer-Links||Artikel-IDs für Footer-Navigation
 * tm_header_images: medialist|Header-Bilder||Bilder für Header-Slideshow
 * tm_start_article: link|Startseite||Link zur Startseite (für Logo-Klick)
 * tm_employee_count: number|Mitarbeiteranzahl|50|Anzahl der Mitarbeiter
 * tm_founded_year: number|Gründungsjahr|2000|Jahr der Firmengründung
 * tm_main_category: sqlselect|Hauptkategorie|SELECT id, name FROM rex_article WHERE parent_id = 0 AND startarticle = 1 ORDER BY name|Artikel-Kategorie für Hauptnavigation
 * tm_show_breadcrumbs: checkbox|Breadcrumbs anzeigen||Breadcrumb-Navigation aktivieren
 * tm_show_contact_info: checkbox|Kontaktinfo im Header||Telefon/E-Mail im Header anzeigen
 */

use FriendsOfRedaxo\TemplateManager\TemplateManager;
?>
<!DOCTYPE html>
<html lang="<?= rex_clang::getCurrent()->getCode() ?>">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= rex_escape($this->getValue('name')) ?> | <?= rex_escape(TemplateManager::get('tm_company_name', 'Muster GmbH')) ?></title>
    
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
            max-width: var(--max-width);
            margin: 2rem auto;
            padding: 0 1rem;
            min-height: 50vh;
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

<!-- Main Content -->
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
                <p>Dieses Template dient nur zu Demonstrationszwecken und ist nicht für den produktiven Einsatz gedacht. Es zeigt die Funktionsweise der Template Manager Settings.</p>
                
                <h3>Features:</h3>
                <ul>
                    <li>✅ Dark/Light Mode Unterstützung (automatisch basierend auf System-Einstellungen)</li>
                    <li>✅ 15+ konfigurierbare Einstellungen über Template Manager</li>
                    <li>✅ Alle Feldtypen demonstriert (text, textarea, number, email, tel, media, medialist, link, linklist, colorselect, sqlselect, checkbox)</li>
                    <li>✅ Modernes, responsives Design ohne Framework</li>
                    <li>✅ CSS Custom Properties für einfaches Theming</li>
                    <li>✅ Barrierefrei (WCAG 2.1 AA)</li>
                </ul>
                
                <h3>Konfigurierte Einstellungen:</h3>
                <div style="background: white; padding: 1rem; border-radius: var(--border-radius); margin: 1rem 0; font-size: 0.9rem;">
                    <p><strong>Firmenname:</strong> <?= rex_escape(TemplateManager::get('tm_company_name', 'Nicht konfiguriert')) ?></p>
                    <?php if (TemplateManager::get('tm_slogan')): ?>
                    <p><strong>Slogan:</strong> <?= rex_escape(TemplateManager::get('tm_slogan')) ?></p>
                    <?php endif; ?>
                    <p><strong>Primärfarbe:</strong> <span style="display:inline-block;width:16px;height:16px;background:<?= TemplateManager::get('tm_primary_color', '#005d40') ?>;border:1px solid #ccc;vertical-align:middle;border-radius:3px;"></span> <?= TemplateManager::get('tm_primary_color', '#005d40') ?></p>
                    <?php if (TemplateManager::get('tm_employee_count')): ?>
                    <p><strong>Mitarbeiter:</strong> <?= (int)TemplateManager::get('tm_employee_count') ?></p>
                    <?php endif; ?>
                    <?php if (TemplateManager::get('tm_founded_year')): ?>
                    <p><strong>Gegründet:</strong> <?= (int)TemplateManager::get('tm_founded_year') ?></p>
                    <?php endif; ?>
                    <?php if (TemplateManager::get('tm_opening_hours')): ?>
                    <p><strong>Öffnungszeiten:</strong> <?= rex_escape(TemplateManager::get('tm_opening_hours')) ?></p>
                    <?php endif; ?>
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

<!-- Footer -->
<footer>
    <div class="container">
        
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

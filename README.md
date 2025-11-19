# Template Manager

Ein REDAXO-Addon zur Verwaltung von domain- und sprachspezifischen Template-Einstellungen über DocBlock-Konfiguration.

## Features

- 📝 **DocBlock-basierte Konfiguration** - Template-Settings direkt im Template-Code definieren
- 🌍 **Multi-Domain Support** - Unterschiedliche Einstellungen pro YRewrite-Domain
- 🌐 **Mehrsprachigkeit** - Separate Einstellungen für jede Sprache mit Fallback
- 🎨 **20+ Feldtypen** - text, textarea, cke5, number, email, tel, date, time, color, colorselect, media, medialist, select, checkbox, link, linklist u.v.m.
- 🔧 **Native REDAXO Widgets** - Volle Integration von Linkmap, Medienpicker und Bootstrap Selectpicker
- 🎨 **Visuelle Farbauswahl** - Colorselect mit farbigen Badges
- 🚀 **Einfache Frontend-API** - Statische Klassen-Methoden mit optionalen Domain/Sprach-Parametern
- 🔌 **Erweiterbar** - Extension Point System für eigene Feldtypen durch externe Addons

## Erweiterbarkeit für externe Addons

Ab Version 1.x können externe Addons eigene Feldtypen registrieren:

```php
// In boot.php des externen Addons
rex_extension::register('TEMPLATE_MANAGER_FIELD_RENDERERS', function($ep) {
    $renderers = $ep->getSubject();
    $renderers[] = new \MeinAddon\TemplateManagerFieldRenderer();
    return $renderers;
});
```

Siehe [EXTERNAL_FIELD_RENDERER_EXAMPLE.md](EXTERNAL_FIELD_RENDERER_EXAMPLE.md) für vollständige Beispiele.

## Installation

1. Addon im REDAXO-Backend installieren
2. Optional: Demo-Template über **Template Manager** → **Setup** importieren
3. Eigene Templates mit DOMAIN_SETTINGS erstellen

## Demo-Template

Das Addon enthält ein vorkonfiguriertes Demo-Template:

- **Name:** Modern Business (Demo)
- **Features:**
  - Dark/Light Mode Support (automatisch basierend auf System-Einstellungen)
  - 8 essenzielle Einstellungen (inkl. colorselect, medialist, uikit_theme_select)
  - Modernes, responsives Design ohne Framework-Abhängigkeiten
  - CSS Custom Properties für einfaches Theming
  - Barrierefrei (WCAG 2.1 AA)
  - Demo-Content wenn noch kein Content vorhanden

**Enthaltene Feldtypen:**
- `text` - Firmenname
- `colorselect` - Akzentfarbe (8 vordefinierte Brand-Farben)
- `email` - Kontakt E-Mail
- `tel` - Telefonnummer
- `linklist` - Footer-Links
- `medialist` - Header-Bilder
- `checkbox` - Breadcrumbs anzeigen

Import über: **Template Manager** → **Setup** → **Demo-Template jetzt importieren**

## Template-Konfiguration

### DocBlock-Format

Füge einen PHP-DocBlock-Kommentar am Anfang deines Templates ein mit einem `DOMAIN_SETTINGS` Abschnitt:

```php
<?php
/**
 * Mein Template
 * 
 * Beschreibung des Templates
 * 
 * DOMAIN_SETTINGS
 * tm_logo: media|Logo||Firmenlogo
 * tm_company_name: text|Firmenname|Muster GmbH|Offizieller Firmenname
 * tm_primary_color: colorselect|Akzentfarbe|#005d40:#005d40 Grün,#7D192C:#7D192C Rot,#1e87f0:#1e87f0 Blau|Hauptfarbe
 * tm_contact_email: email|E-Mail|info@beispiel.de|Kontakt E-Mail-Adresse
 * tm_contact_phone: tel|Telefon|+49 123 456789|Kontakt-Telefonnummer
 * tm_footer_links: linklist|Footer-Links||Artikel-IDs für Footer-Navigation
 * tm_header_images: medialist|Header-Bilder||Bilder für Header-Slideshow
 * tm_show_breadcrumbs: checkbox|Breadcrumbs anzeigen||Breadcrumb-Navigation aktivieren
 */
?>
<!DOCTYPE html>
<html>
<!-- Template-Code hier -->
</html>
```

**Wichtig:** 
- Der DocBlock muss in `<?php ... ?>` eingebettet sein, damit er nicht als HTML ausgegeben wird
- Alle Feldnamen müssen mit `tm_` beginnen (Template Manager Prefix)
- Nur Templates mit `tm_` Prefix in den Settings werden als konfigurierbar erkannt

### Feldtyp-Syntax

Jede Setting-Zeile folgt diesem Format:

```
tm_feldname: typ|Label|DefaultWert|Beschreibung
```

**Wichtig:** Alle Feldnamen müssen mit `tm_` beginnen (Template Manager Prefix)!

### Verfügbare Feldtypen

| Typ | Beschreibung | Beispiel Default |
|-----|--------------|------------------|
| **Text-Felder** |
| `text` | Einzeiliges Textfeld | `Beispieltext` |
| `textarea` | Mehrzeiliges Textfeld | `Längerer Text` |
| `cke5` | WYSIWYG Editor (CKE5) | `<p>HTML Content</p>` |
| `email` | E-Mail-Adresse mit Validierung | `info@beispiel.de` |
| `url` | URL/Link (extern) | `https://beispiel.de` |
| `tel` | Telefonnummer | `+49 123 456789` |
| **Numerische Felder** |
| `number` | Zahleneingabe (inkl. Dezimal) | `42` oder `3.14` |
| **Datum/Zeit** |
| `date` | Datum (YYYY-MM-DD) | `2024-01-15` |
| `datetime-local` | Datum + Zeit | `2024-01-15T10:30` |
| `time` | Uhrzeit | `14:30` |
| **Farben** |
| `color` | HTML5 Color Picker | `#005d40` |
| `colorselect` | Vordefinierte Farben (Selectpicker mit Badges) | `#005d40:Brand-Grün,#7D192C:Brand-Rot` |
| **Medien** |
| `media` | Einzelne Mediendatei (natives Widget) | `logo.png` |
| `medialist` | Liste von Mediendateien (natives Widget) | `bild1.jpg,bild2.jpg` |
| **Auswahl** |
| `select` | Dropdown-Auswahl | `wert1:Label 1,wert2:Label 2` |
| `checkbox` | Ja/Nein Checkbox | `` (leer = nicht aktiviert) |
| **Links** |
| `link` | Interner REDAXO-Link (natives Widget) | `5` (Artikel-ID) |
| `linklist` | Liste interner Links (natives Widget) | `1,5,8` (Artikel-IDs) |
| `external_linklist` | Externe Link-Liste mit Live-Vorschau | `Name\|URL\|Beschreibung` (ein Link pro Zeile) |
| **Struktur** |
| `category` | Kategorie-Auswahl (hierarchische Struktur) | `5` (Kategorie-ID) |
| `categorylist` | Mehrere Kategorien auswählen | `1,5,8` (Kategorie-IDs) |

### External Linklist - Externe Links mit Repeater-Style

Der Feldtyp `external_linklist` ermöglicht die Verwaltung mehrerer externer Links mit Live-Vorschau im Backend:

**Format:** Ein Link pro Zeile im Format `Name|URL|Beschreibung` (Beschreibung optional)

**Beispiel:**
```
tm_footer_partners: external_linklist|Partner-Links||Externe Partner verlinken
```

**Backend-Features:**
- ✅ **Live-Vorschau** mit Validierung (zeigt Fehler sofort an)
- ✅ **URL-Validierung** (muss mit http:// oder https:// beginnen)
- ✅ **Format-Hilfe** direkt im Feld
- ✅ **Kommentare** möglich (Zeilen mit # oder // am Anfang)
- ✅ **Monospace-Font** für bessere Lesbarkeit

**Eingabe-Beispiel:**
```
WDFV|https://wdfv.de|Westdeutscher Fußballverband
FVN|https://fvn.de|Fußballverband Niederrhein
FVM|https://fvm.de|Fußballverband Mittelrhein
# Kommentare sind möglich
FLVW|https://flvw.de|Fußball- und Leichtathletik-Verband Westfalen
```

**Frontend-Nutzung:**
```php
<?php
use FriendsOfRedaxo\TemplateManager\TemplateManager;
use FriendsOfRedaxo\TemplateManager\ExternalLinklistWidget;

// Variante 1: Direkt als HTML rendern
echo '<ul>';
echo ExternalLinklistWidget::renderHtml(
    TemplateManager::get('tm_footer_partners'), 
    true  // true = Links in neuem Tab öffnen
);
echo '</ul>';

// Variante 2: Als Array parsen für individuelle Verarbeitung
$links = ExternalLinklistWidget::parse(
    TemplateManager::get('tm_footer_partners')
);

foreach ($links as $link) {
    echo '<div class="partner-card">';
    echo '<h3>' . rex_escape($link['name']) . '</h3>';
    echo '<p>' . rex_escape($link['description']) . '</p>';
    echo '<a href="' . rex_escape($link['url']) . '" target="_blank">';
    echo 'Zur Website <i class="icon-external"></i>';
    echo '</a>';
    echo '</div>';
}
?>
```

**Rückgabe-Format der parse()-Methode:**
```php
[
    [
        'name' => 'WDFV',
        'url' => 'https://wdfv.de',
        'description' => 'Westdeutscher Fußballverband'
    ],
    // ...
]
```

**Typische Verwendung:**
- Footer-Links zu Verbänden/Partnern
- Social Media Links
- Externe Ressourcen
- Sponsor-Listen
- Tool/Service-Verzeichnisse

### Select-Optionen & Colorselect

Bei `select` und `colorselect` Feldern werden die Optionen im Default-Wert definiert:

**Select:**
```
tm_header_style: select|Header-Stil|standard|standard:Standard,modern:Modern,minimal:Minimal|Auswahl des Header-Designs
```

**Colorselect (mit visuellen Farb-Badges):**
```
tm_primary_color: colorselect|Akzentfarbe|#005d40:#005d40 Brand-Grün,#7D192C:#7D192C Brand-Rot,#1e87f0:#1e87f0 Blau|Hauptfarbe
```

Format: `wert:Label,wert2:Label2` oder einfach `wert1,wert2,wert3`

**Hinweis:** `colorselect` zeigt farbige Badges im Bootstrap Selectpicker an - ideal für vordefinierte Farbpaletten!

### CKE5 WYSIWYG Editor

Der Feldtyp `cke5` bietet einen vollwertigen WYSIWYG-Editor:

**Beispiel mit Default-Profil:**
```
tm_welcome_text: cke5|Willkommenstext||Editor-Inhalt mit HTML-Formatierung
```

**Mit eigenem Profil (Profil im Default-Wert angeben):**
```
tm_footer_text: cke5|Footer-Text|simple|Editor mit 'simple' Profil
tm_description: cke5|Beschreibung|full|Editor mit 'full' Profil
```

**Features:**
- Profil-Angabe im Default-Wert (leer = 'default')
- Automatische Sprach-Erkennung (User + Content)
- Fallback zu Textarea wenn CKE5 nicht verfügbar
- Unterstützt alle CKE5-Profile aus dem Backend

**Frontend-Ausgabe:**
```php
<!-- Direktausgabe (HTML ist bereits formatiert) -->
<?= TemplateManager::get('tm_welcome_text') ?>

<!-- Mit Fallback -->
<?= TemplateManager::get('tm_welcome_text', '<p>Standard-Text</p>') ?>
```

**Wichtig:** CKE5-Inhalte sind bereits HTML-formatiert und sollten **nicht** mit `rex_escape()` ausgegeben werden!

### Category Select

Der Feldtyp `category` bietet eine hierarchische Kategorie-Auswahl mit korrekter Struktur-Darstellung:

**Beispiel:**
```
tm_news_category: category|News-Kategorie|5|Kategorie für News-Artikel
tm_main_category: category|Hauptkategorie||Root-Kategorie auswählen
```

**Features:**
- Hierarchische Darstellung mit Einrückung
- Kategorie-IDs werden angezeigt: "Name [ID]"
- Berechtigungs-Prüfung (nur Kategorien mit Zugriff)
- "Homepage" Option für Root-Level (ID: 0)
- Berücksichtigt aktuelle Sprache
- Bootstrap Selectpicker mit Live-Search

**Frontend-Nutzung:**
```php
<?php
use FriendsOfRedaxo\TemplateManager\TemplateManager;

// Kategorie-ID abrufen
$categoryId = TemplateManager::get('tm_news_category');

if ($categoryId) {
    // Kategorie-Objekt laden
    $category = rex_category::get($categoryId);
    
    if ($category) {
        echo '<h2>' . rex_escape($category->getName()) . '</h2>';
        
        // Artikel der Kategorie auflisten
        $articles = $category->getArticles();
        foreach ($articles as $article) {
            if (!$article->isStartArticle()) {
                echo '<a href="' . $article->getUrl() . '">';
                echo rex_escape($article->getName());
                echo '</a><br>';
            }
        }
    }
}
?>
```

**Typische Verwendung:**
- News-Kategorie für Artikel-Listen
- Landingpage-Kategorie
- Produkt-Kategorie
- Filterkategorien

### CategoryList Select

Der Feldtyp `categorylist` bietet Mehrfachauswahl von Kategorien mit hierarchischer Darstellung:

**Beispiel:**
```
tm_news_categories: categorylist|News-Kategorien||Mehrere Kategorien für News-Filter
tm_product_categories: categorylist|Produkt-Kategorien|5,8|Standard-Produkt-Kategorien
```

**Features:**
- Mehrfachauswahl mit Checkboxen
- Hierarchische Darstellung mit Einrückung
- Kategorie-IDs werden angezeigt: "Name [ID]"
- "Alle auswählen / Keine" Buttons
- Berechtigungs-Prüfung
- Bootstrap Selectpicker mit Live-Search

**Frontend-Nutzung:**
```php
<?php
use FriendsOfRedaxo\TemplateManager\TemplateManager;

// Kategorie-IDs abrufen (komma-separiert)
$categoryIds = TemplateManager::get('tm_news_categories');

if ($categoryIds) {
    $categoryIds = array_filter(array_map('intval', explode(',', $categoryIds)));
    
    echo '<div class="category-filter">';
    foreach ($categoryIds as $catId) {
        $category = rex_category::get($catId);
        if ($category) {
            echo '<a href="' . $category->getUrl() . '" class="btn">';
            echo rex_escape($category->getName());
            echo '</a> ';
        }
    }
    echo '</div>';
    
    // Oder: Artikel aus allen ausgewählten Kategorien
    $articles = [];
    foreach ($categoryIds as $catId) {
        $category = rex_category::get($catId);
        if ($category) {
            $articles = array_merge($articles, $category->getArticles());
        }
    }
    
    // Artikel ausgeben...
}
?>
```

**Typische Verwendung:**
- Mehrere News-Kategorien
- Produkt-Filtergruppen
- Content-Aggregation aus verschiedenen Bereichen
- Multi-Category Landing Pages

## Frontend-Nutzung


## Frontend-Nutzung

### TemplateManager Class

Die Settings werden über statische Methoden der `TemplateManager` Klasse abgerufen:

```php
<?php
use FriendsOfRedaxo\TemplateManager\TemplateManager;

// Einfacher Zugriff (aktuelle Domain + Sprache)
$companyName = TemplateManager::get('tm_company_name');
$logo = TemplateManager::get('tm_logo');

// Mit Fallback-Wert
$primaryColor = TemplateManager::get('tm_primary_color', '#005d40');

// Bestimmte Sprache abrufen (z.B. Sprach-ID 2)
$companyNameEN = TemplateManager::get('tm_company_name', null, null, 2);

// Bestimmte Domain abrufen (z.B. Domain-ID 1)
$companyDomain1 = TemplateManager::get('tm_company_name', null, 1, null);

// Bestimmte Domain UND Sprache
$companyDomain1EN = TemplateManager::get('tm_company_name', null, 1, 2);

// Alle Settings als Array
$allSettings = TemplateManager::getAll();
?>
```

### Beispiele

```php
<!-- Logo anzeigen -->
<?php if (TemplateManager::get('tm_logo')): ?>
    <img src="<?= rex_url::media(TemplateManager::get('tm_logo')) ?>" alt="Logo">
<?php endif; ?>

<!-- Firmenname mit Fallback -->
<h1><?= rex_escape(TemplateManager::get('tm_company_name', 'Muster GmbH')) ?></h1>

<!-- CKE5 WYSIWYG Inhalt (kein rex_escape!) -->
<?= TemplateManager::get('tm_welcome_text', '<p>Willkommen!</p>') ?>

<!-- E-Mail -->
<?php if (TemplateManager::get('tm_contact_email')): ?>
    <a href="mailto:<?= rex_escape(TemplateManager::get('tm_contact_email')) ?>">
        <?= rex_escape(TemplateManager::get('tm_contact_email')) ?>
    </a>
<?php endif; ?>

<!-- Telefon -->
<?php if (TemplateManager::get('tm_contact_phone')): ?>
    <a href="tel:<?= rex_escape(TemplateManager::get('tm_contact_phone')) ?>">
        <?= rex_escape(TemplateManager::get('tm_contact_phone')) ?>
    </a>
<?php endif; ?>

<!-- Linklist verarbeiten -->
<?php if (TemplateManager::get('tm_footer_links')): ?>
    <ul>
    <?php
    $links = explode(',', TemplateManager::get('tm_footer_links'));
    foreach ($links as $articleId) {
        $article = rex_article::get(trim($articleId));
        if ($article) {
            echo '<li><a href="' . $article->getUrl() . '">' . 
                 rex_escape($article->getName()) . '</a></li>';
        }
    }
    ?>
    </ul>
<?php endif; ?>

<!-- Medialist verarbeiten -->
<?php if (TemplateManager::get('tm_header_images')): ?>
    <div class="slideshow">
    <?php
    $images = explode(',', TemplateManager::get('tm_header_images'));
    foreach ($images as $image) {
        $image = trim($image);
        if ($image) {
            echo '<img src="' . rex_url::media($image) . '" alt="">';
        }
    }
    ?>
    </div>
<?php endif; ?>

<!-- Checkbox prüfen -->
<?php if (TemplateManager::get('tm_show_breadcrumbs')): ?>
    <!-- Breadcrumb-Code hier -->
<?php endif; ?>

<!-- CSS Custom Properties mit Colorselect -->
<style>
    :root {
        --primary-color: <?= TemplateManager::get('tm_primary_color', '#005d40') ?>;
        --primary-dark: color-mix(in srgb, var(--primary-color) 80%, black);
    }
</style>
```

## Backend-Nutzung

### Template-Einstellungen konfigurieren

1. **Template Manager** → **Template Einstellungen** öffnen
2. Template aus Liste auswählen → **Konfigurieren** klicken
3. Domain und Sprache wählen
4. Einstellungen in den Sprach-Tabs eingeben
5. **Speichern** klicken (speichert alle Sprachen gleichzeitig)

### Mehrsprachigkeit

- Jede Sprache hat einen eigenen Tab
- Die erste Sprache dient als **Fallback** für andere Sprachen
- Leere Felder in Sprache 2+ werden automatisch durch Sprache 1 ersetzt
- Alle Sprachen werden gemeinsam gespeichert

### Multi-Domain

- Jede YRewrite-Domain kann eigene Einstellungen haben
- Domain-Auswahl über Dropdown oben
- Default-Domain (ID 0) wird immer als letzte angezeigt
- Settings werden getrennt pro Domain + Sprache gespeichert

## Setup & Wartung

### Demo-Template importieren

**Template Manager** → **Setup** → **Demo-Template jetzt importieren**

Importiert das vorkonfigurierte Modern Business Template mit 5 Einstellungen und modernem Design.

### Datenbank-Cleanup

**Template Manager** → **Setup** → **Verwaiste Settings entfernen**

Entfernt Settings von gelöschten Templates aus der Datenbank. Nützlich wenn Templates gelöscht wurden und deren Settings verblieben sind.

## Datenbankstruktur

Das Addon erstellt die Tabelle `rex_template_settings`:

| Spalte | Typ | Beschreibung |
|--------|-----|--------------|
| id | int | Primary Key |
| template_id | int | REDAXO Template-ID |
| domain_id | int | YRewrite Domain-ID |
| clang_id | int | Sprach-ID |
| setting_key | varchar(191) | Setting-Name (mit tm_ Prefix) |
| setting_value | text | Gespeicherter Wert |
| created_date | datetime | Erstellungsdatum |
| updated_date | datetime | Änderungsdatum |

**UNIQUE KEY:** (template_id, domain_id, clang_id, setting_key)

## Best Practices

### Naming Convention

- **Immer** `tm_` Prefix verwenden
- Sprechende Namen: `tm_contact_email` statt `tm_email1`
- Gruppierung durch Prefix: `tm_footer_col1_title`, `tm_footer_col2_title`

### Defaults setzen

Gib immer sinnvolle Default-Werte an:

```
tm_company_name: text|Firmenname|Muster GmbH|Offizieller Firmenname
```

So funktioniert die Website auch ohne Konfiguration.

### Fallback-Logik

Im Template immer mit Fallback-Werten arbeiten:

```php
<?= rex_escape(TemplateManager::get('tm_company_name', 'Meine Firma')) ?>
```

Die Methode gibt den Default-Wert zurück, wenn das Setting nicht existiert.

### Beschreibungen

Nutze das Beschreibungs-Feld für Hinweise:

```
tm_google_analytics: text|Google Analytics ID||GA4 Tracking-ID (Format: G-XXXXXXXXXX)
```

## Beispiel: Minimales Template

```php
<?php
/**
 * Simple Business Template
 * 
 * DOMAIN_SETTINGS
 * tm_logo: media|Logo||Firmenlogo
 * tm_company_name: text|Firmenname|Muster GmbH|Firmenname
 * tm_contact_email: email|E-Mail|info@beispiel.de|Kontakt E-Mail
 * tm_primary_color: text|Akzentfarbe|#005d40|Akzentfarbe (Hex)
 * tm_footer_text: textarea|Footer-Text|© 2024 Muster GmbH|Copyright-Text
 */

use FriendsOfRedaxo\TemplateManager\TemplateManager;
?>
<!DOCTYPE html>
<html>
<head>
    <title><?= $this->getValue('name') ?> | <?= TemplateManager::get('tm_company_name') ?></title>
    <style>
        :root { --primary: <?= TemplateManager::get('tm_primary_color', '#005d40') ?>; }
        body { font-family: sans-serif; }
        header { background: var(--primary); color: white; padding: 1rem; }
    </style>
</head>
<body>
    <header>
        <?php if (TemplateManager::get('tm_logo')): ?>
            <img src="<?= rex_url::media(TemplateManager::get('tm_logo')) ?>" alt="Logo">
        <?php endif; ?>
        <h1><?= TemplateManager::get('tm_company_name') ?></h1>
    </header>
    
    <main><?= $this->getArticle() ?></main>
    
    <footer>
        <p><?= nl2br(rex_escape(TemplateManager::get('tm_footer_text'))) ?></p>
        <a href="mailto:<?= TemplateManager::get('tm_contact_email') ?>">Kontakt</a>
    </footer>
</body>
</html>
```

## Technische Details

### Namespaces

```php
use FriendsOfRedaxo\TemplateManager\TemplateParser;
use FriendsOfRedaxo\TemplateManager\TemplateManager;
```

### Parser-Regex

Der Parser sucht nach diesem Pattern:

```regex
#\*\s+(tm_\w+):\s*([^|]+)\|([^|]*)\|([^|]*)\|(.*)$#m
```

Nur Zeilen mit `tm_` Prefix werden erfasst!

### Boot-Prozess

1 Bei jedem Frontend-Request:
   - TemplateManager::get() wird aufgerufen
   - Beim ersten Aufruf: Cache wird geladen
   - Aktuelle Domain + Sprache werden automatisch ermittelt
   - Settings aus DB geladen mit Fallback-Logik
   - Ergebnis wird pro Request gecacht (nach Domain/Sprach-Kombination)

### API-Signatur

```php
TemplateManager::get(
    string $key,              // Setting-Key (z.B. 'tm_company_name')
    mixed $default = null,    // Fallback wenn nicht vorhanden
    ?int $domainId = null,    // Optional: Domain-ID (null = aktuelle)
    ?int $clangId = null      // Optional: Sprach-ID (null = aktuelle)
): mixed

TemplateManager::getAll(
    ?int $domainId = null,    // Optional: Domain-ID (null = aktuelle)
    ?int $clangId = null      // Optional: Sprach-ID (null = aktuelle)
): array
```

## Anforderungen

- **REDAXO:** >= 5.17
- **YRewrite:** >= 2.0 (für Multi-Domain Support)
- **PHP:** >= 8.0
- **Optional:** UIKit Theme Builder (für `uikit_theme_select` Feldtyp)

## Lizenz

MIT License

## Changelog

### Version 1.2.0 (19.11.2025)
- ✨ **Neuer Feldtyp**: `external_linklist` für externe Link-Listen mit Live-Vorschau
- 🎨 **Repeater-Funktionalität**: Strukturierte externe Links (Name|URL|Beschreibung)
- 🔍 **Live-Validierung**: URL-Prüfung und Format-Feedback im Backend
- 📝 **Kommentar-Support**: Zeilen mit # oder // werden ignoriert
- 🎯 **ExternalLinklistWidget**: Neue Helper-Klasse mit parse() und renderHtml() Methoden
- 🏗️ **Architektur**: Eigener FieldRenderer statt textarea-Missbrauch
- 📚 **Dokumentation**: Umfassende Beispiele für external_linklist Nutzung

### Version 1.1.0 (19.11.2025)
- ✨ **Neue Feldtypen**: `banner_select` für UIKit Banner Design Integration
- 🎨 **Footer Design**: Professionelle rechtliche Gestaltung mit kompakten Link-Abständen
- 📱 **Mobile Optimierung**: Verbesserte Navigation mit größerem Hamburger-Icon
- 🌐 **Multi-Column Footer**: Unterstützung für flexible Grid-Layouts
- ♿ **Accessibility**: Responsive Legal-Navigation mit verbesserter Mobile-Darstellung

### Version 1.0.0 (Initial Release)
- 🎉 Erste öffentliche Version
- 📝 DocBlock-basierte Konfiguration
- 🌍 Multi-Domain Support mit YRewrite
- 🌐 Mehrsprachigkeit mit Fallback-Logik
- 🎨 20+ Feldtypen
- 🔧 Native REDAXO Widgets
- 🚀 Statische Frontend-API
- 📦 Demo-Template

## Support

- GitHub Issues: https://github.com/FriendsOfREDAXO/template_manager/issues
- GitHub Repository: https://github.com/FriendsOfREDAXO/template_manager

## Credits

Entwickelt von Friends Of REDAXO

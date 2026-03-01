# Template Manager

Ein REDAXO-Addon zur Verwaltung von domain- und sprachspezifischen Template-Einstellungen über DocBlock-Konfiguration.

## Features

- 📝 **DocBlock-basierte Konfiguration** - Template-Settings direkt im Template-Code definieren
- 🌍 **Multi-Domain Support** - Unterschiedliche Einstellungen pro YRewrite-Domain
- 🌐 **Mehrsprachigkeit** - Separate Einstellungen für jede Sprache mit Fallback
- 🎨 **20+ Feldtypen** - text, textarea, cke5, number, email, tel, date, time, color, colorselect, media, medialist, select, checkbox, link, linklist u.v.m.
- 🔧 **Native REDAXO Widgets** - Volle Integration von Linkmap, Medienpicker und Bootstrap Selectpicker
- 🎨 **Visuelle Farbauswahl** - Colorselect mit farbigen Badges
- 🏢 **Globale Variablen** - Mandantenübergreifende Einstellungen (SSO, Firmeninfos, API-Keys)
- � **Notice-Felder** - Hinweise, Warnungen und Informationen direkt im Einstellungsformular
- 🔄 **Domain-Einstellungen kopieren** - Settings einer Domain auf andere Domains übertragen
- �🚀 **Einfache Frontend-API** - Statische Klassen-Methoden mit optionalen Domain/Sprach-Parametern
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
  - 16+ konfigurierbare Einstellungen
  - Modernes, responsives Design ohne Framework-Abhängigkeiten
  - CSS Custom Properties für einfaches Theming
  - Barrierefrei (WCAG 2.1 AA)
  - Font Awesome 6 Integration
  - Demo-Content wenn noch kein Content vorhanden

**Enthaltene Feldtypen:**
- `text` - Firmenname, Slogan
- `colorselect` - Akzentfarbe (8 vordefinierte Brand-Farben)
- `email` - Kontakt E-Mail
- `tel` - Telefonnummer
- `number` - Mitarbeiteranzahl, Gründungsjahr
- `media` - Logo
- `medialist` - Header-Bilder
- `link` - Startseite
- `linklist` - Footer-Links
- `category` - Hauptkategorie
- `categorylist` - Service-Kategorien
- `checkbox` - Breadcrumbs anzeigen, Kontaktinfo im Header
- `social_links` - Social Media Links (Repeater mit Sortierung)
- `opening_hours` - Strukturierte Öffnungszeiten (Google-Style)

Import über: **Template Manager** → **Setup** → **Demo-Template jetzt importieren**

## Globale Variablen (Global Settings)

Ab Version 1.4.0 unterstützt der Template Manager **Globale Variablen**. Diese sind ideal für Einstellungen, die über alle Domains und Templates hinweg identisch sein sollen (z.B. Firmenname, Social Media Profile, API-Keys), aber dennoch sprachspezifisch verwaltet werden müssen.

### Definition

Die Definition erfolgt unter **Template Manager** -> **Globale Variablen** im Tab **Definition**. Hier wird das gleiche DocBlock-Format verwendet wie in den Templates, jedoch mit dem Marker `GLOBAL_SETTINGS`:

```php
/**
 * GLOBAL_SETTINGS
 * 
 * --- Unternehmen [fa-solid fa-building] ---
 * tm_company_name: text|Firmenname|Mein Unternehmen|Offizieller Name
 * tm_company_email: email|E-Mail|info@beispiel.de|Zentrale Kontaktadresse
 * 
 * --- Social Media [fa-brands fa-share-nodes] ---
 * tm_social_facebook: text|Facebook URL||Link zum Profil
 * tm_social_instagram: text|Instagram URL||Link zum Profil
 */
```

### Werte pflegen

Sobald eine Definition gespeichert wurde, erscheint automatisch der Tab **Werte**. Dort können pro installierter Sprache die konkreten Inhalte gepflegt werden.

### Zugriff im Frontend

Der Zugriff erfolgt identisch zu den Template-Settings über die `TemplateManager::get()` Methode:

```php
<?php
use FriendsOfRedaxo\TemplateManager\TemplateManager;

// Holt den Wert (Priorität: Template-Setting > Globales Setting > Default)
$companyName = TemplateManager::get('tm_company_name');
?>
```

**Kaskadierung (Priority):**
1. **Template-Setting**: Wenn im aktuellen Template ein Feld mit gleichem Namen definiert und befüllt ist.
2. **Globales Setting**: Wenn im Template nichts definiert ist (oder kein Wert vorliegt), wird der globale Wert gezogen.
3. **Default-Wert**: Der in der Definition angegebene Standardwert.

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

### Gruppierung mit Akkordeons

Ab Version 1.3.0 können Einstellungen in thematische Gruppen organisiert werden:

**Syntax:**
```
--- Gruppenname ---
```

Optional mit **Font Awesome 6 Icon**:
```
--- Gruppenname [fa-solid fa-icon] ---
```

Optional mit **Rechteverwaltung** (nur für bestimmte Benutzergruppen sichtbar):
```
--- Gruppenname [admin,developer] ---
```

Beides kombiniert:
```
--- Gruppenname [fa-solid fa-icon] [admin,developer] ---
```

**Beispiel:**
```php
/**
 * DOMAIN_SETTINGS
 * 
 * --- Branding & Design [fa-solid fa-palette] ---
 * tm_company_name: text|Firmenname|Muster GmbH|Offizieller Firmenname
 * tm_logo: media|Logo||Firmenlogo (Header)
 * tm_primary_color: colorselect|Akzentfarbe|#005d40|Hauptfarbe
 * 
 * --- Kontaktinformationen [fa-solid fa-address-book] ---
 * tm_contact_email: email|E-Mail|info@beispiel.de|Kontakt E-Mail
 * tm_contact_phone: tel|Telefon|+49 123 456789|Telefonnummer
 * 
 * --- Navigation & Links [fa-solid fa-bars] [admin,developer] ---
 * tm_footer_links: linklist|Footer-Links||Artikel-IDs für Footer
 * tm_show_breadcrumbs: checkbox|Breadcrumbs anzeigen||Navigation aktivieren
 */
```

**Features:**
- **Icons:** Font Awesome 6 Icons (solid, brands, regular) werden automatisch vor dem Gruppennamen angezeigt
- **Rechte:** Admin sieht immer alles, andere Benutzer nur Gruppen ohne Rechte oder mit passender Rolle
- **Accordion:** Gruppen werden als Bootstrap-Akkordeons dargestellt, erste Gruppe standardmäßig geöffnet
- **Ohne Gruppen:** Alte Darstellung ohne Akkordeons bleibt erhalten

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
| **Spezial-Felder** |
| `notice` | Hinweis-/Warnbox im Formular (kein Datenbankfeld) | `info` |
| `social_links` | Social Media Links Repeater | JSON (Icon + URL + Label) |
| `opening_hours` | Strukturierte Öffnungszeiten | JSON (Wochentage + Sonderzeiten) |

### Notice-Felder – Hinweise und Warnungen im Formular

Der Feldtyp `notice` ist ein rein visuelles Hinweisfeld im Einstellungsformular. Er speichert **keinen Wert** in der Datenbank, sondern zeigt einen formatierten Alert-Block an – ideal für Erklärungen, Warnungen oder Trennungshilfen zwischen Feldergruppen.

**Syntax:**
```
tm_key: notice|Label|Typ|Text
```

**Typen:**

| Typ | Farbe | Verwendung |
|-----|-------|------------|
| `info` | Blau (Standard) | Neutrale Hinweise, Erklärungen |
| `success` | Grün | Positive Infos, Bestätigungen |
| `warning` | Orange | Wichtige Hinweise, Vorsicht |
| `danger` | Rot | Kritische Warnungen |

**Beispiele:**
```
tm_hint_colors: notice|Hinweis|info|Farben werden als CSS Custom Properties ausgegeben.
tm_warn_api: notice|Achtung|warning|Änderungen an API-Keys erfordern einen Cache-Flush.
tm_info_media: notice|Info|success|Bilder sollten mindestens 1200×600 px groß sein.
tm_alert_delete: notice|Warnung|danger|Das Löschen kann nicht rückgängig gemacht werden!
```

> **Hinweis:** Das `tm_`-Prefix ist auch bei Notice-Feldern erforderlich. Der Schlüssel kann beliebig gewählt werden, da er nicht in der Datenbank landet.

---

### Domain-Einstellungen kopieren

Settings eines Templates können mit einem Klick von einer Domain auf eine andere übertragen werden. Die Funktion ist nur für Admins oder Benutzer mit dem Recht `template_manager[copy]` sichtbar.

**Vorgehen:**
1. **Template Manager** → **Template Einstellungen** → Template und Quell-Domain wählen
2. Button **„Einstellungen kopieren nach …"** unterhalb der Auswahl klicken
3. Ziel-Domain wählen, Sprachen auswählen, Überschreiben-Option setzen
4. **„Jetzt kopieren"** klicken → Ergebnis zeigt kopierte / übersprungene / fehlerhafte Einträge

**Optionen:**
- **Sprachen:** Nur ausgewählte Sprachen werden übertragen
- **Vorhandene Werte überschreiben** (Standard aktiv): Bestehende Werte in der Ziel-Domain werden ersetzt; deaktiviert werden nur fehlende Werte ergänzt

---

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

### Social Links - Social Media mit Repeater & Sortierung

Der Feldtyp `social_links` bietet eine komfortable Verwaltung von Social Media Links mit Drag & Drop Sortierung:

**Beispiel:**
```
tm_social_links: social_links|Social Media Links||Verlinkte Social-Media-Profile
tm_social_links: social_links|Social Media Links|fa|Nur Font Awesome Icons
tm_social_links: social_links|Social Media Links|uk|Nur UIKit Icons
tm_social_links: social_links|Social Media Links|both|Beide Icon-Sets (Standard)
```

**Icon-Modus (im Default-Wert):**
- `fa` - Nur Font Awesome Icons
- `uk` - Nur UIKit Icons  
- `both` oder leer - Beide Icon-Sets (Standard)

**Backend-Features:**
- ✅ **30+ vordefinierte Social Icons** (Font Awesome + UIKit)
- ✅ **Drag & Drop Sortierung** mit Handle
- ✅ **Pfeil-Buttons** zum manuellen Sortieren
- ✅ **Live Icon-Vorschau** bei der Auswahl
- ✅ **Optionales Label** pro Link
- ✅ **Icon-Set wählbar** (fa, uk, both)

**Enthaltene Icons:**
- Facebook, Twitter/X, Instagram, LinkedIn, Xing, YouTube
- TikTok, Pinterest, WhatsApp, Telegram, GitHub, GitLab
- Discord, Slack, Mastodon, Threads, Bluesky, Reddit
- Snapchat, Vimeo, Dribbble, Behance, Flickr, Spotify
- SoundCloud, Twitch, RSS, E-Mail, Telefon, Webseite

**Frontend-Nutzung:**
```php
<?php
use FriendsOfRedaxo\TemplateManager\TemplateManager;

$socialLinksJson = TemplateManager::get('tm_social_links');
$socialLinks = $socialLinksJson ? json_decode($socialLinksJson, true) : [];

if (!empty($socialLinks)): ?>
<div class="social-links">
    <?php foreach ($socialLinks as $link): 
        if (empty($link['url'])) continue;
        $icon = $link['icon'] ?? '';
        $url = $link['url'];
        $label = $link['label'] ?? '';
        
        // Icon-Klasse ermitteln
        if (str_starts_with($icon, 'uk-icon-')) {
            // UIKit Icon
            $ukIcon = str_replace('uk-icon-', '', $icon);
            $iconHtml = '<span uk-icon="icon: ' . rex_escape($ukIcon) . '"></span>';
        } else {
            // Font Awesome (für FA6 CDN)
            $brandIcons = ['fa-facebook', 'fa-twitter', 'fa-instagram', /* ... */];
            $faClass = in_array($icon, $brandIcons, true) 
                ? 'fa-brands ' . $icon 
                : 'fa-solid ' . $icon;
            $iconHtml = '<i class="' . rex_escape($faClass) . '"></i>';
        }
    ?>
    <a href="<?= rex_escape($url) ?>" 
       target="_blank" 
       rel="noopener noreferrer"
       <?= $label ? 'title="' . rex_escape($label) . '"' : '' ?>>
        <?= $iconHtml ?>
    </a>
    <?php endforeach; ?>
</div>
<?php endif; ?>
```

**JSON-Datenstruktur:**
```json
[
    { "icon": "fa-facebook", "url": "https://facebook.com/firma", "label": "Facebook" },
    { "icon": "fa-instagram", "url": "https://instagram.com/firma", "label": "Instagram" },
    { "icon": "uk-icon-github", "url": "https://github.com/firma", "label": "" }
]
```

### Opening Hours - Strukturierte Öffnungszeiten (Google My Business Style)

Der Feldtyp `opening_hours` bietet eine professionelle Verwaltung von Öffnungszeiten wie bei Google My Business:

**Beispiel:**
```
tm_opening_hours: opening_hours|Öffnungszeiten||Geschäftszeiten inkl. Feiertage
```

**Backend-Features:**
- ✅ **Reguläre Zeiten** pro Wochentag (Mo-So)
- ✅ **Mehrere Zeitfenster pro Tag** (z.B. für Mittagspausen: 9-12 + 14-18 Uhr)
- ✅ **Status-Optionen**: Geöffnet / Geschlossen / 24h geöffnet
- ✅ **Schnellaktionen**: "Mo → Werktage", "Alle geschlossen", "Zurücksetzen"
- ✅ **Kopier-Funktion**: Zeiten auf andere Tage/Gruppen übertragen
- ✅ **Sonderzeiten/Feiertage** mit 15 vordefinierten deutschen Feiertagen
- ✅ **Bewegliche Feiertage**: Ostern, Pfingsten, Christi Himmelfahrt etc.
- ✅ **Live-Vorschau** mit Tabelle und "heute"-Markierung
- ✅ **3 Tabs**: Reguläre Zeiten | Sonderzeiten | Vorschau
- ✅ **Freitext/Notiz-Feld** für zusätzliche Hinweise (z.B. "Termine nach Vereinbarung")
- ✅ **Auto-Repair**: Feiertage werden automatisch erkannt und repariert

**Vordefinierte Feiertage:**
- Neujahr, Karfreitag, Ostersonntag, Ostermontag
- Tag der Arbeit, Christi Himmelfahrt, Pfingstsonntag, Pfingstmontag
- Fronleichnam, Tag der Deutschen Einheit, Allerheiligen
- Heiligabend, 1. + 2. Weihnachtstag, Silvester

**Frontend-Nutzung mit OpeningHoursHelper (empfohlen):**

Die `OpeningHoursHelper`-Klasse bietet eine komfortable API mit Übersetzungen und vorformatierten Daten:

```php
<?php
use FriendsOfRedaxo\TemplateManager\TemplateManager;
use FriendsOfRedaxo\TemplateManager\OpeningHoursHelper;

// Helper instanziieren (Sprache wird automatisch erkannt)
$helper = new OpeningHoursHelper(
    TemplateManager::get('tm_opening_hours'),
    rex_clang::getCurrent()->getCode() // 'de', 'en' etc.
);

if ($helper->hasData()):
    $regularHours = $helper->getRegular();        // Alle Wochentage einzeln
    $groupedHours = $helper->getRegularGrouped(); // Tage mit gleichen Zeiten zusammengefasst
    $specialHours = $helper->getSpecial(5, true); // Max 5, nur zukünftige
    $currentStatus = $helper->getCurrentStatus(); // Jetzt geöffnet?
?>

<!-- Status-Badge -->
<?php if ($currentStatus['is_open']): ?>
    <span class="badge badge-success"><?= rex_escape($currentStatus['label']) ?></span>
    <?php if ($currentStatus['next_change_label']): ?>
        <small><?= rex_escape($currentStatus['next_change_label']) ?></small>
    <?php endif; ?>
<?php else: ?>
    <span class="badge badge-danger"><?= rex_escape($currentStatus['label']) ?></span>
<?php endif; ?>

<!-- Gruppierte Öffnungszeiten (empfohlen für kompakte Darstellung) -->
<h3><?= rex_escape($helper->translate('labels.opening_hours')) ?></h3>
<table>
    <?php foreach ($groupedHours as $group): ?>
    <tr<?= $group['contains_today'] ? ' class="today"' : '' ?>>
        <td><?= rex_escape($group['label']) ?></td> <!-- z.B. "Mo - Fr" oder "Sa, So" -->
        <td class="<?= $group['is_closed'] ? 'closed' : '' ?>">
            <?= rex_escape($group['formatted']) ?>
        </td>
    </tr>
    <?php endforeach; ?>
</table>

<!-- Freitext/Notiz anzeigen -->
<?php if ($helper->hasNote()): ?>
<p class="note"><em><?= rex_escape($helper->getNote()) ?></em></p>
<?php endif; ?>

<!-- Alternative: Alle Tage einzeln -->
<!--
<table>
    <?php foreach ($regularHours as $day): ?>
    <tr<?= $day['is_today'] ? ' class="today"' : '' ?>>
        <td>
            <?= rex_escape($day['label']) ?>
            <?= $day['is_today'] ? ' <small>(' . rex_escape($helper->translate('labels.today')) . ')</small>' : '' ?>
        </td>
        <td class="<?= $day['is_closed'] ? 'closed' : '' ?>">
            <?= rex_escape($day['formatted']) ?>
        </td>
    </tr>
    <?php endforeach; ?>
</table>
-->

<!-- Sonderöffnungszeiten -->
<?php if (!empty($specialHours)): ?>
<h4><?= rex_escape($helper->translate('labels.special_hours')) ?></h4>
<ul>
    <?php foreach ($specialHours as $special): ?>
    <li>
        <strong><?= rex_escape($special['display_name']) ?>:</strong>
        <?= rex_escape($special['formatted']) ?>
    </li>
    <?php endforeach; ?>
</ul>
<?php endif; ?>

<?php endif; ?>
```

**OpeningHoursHelper - Verfügbare Methoden:**

| Methode | Beschreibung |
|---------|--------------|
| `hasData()` | Prüft ob Daten vorhanden sind |
| `getRegular(bool $shortLabels = false)` | Reguläre Zeiten als Array (jeden Tag einzeln) |
| `getRegularGrouped(bool $shortLabels = true)` | Aufeinanderfolgende Tage mit gleichen Zeiten zusammengefasst |
| `getSpecial(?int $limit, bool $futureOnly)` | Sonderzeiten als Array |
| `getToday()` | Heutigen Tag mit Status |
| `isOpenNow()` | Prüft ob aktuell geöffnet |
| `getCurrentStatus()` | Status-Array für "Jetzt geöffnet"-Anzeige |
| `getNote()` | Freitext/Notiz abrufen (z.B. "Termine nach Vereinbarung") |
| `hasNote()` | Prüft ob eine Notiz vorhanden ist |
| `translate(string $key)` | Übersetzung abrufen |
| `setLocale(string $locale)` | Sprache ändern |
| `setTranslations(string $locale, array $translations)` | Eigene Übersetzungen setzen |

**Eigene Übersetzungen hinzufügen:**

```php
$helper = new OpeningHoursHelper($json);

// Französisch hinzufügen
$helper->setTranslations('fr', [
    'weekdays' => [
        'monday' => 'Lundi',
        'tuesday' => 'Mardi',
        // ...
    ],
    'status' => [
        'closed' => 'Fermé',
        'open_24h' => 'Ouvert 24h/24',
        'open' => 'Ouvert',
    ],
    'labels' => [
        'today' => "aujourd'hui",
        'opening_hours' => "Heures d'ouverture",
        'special_hours' => 'Horaires spéciaux',
        'we_are_open' => 'Nous sommes ouverts',
        'we_are_closed' => 'Nous sommes fermés',
        'time_suffix' => 'h',
    ],
]);
```

**Datenstruktur der Arrays:**

```php
// getRegular() gibt zurück (jeden Tag einzeln):
[
    'monday' => [
        'key' => 'monday',
        'label' => 'Montag',           // Übersetzt
        'label_short' => 'Mo',         // Kurz
        'label_full' => 'Montag',      // Lang
        'status' => 'open',            // open, closed, 24h
        'status_label' => 'Geöffnet',  // Übersetzt
        'is_today' => true,            // Bool
        'is_open' => true,             // Bool
        'is_closed' => false,          // Bool
        'is_24h' => false,             // Bool
        'times' => [                   // Rohdaten
            ['open' => '09:00', 'close' => '12:00'],
            ['open' => '14:00', 'close' => '18:00'],
        ],
        'times_formatted' => '09:00–12:00, 14:00–18:00 Uhr', // Formatiert
        'formatted' => '09:00–12:00, 14:00–18:00 Uhr',       // Status oder Zeiten
    ],
    // ...
]

// getRegularGrouped() gibt zurück (aufeinanderfolgende Tage mit gleichen Zeiten zusammengefasst):
[
    [
        'label' => 'Mo - Fr',           // Kurzform-Bereich
        'label_full' => 'Montag - Freitag', // Langform-Bereich
        'days' => ['monday', 'tuesday', 'wednesday', 'thursday', 'friday'],
        'day_count' => 5,
        'status' => 'open',
        'status_label' => 'Geöffnet',
        'is_open' => true,
        'is_closed' => false,
        'is_24h' => false,
        'times' => [...],
        'times_formatted' => '09:00–18:00 Uhr',
        'formatted' => '09:00–18:00 Uhr',
        'contains_today' => true,       // Ist der aktuelle Tag in dieser Gruppe?
    ],
    [
        'label' => 'Sa, So',            // Zwei aufeinanderfolgende Tage
        'label_full' => 'Samstag, Sonntag',
        'day_count' => 2,
        'is_closed' => true,
        'formatted' => 'Geschlossen',
        'contains_today' => false,
    ],
]

// getSpecial() gibt zurück:
[
    [
        'date' => '12-24',                    // Originaldatum
        'date_resolved' => '2026-12-24',      // Aufgelöstes Datum
        'date_formatted' => '24.12.',         // Formatiert
        'name' => 'Heiligabend',              // Name
        'display_name' => 'Heiligabend',      // Name oder formatiertes Datum
        'status' => 'open',
        'status_label' => 'Geöffnet',
        'is_holiday' => true,                 // Vordefinierter Feiertag
        'is_open' => true,
        'is_closed' => false,
        'times' => [...],
        'times_formatted' => '09:00–14:00 Uhr',
        'formatted' => '09:00–14:00 Uhr',
    ],
    // ...
]

// getCurrentStatus() gibt zurück:
[
    'is_open' => true,
    'label' => 'Wir haben geöffnet',
    'today' => [...],                     // getToday() Daten
    'next_change' => '18:00',             // Nächste Statusänderung
    'next_change_label' => 'Schließt um 18:00 Uhr',
]
```

**JSON-Datenstruktur:**
```json
{
    "regular": {
        "monday": { 
            "status": "open", 
            "times": [
                { "open": "09:00", "close": "12:00" },
                { "open": "14:00", "close": "18:00" }
            ]
        },
        "saturday": { "status": "closed", "times": [] },
        "sunday": { "status": "closed", "times": [] }
    },
    "special": [
        { 
            "date": "12-24", 
            "name": "Heiligabend", 
            "status": "open", 
            "times": [{ "open": "09:00", "close": "14:00" }],
            "holiday": true
        },
        { 
            "date": "2026-01-02", 
            "name": "Betriebsferien", 
            "status": "closed", 
            "times": [],
            "holiday": false
        }
    ],
    "note": "Termine nach Vereinbarung unter 0123-456789"
}
```

**Typische Verwendung:**
- Geschäfts-/Ladenöffnungszeiten
- Praxis-/Kanzlei-Sprechstunden
- Restaurant-Öffnungszeiten mit Mittagspause
- Service-Hotline Erreichbarkeit

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

### Version 1.5.0 (01.03.2026)
- ✨ **Neuer Feldtyp `notice`**: Hinweis-, Info-, Warn- und Danger-Boxen direkt im Einstellungsformular (kein DB-Wert)
- 🔄 **Domain-Einstellungen kopieren**: Settings eines Templates per Formular von einer Domain auf eine andere übertragen
- 🔒 **Rechteverwaltung Copy**: Nur Admins oder User mit `template_manager[copy]` können kopieren
- 🌑 **Dark Mode**: OpeningHours-Widget mit vollständigem Dark-Mode via CSS Custom Properties (`--oh-*`)
- 🐛 **Bugfix**: YRewrite Default-Domain (ID null) wurde als „Unbekannt" angezeigt – `(int)` Cast behebt Typen-Vergleich

### Version 1.4.0
- 🏢 **Globale Variablen** (`GLOBAL_SETTINGS`): Domainübergreifende Einstellungen mit eigenem Menüpunkt
- 🔑 **Kaskadierung**: Template-Setting → Globales Setting → Default-Wert
- 📝 **Akkordeon-Gruppen**: Felder in thematische, aufklappbare Gruppen mit Icons organisieren
- 🔒 **Gruppen-Rechte**: Gruppen pro Benutzerrolle sichtbar/unsichtbar

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

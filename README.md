# Template Manager

Ein REDAXO-Addon zur Verwaltung von domain- und sprachspezifischen Template-Einstellungen über DocBlock-Konfiguration.

## Features

- 📝 **DocBlock-basierte Konfiguration** - Template-Settings direkt im Template-Code definieren
- 🌍 **Multi-Domain Support** - Unterschiedliche Einstellungen pro YRewrite-Domain
- 🌐 **Mehrsprachigkeit** - Separate Einstellungen für jede Sprache mit Fallback
- 🎨 **9 Feldtypen** - text, textarea, email, url, media, select, checkbox, link, linklist
- 🔧 **Native REDAXO Widgets** - Volle Integration von Linkmap und Medienpicker
- 🚀 **Einfache Frontend-API** - Statische Klassen-Methoden mit optionalen Domain/Sprach-Parametern

## Installation

1. Addon im REDAXO-Backend installieren
2. Optional: Demo-Template über **Template Manager** → **Setup** importieren
3. Eigene Templates mit DOMAIN_SETTINGS erstellen

## Demo-Template

Das Addon enthält ein vorkonfiguriertes Demo-Template:

- **Name:** Modern Business (Demo)
- **Features:**
  - Dark/Light Mode Support (automatisch basierend auf System-Einstellungen)
  - 5 essenzielle Einstellungen
  - Modernes, responsives Design ohne Framework-Abhängigkeiten
  - CSS Custom Properties für einfaches Theming
  - Barrierefrei (WCAG 2.1 AA)
  - Demo-Content wenn noch kein Content vorhanden

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
 * tm_contact_email: email|E-Mail|info@beispiel.de|Kontakt E-Mail-Adresse
 * tm_footer_links: linklist|Footer-Links||Artikel-IDs für Footer-Navigation
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
| `text` | Einzeiliges Textfeld | `Beispieltext` |
| `textarea` | Mehrzeiliges Textfeld | `Längerer Text` |
| `email` | E-Mail-Adresse | `info@beispiel.de` |
| `url` | URL/Link (extern) | `https://beispiel.de` |
| `media` | Mediendatei (natives Widget) | `logo.png` |
| `select` | Dropdown-Auswahl | `wert1:Label 1,wert2:Label 2` |
| `checkbox` | Ja/Nein Checkbox | `` (leer = nicht aktiviert) |
| `link` | Interner REDAXO-Link (natives Widget) | `5` (Artikel-ID) |
| `linklist` | Liste interner Links (natives Widget) | `1,5,8` (Artikel-IDs) |

### Select-Optionen

Bei Select-Feldern werden die Optionen im Default-Wert definiert:

```
tm_header_style: select|Header-Stil|standard|standard:Standard,modern:Modern,minimal:Minimal|Auswahl des Header-Designs
```

Format: `wert:Label,wert2:Label2` oder einfach `wert1,wert2,wert3`

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

<!-- E-Mail -->
<?php if (TemplateManager::get('tm_contact_email')): ?>
    <a href="mailto:<?= rex_escape(TemplateManager::get('tm_contact_email')) ?>">
        <?= rex_escape(TemplateManager::get('tm_contact_email')) ?>
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

<!-- Checkbox prüfen -->
<?php if (TemplateManager::get('tm_show_breadcrumbs')): ?>
    <!-- Breadcrumb-Code hier -->
<?php endif; ?>

<!-- CSS Custom Properties -->
<style>
    :root {
        --primary-color: <?= TemplateManager::get('tm_primary_color', '#005d40') ?>;
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

## Lizenz

MIT License

## Support

- GitHub Issues: [https://github.com/FriendsOfREDAXO/template_manager)

## Credits

Entwickelt von Friends Of REDAXO

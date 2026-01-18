# Changelog

Alle wichtigen Änderungen am Template Manager werden in dieser Datei dokumentiert.

## [1.3.0] - 2026-01-19

### Added - OpeningHours Widget (NEU)

- ✅ **SocialLinksFieldRenderer** - Social Media Links mit Drag & Drop Sortierung (30+ Icons)

**Neuer Feldtyp `opening_hours`:**
- ✅ **OpeningHoursFieldRenderer** - Professionelle Öffnungszeiten-Verwaltung im Google My Business Style
- ✅ **OpeningHoursHelper** - Frontend-Helferklasse mit Mehrsprachigkeit (DE/EN)
- ✅ **3 Tabs im Backend**: Reguläre Zeiten | Sonderzeiten/Feiertage | Live-Vorschau

**Reguläre Öffnungszeiten:**
- ✅ **Status pro Wochentag** - Geöffnet / Geschlossen / 24h geöffnet
- ✅ **Mehrere Zeitfenster pro Tag** - z.B. für Mittagspausen (09:00-12:00, 14:00-18:00)
- ✅ **Schnellaktionen** - "Mo → Werktage kopieren", "Alle geschlossen", "Zurücksetzen"
- ✅ **Freitext/Notiz-Feld** - Für zusätzliche Hinweise (z.B. "Termine nach Vereinbarung")

**Feiertage & Sonderzeiten:**
- ✅ **15 vordefinierte deutsche Feiertage** - Dropdown zur schnellen Auswahl
- ✅ **Bewegliche Feiertage** - Ostern, Pfingsten, Christi Himmelfahrt, Fronleichnam automatisch berechnet
- ✅ **Individuelle Daten** - Eigene Sonderzeiten mit Namen hinzufügen
- ✅ **Robuste Oster-Berechnung** - 3 Fallback-Methoden für maximale Kompatibilität

**Frontend-Helper (`OpeningHoursHelper`):**
- ✅ **`getRegular()`** - Alle Wochentage einzeln mit Status und Zeiten
- ✅ **`getRegularGrouped()`** - Aufeinanderfolgende Tage mit gleichen Zeiten zusammengefasst (z.B. "Mo - Fr")
- ✅ **`getSpecial()`** - Sonderzeiten/Feiertage mit Datumsauflösung
- ✅ **`getCurrentStatus()`** - "Jetzt geöffnet/geschlossen" mit nächster Statusänderung
- ✅ **`isOpenNow()`** - Boolean für aktuellen Status
- ✅ **`getToday()`** - Heutiger Tag mit allen Details
- ✅ **`getNote()` / `hasNote()`** - Freitext/Notiz abrufen
- ✅ **Mehrsprachigkeit** - DE/EN integriert, erweiterbar via `setTranslations()`

**Demo-Template:**
- ✅ **Sidebar-Layout** - Öffnungszeiten in sticky Sidebar neben Hauptinhalt
- ✅ **Pulsierender Status-Badge** - Animation für "Aktuell geöffnet"
- ✅ **Kompakte gruppierte Darstellung** - Nutzt `getRegularGrouped()`
- ✅ **Sonderzeiten-Anzeige** - Mit formatiertem Datum
- ✅ **Notiz-Anzeige** - Mit Info-Icon in der Sidebar

### Fixed
- 🐛 **Auto-Repair für Feiertage** - Erkennt und repariert korrupte Daten beim Laden

### Changed
- 📝 **README komplett erweitert** - Ausführliche Dokumentation mit Beispielen
- 📝 **Demo-Template aktualisiert** - Zeigt alle OpeningHours-Features

---

## [1.2.0] - 2026-01-15

### Added - Social Links & External Linklist
- ✅ **ExternalLinklistFieldRenderer** - Externe Links mit Live-Vorschau und Validierung

---

## [1.1.0] - 2026-01-10

### Added - Category Feldtypen
- ✅ **CategoryFieldRenderer** - Hierarchische Kategorie-Auswahl mit Einrückung
- ✅ **CategoryListFieldRenderer** - Mehrfachauswahl von Kategorien

---

## [1.0.0] - Initial Release

### Added
- ✅ **Extension Point System** - `TEMPLATE_MANAGER_FIELD_RENDERERS` für eigene Feldtypen
- ✅ **Field Renderer Architecture** - Saubere Trennung der Feldtyp-Logik
  - `FieldRendererInterface` - Interface für alle Renderer
  - `AbstractFieldRenderer` - Basisklasse mit Hilfsmethoden
  - `FieldRendererManager` - Zentrale Verwaltung aller Renderer
- ✅ **Standard Field Renderer:**
  - `InputFieldRenderer` - text, email, url, tel, number, date, datetime-local, time, color
  - `TextareaFieldRenderer` - textarea, cke5
  - `SelectFieldRenderer` - select, colorselect, sqlselect
  - `CheckboxFieldRenderer` - checkbox
  - `MediaLinkFieldRenderer` - media, medialist, link, linklist
  - `CategoryFieldRenderer` - **NEU:** Hierarchische Kategorie-Auswahl
  - `CategoryListFieldRenderer` - **NEU:** Mehrfachauswahl von Kategorien

### Changed
- 🔄 **Refactoring:** `pages/config.php` - Alte `renderSettingField()` Funktion entfernt
- 🔄 **Refactoring:** Field Rendering erfolgt nun über `FieldRendererManager`
- 📝 **Dokumentation:** README mit Extensibility-Dokumentation aktualisiert
- 📝 **Dokumentation:** Neue Datei `EXTERNAL_FIELD_RENDERER_EXAMPLE.md` mit Beispielen

### Removed
- ❌ **ENTFERNT:** Feldtyp `banner_select` - Externe Addons müssen eigene Field Renderer registrieren
- ❌ **ENTFERNT:** Feldtyp `uikit_theme_select` - Externe Addons müssen eigene Field Renderer registrieren
- ❌ **ENTFERNT:** Klasse `BannerSelectWidget` - Durch Field Renderer System ersetzt
- ❌ **ENTFERNT:** Klasse `BannerSelectFieldRenderer` - Durch Extension Point System ersetzt

### Migration für externe Feldtypen

Wenn Sie `banner_select` verwenden, muss das `uikit_banner_design` Addon einen eigenen Field Renderer bereitstellen:

```php
// In boot.php des uikit_banner_design Addons
rex_extension::register('TEMPLATE_MANAGER_FIELD_RENDERERS', function($ep) {
    $renderers = $ep->getSubject();
    $renderers[] = new \UikitBannerDesign\TemplateManagerFieldRenderer();
    return $renderers;
});
```

Siehe [EXTERNAL_FIELD_RENDERER_EXAMPLE.md](EXTERNAL_FIELD_RENDERER_EXAMPLE.md) für vollständige Beispiele.

### Warum diese Änderungen?

**Problem:** `banner_select` und `uikit_theme_select` sind spezifisch für nicht-öffentliche, kommerzielle Addons:
- 🔒 Nicht frei verfügbar
- 🔒 Können nicht angefragt werden
- 🔒 Gehören nicht in den Core

**Lösung:** Extension Point System ermöglicht externen Addons ihre eigenen Feldtypen zu registrieren:
- ✅ Saubere Trennung
- ✅ Keine Core-Abhängigkeiten von proprietären Addons
- ✅ Erweiterbar für beliebige Addons
- ✅ Bessere Wartbarkeit

### API-Kompatibilität

Die bestehende Frontend-API bleibt **vollständig kompatibel**:

```php
// Funktioniert weiterhin wie gewohnt
TemplateManager::get('tm_company_name');
TemplateManager::getAll();
```

Nur die Implementierung der Field Renderer wurde modernisiert.

# Changelog

Alle wichtigen Änderungen am Template Manager werden in dieser Datei dokumentiert.

## [1.0.0] - Aktuell

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

# Beispiel: Externe Field Renderer

Diese Dateien zeigen wie externe Addons eigene Feldtypen für den Template Manager registrieren können.

## UIKit Banner Design Addon

**Datei:** `redaxo/src/addons/uikit_banner_design/boot.php`

```php
<?php

// Eigenen Field Renderer für Template Manager registrieren
if (rex_addon::get('template_manager')->isAvailable()) {
    rex_extension::register('TEMPLATE_MANAGER_FIELD_RENDERERS', function(rex_extension_point $ep) {
        $renderers = $ep->getSubject();
        $renderers[] = new \UikitBannerDesign\TemplateManagerFieldRenderer();
        return $renderers;
    });
}
```

**Datei:** `redaxo/src/addons/uikit_banner_design/lib/TemplateManagerFieldRenderer.php`

```php
<?php

namespace UikitBannerDesign;

use FriendsOfRedaxo\TemplateManager\AbstractFieldRenderer;

class TemplateManagerFieldRenderer extends AbstractFieldRenderer
{
    public function supports(string $type): bool
    {
        return $type === 'banner_select';
    }
    
    public function render(array $setting, string $value, string $name, int $clangId): string
    {
        $html = $this->renderFormGroupStart($setting);
        
        // Banner aus DB laden
        $sql = \rex_sql::factory();
        $banners = $sql->getArray('SELECT id, name FROM ' . \rex::getTable('uikit_banner_designs') . ' ORDER BY name ASC');
        
        $fieldId = 'banner_select_' . $clangId . '_' . preg_replace('/[^a-z0-9_]/i', '_', $setting['key']);
        
        $html .= '<select class="form-control" name="' . $name . '" id="' . $fieldId . '">';
        $html .= '<option value="">-- Kein Banner --</option>';
        
        foreach ($banners as $banner) {
            $selected = $value == $banner['id'] ? 'selected' : '';
            $html .= '<option value="' . (int)$banner['id'] . '" ' . $selected . '>';
            $html .= \rex_escape($banner['name']);
            $html .= '</option>';
        }
        
        $html .= '</select>';
        
        // Vorschau-Link
        if (!empty($value) && is_numeric($value)) {
            $previewUrl = \rex_url::backendPage('uikit_banner_design/preview', ['id' => (int)$value]);
            $html .= '<p class="help-block" data-preview-container>';
            $html .= '<a href="' . htmlspecialchars_decode($previewUrl) . '" target="_blank" class="btn btn-xs btn-default" style="margin-top: 5px;">';
            $html .= '<i class="rex-icon fa-eye"></i> Banner Vorschau';
            $html .= '</a>';
            $html .= '</p>';
        }
        
        // Live-Update JavaScript
        $html .= $this->renderScript('
            jQuery(function($) {
                var $select = $("#" + ' . json_encode($fieldId) . ' + "");
                $select.on("change", function() {
                    var bannerId = $(this).val();
                    var $container = $select.parent().find("[data-preview-container]");
                    
                    if (bannerId && bannerId !== "") {
                        var baseUrl = "' . htmlspecialchars_decode(\rex_url::backendPage('uikit_banner_design/preview')) . '";
                        var previewUrl = baseUrl + "&id=" + bannerId;
                        
                        if ($container.length === 0) {
                            $container = $("<p class=\\"help-block\\" data-preview-container></p>").insertAfter($select);
                        }
                        $container.html("<a href=\\"" + previewUrl + "\\" target=\\"_blank\\" class=\\"btn btn-xs btn-default\\" style=\\"margin-top: 5px;\\"><i class=\\"rex-icon fa-eye\\"></i> Banner Vorschau</a>");
                    } else {
                        $container.remove();
                    }
                });
            });
        ');
        
        $html .= $this->renderFormGroupEnd($setting);
        
        return $html;
    }
}
```

## Verwendung im Template

```php
/**
 * DOMAIN_SETTINGS
 * 
 * tm_header_banner: banner_select|Header Banner|5|Banner für den Kopfbereich
 * tm_footer_banner: banner_select|Footer Banner||Banner für den Fußbereich (optional)
 */
```

## Wichtig

Ab Template Manager 2.0 werden die integrierten Renderer für `banner_select` und `uikit_theme_select` entfernt.
Externe Addons müssen ihre eigenen Renderer wie oben gezeigt registrieren.

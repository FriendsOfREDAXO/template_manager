<?php

namespace FriendsOfRedaxo\TemplateManager\FieldRenderer;

use FriendsOfRedaxo\TemplateManager\AbstractFieldRenderer;

/**
 * Renderer für UIKit Banner Designer Auswahl
 * 
 * @deprecated Version 1.x - Wird in Version 2.0 entfernt!
 * 
 * Dieser Renderer ist nur temporär im Core enthalten, da das UIKit Banner Designer
 * Addon nicht frei verfügbar ist und nicht angefragt werden kann.
 * 
 * In Version 2.0 wird dieser Renderer entfernt. Das uikit_banner_design Addon sollte
 * stattdessen einen eigenen Renderer über den Extension Point TEMPLATE_MANAGER_FIELD_RENDERERS
 * registrieren.
 * 
 * @example Externes Addon (z.B. uikit_banner_design) sollte registrieren:
 * rex_extension::register('TEMPLATE_MANAGER_FIELD_RENDERERS', function($ep) {
 *     $renderers = $ep->getSubject();
 *     $renderers[] = new \UikitBannerDesign\TemplateManagerFieldRenderer();
 *     return $renderers;
 * });
 */
class BannerSelectFieldRenderer extends AbstractFieldRenderer
{
    public function supports(string $type): bool
    {
        return $type === 'banner_select';
    }
    
    public function render(array $setting, string $value, string $name, int $clangId): string
    {
        $html = $this->renderFormGroupStart($setting);
        
        // Deprecation Warning im Backend anzeigen
        $html .= '<div class="alert alert-warning" style="margin-bottom: 10px;">';
        $html .= '<i class="rex-icon fa-exclamation-triangle"></i> ';
        $html .= '<strong>Deprecated:</strong> Der Feldtyp <code>banner_select</code> wird in Template Manager 2.0 entfernt. ';
        $html .= 'Das UIKit Banner Design Addon sollte seinen eigenen Field Renderer registrieren.';
        $html .= '</div>';
        
        // Banner aus Datenbank laden
        $sql = \rex_sql::factory();
        
        try {
            $banners = $sql->getArray('SELECT id, name FROM ' . \rex::getTable('uikit_banner_designs') . ' ORDER BY name ASC');
        } catch (\Exception $e) {
            $html .= '<p class="text-danger"><i class="rex-icon fa-exclamation-triangle"></i> UIKit Banner Design Addon ist nicht installiert oder Tabelle fehlt.</p>';
            $html .= '<input type="hidden" name="' . $name . '" value="">';
            $html .= $this->renderFormGroupEnd($setting, false);
            return $html;
        }
        
        // Eindeutige ID ohne Sonderzeichen für JavaScript
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
        
        // Vorschau-Link wenn Banner ausgewählt
        if (!empty($value) && is_numeric($value)) {
            $previewUrl = \rex_url::backendPage('uikit_banner_design/preview', ['id' => (int)$value]);
            $html .= '<p class="help-block" data-preview-container>';
            $html .= '<a href="' . htmlspecialchars_decode($previewUrl) . '" target="_blank" class="btn btn-xs btn-default" style="margin-top: 5px;">';
            $html .= '<i class="rex-icon fa-eye"></i> Banner Vorschau';
            $html .= '</a>';
            $html .= '</p>';
        }
        
        // Live-Update für Vorschau-Link
        $html .= $this->renderScript('
            jQuery(function($) {
                var $select = $("#' . $fieldId . '");
                
                // Live-Update des Vorschau-Links
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

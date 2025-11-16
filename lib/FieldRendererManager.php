<?php

namespace FriendsOfRedaxo\TemplateManager;

use rex_addon;
use rex_extension;
use rex_extension_point;

/**
 * Field Renderer Manager
 * 
 * Verwaltet Field Renderer und ruft den passenden Renderer für einen Feldtyp auf
 */
class FieldRendererManager
{
    /**
     * @var FieldRendererInterface[]|null
     */
    private static ?array $renderers = null;
    
    /**
     * Initialisiert die Standard-Renderer
     * 
     * @return void
     */
    private static function initRenderers(): void
    {
        if (self::$renderers !== null) {
            return;
        }
        
        // Standard-Renderer registrieren
        self::$renderers = [
            new FieldRenderer\InputFieldRenderer(),
            new FieldRenderer\TextareaFieldRenderer(),
            new FieldRenderer\SelectFieldRenderer(),
            new FieldRenderer\CheckboxFieldRenderer(),
            new FieldRenderer\MediaLinkFieldRenderer(),
            
            // DEPRECATED - Wird in Version 2.0 entfernt
            new FieldRenderer\BannerSelectFieldRenderer(),
        ];
        
        // Extension Point: Externe Addons können eigene Renderer registrieren
        self::$renderers = rex_extension::registerPoint(new rex_extension_point(
            'TEMPLATE_MANAGER_FIELD_RENDERERS',
            self::$renderers,
            []
        ));
    }
    
    /**
     * Rendert ein Setting-Feld
     * 
     * @param array $setting Setting-Array mit keys: key, type, label, default, description, options
     * @param string $value Aktueller Wert
     * @param int $clangId Sprach-ID
     * @return string HTML
     */
    public static function renderField(array $setting, string $value, int $clangId): string
    {
        self::initRenderers();
        
        $name = 'settings[' . $clangId . '][' . $setting['key'] . ']';
        
        // Passenden Renderer finden
        foreach (self::$renderers as $renderer) {
            if ($renderer->supports($setting['type'])) {
                return $renderer->render($setting, $value, $name, $clangId);
            }
        }
        
        // Fallback: Unbekannter Feldtyp
        $addon = rex_addon::get('template_manager');
        
        $html = '<div class="form-group">';
        $html .= '<label class="control-label">' . \rex_escape($setting['label']) . '</label>';
        $html .= '<div class="alert alert-danger">';
        $html .= '<i class="rex-icon fa-exclamation-triangle"></i> ';
        $html .= 'Unbekannter Feldtyp: <code>' . \rex_escape($setting['type']) . '</code>';
        $html .= '</div>';
        $html .= '<p class="help-block">';
        $html .= 'Bitte prüfen Sie, ob ein Addon installiert werden muss, das diesen Feldtyp bereitstellt.';
        $html .= '</p>';
        $html .= '<input type="hidden" name="' . $name . '" value="">';
        $html .= '</div>';
        
        return $html;
    }
    
    /**
     * Cache zurücksetzen (für Tests)
     * 
     * @return void
     */
    public static function reset(): void
    {
        self::$renderers = null;
    }
}

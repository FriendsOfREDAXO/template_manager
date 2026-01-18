<?php

namespace FriendsOfRedaxo\TemplateManager;

/**
 * Banner Select Widget für Template Manager
 * Ermöglicht die Auswahl eines UIKit Banners aus dem Banner Designer
 */
class BannerSelectWidget
{
    /**
     * Rendert das Banner Select Widget
     *
     * @param string $key Feld-Key
     * @param array $config Widget-Konfiguration
     * @param mixed $value Aktueller Wert
     * @return string HTML-Output
     */
    public static function render(string $key, array $config, $value = null): string
    {
        // Prüfen ob Banner Designer Addon installiert ist
        if (!\rex_addon::get('uikit_banner_design')->isAvailable()) {
            return '<div class="alert alert-warning">' .
                   '<i class="fa fa-exclamation-triangle"></i> ' .
                   'UIKit Banner Design Addon nicht verfügbar' .
                   '</div>';
        }
        
        // Banner laden
        $sql = \rex_sql::factory();
        $banners = $sql->getArray('SELECT id, name FROM ' . \rex::getTable('uikit_banner_designs') . ' ORDER BY name ASC');
        
        $output = '';
        
        // Label
        $label = $config['label'] ?? 'Banner';
        $output .= '<label for="' . htmlspecialchars($key) . '">' . htmlspecialchars($label) . '</label>';
        
        // Beschreibung
        if (!empty($config['description'])) {
            $output .= '<p class="help-block">' . htmlspecialchars($config['description']) . '</p>';
        }
        
        // Select-Feld
        $output .= '<select class="form-control" id="' . htmlspecialchars($key) . '" name="' . htmlspecialchars($key) . '">';
        
        // Leere Option
        $output .= '<option value=""';
        if (empty($value)) {
            $output .= ' selected';
        }
        $output .= '>-- Kein Banner --</option>';
        
        // Banner-Optionen
        foreach ($banners as $banner) {
            $output .= '<option value="' . (int)$banner['id'] . '"';
            if ($value == $banner['id']) {
                $output .= ' selected';
            }
            $output .= '>' . htmlspecialchars($banner['name']) . '</option>';
        }
        
        $output .= '</select>';
        
        // Preview-Link wenn Banner ausgewählt
        if (!empty($value) && is_numeric($value)) {
            $previewUrl = \rex_url::backendPage('uikit_banner_design/preview', ['id' => $value]);
            $output .= '<p class="help-block">';
            $output .= '<a href="' . $previewUrl . '" target="_blank" class="btn btn-xs btn-default">';
            $output .= '<i class="fa fa-eye"></i> Vorschau anzeigen';
            $output .= '</a>';
            $output .= '</p>';
        }
        
        return '<div class="form-group">' . $output . '</div>';
    }
}

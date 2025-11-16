<?php

namespace FriendsOfRedaxo\TemplateManager;

use rex_addon;

/**
 * Abstrakte Basisklasse für Field Renderer
 * 
 * Bietet Hilfsmethoden für die Implementierung von Field Renderern
 */
abstract class AbstractFieldRenderer implements FieldRendererInterface
{
    /**
     * Erstellt den öffnenden <div class="form-group"> Tag mit Label
     * 
     * @param array $setting Setting-Array
     * @return string HTML
     */
    protected function renderFormGroupStart(array $setting): string
    {
        $html = '<div class="form-group">';
        $html .= '<label class="control-label">';
        $html .= \rex_escape($setting['label']);
        
        if (!empty($setting['description'])) {
            $html .= ' <small class="text-muted">(' . \rex_escape($setting['description']) . ')</small>';
        }
        
        $html .= '</label>';
        
        return $html;
    }
    
    /**
     * Erstellt den schließenden </div> Tag und optional einen Default-Hinweis
     * 
     * @param array $setting Setting-Array
     * @param bool $showDefault Ob Default-Wert angezeigt werden soll
     * @return string HTML
     */
    protected function renderFormGroupEnd(array $setting, bool $showDefault = true): string
    {
        $html = '';
        
        if ($showDefault && !empty($setting['default'])) {
            $addon = \rex_addon::get('template_manager');
            $html .= '<p class="help-block">';
            $html .= $addon->i18n('template_manager_default_value') . ': ' . \rex_escape($setting['default']);
            $html .= '</p>';
        }
        
        $html .= '</div>';
        
        return $html;
    }
    
    /**
     * Erstellt ein <script> Tag mit nonce
     * 
     * @param string $js JavaScript Code (ohne <script> Tags)
     * @return string HTML
     */
    protected function renderScript(string $js): string
    {
        return '<script nonce="' . \rex_response::getNonce() . '">' . $js . '</script>';
    }
    
    /**
     * Prüft ob ein Addon verfügbar ist
     * 
     * @param string $addonName Name des Addons
     * @return bool
     */
    protected function isAddonAvailable(string $addonName): bool
    {
        return \rex_addon::exists($addonName) && \rex_addon::get($addonName)->isAvailable();
    }
    
    /**
     * Rendert eine Fehlermeldung wenn ein Addon nicht verfügbar ist
     * 
     * @param string $addonName Name des Addons
     * @param string $name Feld-Name für hidden input
     * @return string HTML
     */
    protected function renderAddonNotAvailable(string $addonName, string $name): string
    {
        $html = '<p class="text-warning">';
        $html .= '<i class="rex-icon fa-exclamation-triangle"></i> ';
        $html .= 'Das Addon "' . \rex_escape($addonName) . '" ist nicht installiert oder nicht verfügbar.';
        $html .= '</p>';
        $html .= '<input type="hidden" name="' . $name . '" value="">';
        
        return $html;
    }
}

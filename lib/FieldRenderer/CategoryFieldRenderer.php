<?php

namespace FriendsOfRedaxo\TemplateManager\FieldRenderer;

use FriendsOfRedaxo\TemplateManager\AbstractFieldRenderer;

/**
 * Renderer für Category Select Feld
 * 
 * Verwendet rex_category_select für hierarchische Kategorie-Auswahl
 * mit korrekter Struktur-Darstellung (Einrückung, IDs, Permissions)
 */
class CategoryFieldRenderer extends AbstractFieldRenderer
{
    public function supports(string $type): bool
    {
        return $type === 'category';
    }
    
    public function render(array $setting, string $value, string $name, int $clangId): string
    {
        $html = $this->renderFormGroupStart($setting);
        
        // rex_category_select instanziieren
        // Parameter: $ignoreOfflines, $clang, $checkPerms, $addHomepage
        $categorySelect = new \rex_category_select(
            false,  // ignoreOfflines - zeige auch offline Kategorien
            $clangId, // aktuelle Sprache
            true,   // checkPerms - Berechtigungen prüfen
            true    // addHomepage - "Homepage" Option hinzufügen
        );
        
        $fieldId = 'category-' . $clangId . '-' . preg_replace('/[^a-z0-9_]/i', '_', $setting['key']);
        
        $categorySelect->setName($name);
        $categorySelect->setId($fieldId);
        $categorySelect->setSize(1);
        $categorySelect->setAttribute('class', 'form-control selectpicker');
        $categorySelect->setAttribute('data-live-search', 'true');
        $categorySelect->setAttribute('data-size', '15');
        
        // Wert vorauswählen
        if (!empty($value) && is_numeric($value)) {
            $categorySelect->setSelected((int)$value);
        }
        
        $html .= $categorySelect->get();
        
        // Selectpicker initialisieren
        $html .= $this->renderScript('
            jQuery(function($) {
                $("#' . $fieldId . '").selectpicker("refresh");
            });
        ');
        
        $html .= $this->renderFormGroupEnd($setting);
        
        return $html;
    }
}

<?php

namespace FriendsOfRedaxo\TemplateManager\FieldRenderer;

use FriendsOfRedaxo\TemplateManager\AbstractFieldRenderer;

/**
 * Renderer für CategoryList Select Feld
 * 
 * Verwendet rex_category_select für Mehrfachauswahl von Kategorien
 * mit korrekter hierarchischer Struktur-Darstellung
 */
class CategoryListFieldRenderer extends AbstractFieldRenderer
{
    public function supports(string $type): bool
    {
        return $type === 'categorylist';
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
        
        $fieldId = 'categorylist-' . $clangId . '-' . preg_replace('/[^a-z0-9_]/i', '_', $setting['key']);
        
        $categorySelect->setName($name . '[]');
        $categorySelect->setId($fieldId);
        $categorySelect->setMultiple(true);
        $categorySelect->setSize(10);
        $categorySelect->setAttribute('class', 'form-control selectpicker');
        $categorySelect->setAttribute('data-live-search', 'true');
        $categorySelect->setAttribute('data-size', '15');
        $categorySelect->setAttribute('data-actions-box', 'true');
        
        // Werte vorauswählen (komma-separierte Liste)
        if (!empty($value)) {
            $selectedIds = array_filter(array_map('trim', explode(',', $value)));
            foreach ($selectedIds as $id) {
                if (is_numeric($id)) {
                    $categorySelect->setSelected((int)$id);
                }
            }
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

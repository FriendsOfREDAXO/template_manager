<?php

namespace FriendsOfRedaxo\TemplateManager\FieldRenderer;

use FriendsOfRedaxo\TemplateManager\AbstractFieldRenderer;

/**
 * Renderer für Select, Colorselect und SQL-Select Felder
 */
class SelectFieldRenderer extends AbstractFieldRenderer
{
    public function supports(string $type): bool
    {
        return in_array($type, ['select', 'colorselect', 'sqlselect'], true);
    }
    
    public function render(array $setting, string $value, string $name, int $clangId): string
    {
        $html = $this->renderFormGroupStart($setting);
        
        if ($setting['type'] === 'sqlselect') {
            $html .= $this->renderSqlSelect($setting, $value, $name);
        } elseif ($setting['type'] === 'colorselect') {
            $html .= $this->renderColorSelect($setting, $value, $name);
        } else {
            $html .= $this->renderStandardSelect($setting, $value, $name);
        }
        
        $html .= $this->renderFormGroupEnd($setting);
        
        return $html;
    }
    
    private function renderStandardSelect(array $setting, string $value, string $name): string
    {
        if (!isset($setting['options']) || !is_array($setting['options'])) {
            return '<p class="text-danger"><i class="rex-icon fa-exclamation-triangle"></i> Keine Optionen definiert.</p>'
                 . '<input type="hidden" name="' . $name . '" value="">';
        }
        
        $html = '<select class="form-control" name="' . $name . '">';
        
        foreach ($setting['options'] as $optValue => $optLabel) {
            $selected = $optValue === $value ? 'selected' : '';
            $html .= '<option value="' . \rex_escape($optValue) . '" ' . $selected . '>';
            $html .= \rex_escape($optLabel);
            $html .= '</option>';
        }
        
        $html .= '</select>';
        
        return $html;
    }
    
    private function renderColorSelect(array $setting, string $value, string $name): string
    {
        if (!isset($setting['options']) || !is_array($setting['options'])) {
            return '<p class="text-danger"><i class="rex-icon fa-exclamation-triangle"></i> Keine Farb-Optionen definiert.</p>'
                 . '<input type="hidden" name="' . $name . '" value="">';
        }
        
        $html = '<select class="form-control selectpicker" name="' . $name . '" data-size="10">';
        
        foreach ($setting['options'] as $colorValue => $colorLabel) {
            $selected = $colorValue === $value ? 'selected' : '';
            
            // Validiere Farbwert (Hex, RGB, RGBA oder CSS Color Names)
            $escapedColor = \rex_escape($colorValue);
            if (!preg_match('/^(#[0-9a-f]{3,8}|rgb|rgba|hsl|hsla|[a-z]+)$/i', $colorValue)) {
                $escapedColor = 'transparent'; // Fallback für ungültige Werte
            }
            
            // Farbiges Badge für visuelle Darstellung
            $badge = '<span style="display:inline-block;width:16px;height:16px;border-radius:3px;background:' . $escapedColor . ';margin-right:8px;border:1px solid rgba(0,0,0,0.15);vertical-align:middle;"></span>';
            
            $html .= '<option value="' . \rex_escape($colorValue) . '" ' . $selected . ' data-content="' . \rex_escape($badge . $colorLabel) . '">';
            $html .= \rex_escape($colorLabel);
            $html .= '</option>';
        }
        
        $html .= '</select>';
        
        // Selectpicker initialisieren - Name muss für jQuery-Selektor escaped werden
        $escapedName = preg_replace('/([\\[\\]])/', '\\\\\\\\$1', $name);
        $html .= $this->renderScript('
            jQuery(function($) {
                $("select[name=\'' . $escapedName . '\']").selectpicker("refresh");
            });
        ');
        
        return $html;
    }
    
    private function renderSqlSelect(array $setting, string $value, string $name): string
    {
        $sqlQuery = $setting['options']['_sql_query'] ?? '';
        
        if (empty($sqlQuery)) {
            return '<p class="text-warning"><i class="rex-icon fa-exclamation-triangle"></i> Keine SQL-Query definiert.</p>'
                 . '<input type="hidden" name="' . $name . '" value="">';
        }
        
        // Sicherheitscheck: Nur SELECT-Queries erlauben
        if (!preg_match('/^\s*SELECT\s+/i', $sqlQuery)) {
            return '<p class="text-danger"><i class="rex-icon fa-exclamation-triangle"></i> Ungültige Query: Nur SELECT-Statements sind erlaubt.</p>'
                 . '<input type="hidden" name="' . $name . '" value="">';
        }
        
        try {
            $sql = \rex_sql::factory();
            $sql->setQuery($sqlQuery);
            
            $html = '<select class="form-control selectpicker" name="' . $name . '" data-live-search="true" data-size="10">';
            $html .= '<option value="">-- Bitte wählen --</option>';
            
            for ($i = 0; $i < $sql->getRows(); $i++) {
                $row = $sql->getRow();
                
                // Erwartet: id und name Spalten (oder erstes und zweites Feld)
                $optValue = $row['id'] ?? $row[array_key_first($row)] ?? '';
                $optLabel = $row['name'] ?? $row[array_key_last($row)] ?? $optValue;
                
                $selected = $optValue == $value ? 'selected' : '';
                
                $html .= '<option value="' . \rex_escape($optValue) . '" ' . $selected . '>';
                $html .= \rex_escape($optLabel);
                $html .= '</option>';
                
                $sql->next();
            }
            
            $html .= '</select>';
            
            // Selectpicker initialisieren - Name muss für jQuery-Selektor escaped werden
            $escapedName = preg_replace('/([\\[\\]])/', '\\\\\\\\$1', $name);
            $html .= $this->renderScript('
                jQuery(function($) {
                    $("select[name=\'' . $escapedName . '\']").selectpicker("refresh");
                });
            ');
            
            return $html;
            
        } catch (\Exception $e) {
            return '<p class="text-danger"><i class="rex-icon fa-exclamation-triangle"></i> SQL-Fehler: ' . \rex_escape($e->getMessage()) . '</p>'
                 . '<input type="hidden" name="' . $name . '" value="">';
        }
    }
}

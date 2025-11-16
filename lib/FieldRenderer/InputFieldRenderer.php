<?php

namespace FriendsOfRedaxo\TemplateManager\FieldRenderer;

use FriendsOfRedaxo\TemplateManager\AbstractFieldRenderer;

/**
 * Renderer für Standard HTML5 Input-Felder
 * 
 * Unterstützt: text, email, url, tel, number, date, datetime-local, time, color
 */
class InputFieldRenderer extends AbstractFieldRenderer
{
    private const SUPPORTED_TYPES = [
        'text', 'email', 'url', 'tel', 'number', 
        'date', 'datetime-local', 'time', 'color'
    ];
    
    public function supports(string $type): bool
    {
        return in_array($type, self::SUPPORTED_TYPES, true);
    }
    
    public function render(array $setting, string $value, string $name, int $clangId): string
    {
        $html = $this->renderFormGroupStart($setting);
        
        $inputType = $setting['type'];
        $extraAttrs = '';
        
        if ($inputType === 'number') {
            $extraAttrs = ' step="any"'; // Erlaubt Dezimalzahlen
        }
        
        if ($inputType === 'color') {
            // Color Picker mit Text-Anzeige
            $html .= '<div class="input-group">';
            $html .= '<input type="color" class="form-control" name="' . $name . '" value="' . \rex_escape($value) . '" style="max-width: 80px;">';
            $html .= '<input type="text" class="form-control" value="' . \rex_escape($value) . '" readonly style="font-family: monospace;">';
            $html .= '</div>';
            $html .= $this->renderScript('
                jQuery(function($) {
                    $("input[name=" + ' . json_encode($name) . ' + "]").on("change", function() {
                        $(this).next("input").val($(this).val());
                    });
                });
            ');
        } else {
            $html .= '<input type="' . $inputType . '" class="form-control" name="' . $name . '" value="' . \rex_escape($value) . '"' . $extraAttrs . '>';
        }
        
        $html .= $this->renderFormGroupEnd($setting);
        
        return $html;
    }
}

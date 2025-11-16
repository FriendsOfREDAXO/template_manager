<?php

namespace FriendsOfRedaxo\TemplateManager\FieldRenderer;

use FriendsOfRedaxo\TemplateManager\AbstractFieldRenderer;

/**
 * Renderer für Checkbox Felder
 */
class CheckboxFieldRenderer extends AbstractFieldRenderer
{
    public function supports(string $type): bool
    {
        return $type === 'checkbox';
    }
    
    public function render(array $setting, string $value, string $name, int $clangId): string
    {
        $html = $this->renderFormGroupStart($setting);
        
        $checked = $value === '1' || $value === 'true' ? 'checked' : '';
        
        $html .= '<div class="checkbox">';
        $html .= '<label>';
        $html .= '<input type="hidden" name="' . $name . '" value="0">';
        $html .= '<input type="checkbox" name="' . $name . '" value="1" ' . $checked . '>';
        $html .= ' ' . \rex_escape($setting['description']);
        $html .= '</label>';
        $html .= '</div>';
        
        $html .= $this->renderFormGroupEnd($setting, false);
        
        return $html;
    }
}

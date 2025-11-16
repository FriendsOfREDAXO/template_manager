<?php

namespace FriendsOfRedaxo\TemplateManager\FieldRenderer;

use FriendsOfRedaxo\TemplateManager\AbstractFieldRenderer;

/**
 * Renderer für Textarea und CKE5 WYSIWYG Editor
 */
class TextareaFieldRenderer extends AbstractFieldRenderer
{
    public function supports(string $type): bool
    {
        return in_array($type, ['textarea', 'cke5'], true);
    }
    
    public function render(array $setting, string $value, string $name, int $clangId): string
    {
        $html = $this->renderFormGroupStart($setting);
        
        if ($setting['type'] === 'cke5' && $this->isAddonAvailable('cke5')) {
            // CKE5 WYSIWYG Editor
            $profile = !empty($setting['default']) ? $setting['default'] : 'default';
            $userLang = \Cke5\Utils\Cke5Lang::getUserLang();
            $contentLang = \Cke5\Utils\Cke5Lang::getOutputLang();
            
            $html .= '<textarea class="form-control cke5-editor" ';
            $html .= 'data-profile="' . \rex_escape($profile) . '" ';
            $html .= 'data-lang="' . \rex_escape($userLang) . '" ';
            $html .= 'data-content-lang="' . \rex_escape($contentLang) . '" ';
            $html .= 'name="' . $name . '">';
            $html .= \rex_escape($value);
            $html .= '</textarea>';
        } elseif ($setting['type'] === 'cke5') {
            // Fallback zu normalem Textarea wenn CKE5 nicht verfügbar
            $html .= '<textarea class="form-control" name="' . $name . '" rows="8">';
            $html .= \rex_escape($value);
            $html .= '</textarea>';
            $html .= '<p class="text-muted small"><i class="rex-icon fa-info-circle"></i> CKE5 Addon nicht verfügbar - Fallback zu einfachem Textarea.</p>';
        } else {
            // Normales Textarea
            $html .= '<textarea class="form-control" name="' . $name . '" rows="4">';
            $html .= \rex_escape($value);
            $html .= '</textarea>';
        }
        
        $html .= $this->renderFormGroupEnd($setting);
        
        return $html;
    }
}

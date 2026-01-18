<?php

namespace FriendsOfRedaxo\TemplateManager\FieldRenderer;

use FriendsOfRedaxo\TemplateManager\AbstractFieldRenderer;
use rex_escape;
use rex_var_link;

/**
 * Renderer für Article Select Feld
 * 
 * Nutzt die REDAXO Linkmap für Artikel-Auswahl
 */
class ArticleFieldRenderer extends AbstractFieldRenderer
{
    /** @var int */
    private static $linkCounter = 0;

    public function supports(string $type): bool
    {
        return $type === 'article';
    }
    
    public function render(array $setting, string $value, string $name, int $clangId): string
    {
        $html = $this->renderFormGroupStart($setting);
        
        // Widget-Counter für eindeutige IDs
        ++self::$linkCounter;
        $widgetId = 'REX_LINK_' . self::$linkCounter;
        
        // Nutze rex_var_link::getWidget() wie yform_content_builder und structure addon
        $html .= rex_var_link::getWidget(self::$linkCounter, $name, $value);
        
        $html .= $this->renderFormGroupEnd($setting);
        
        return $html;
    }
}

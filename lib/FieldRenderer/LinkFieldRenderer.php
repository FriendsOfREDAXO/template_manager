<?php

namespace FriendsOfRedaxo\TemplateManager\FieldRenderer;

use FriendsOfRedaxo\TemplateManager\AbstractFieldRenderer;

/**
 * Renderer für REDAXO Link Widgets
 * 
 * Unterstützt: link, linklist, article (Alias für link)
 */
class LinkFieldRenderer extends AbstractFieldRenderer
{
    private static int $linkCounter = 0;
    private static int $linklistCounter = 0;
    
    public function supports(string $type): bool
    {
        return in_array($type, ['link', 'linklist', 'article'], true);
    }
    
    public function render(array $setting, string $value, string $name, int $clangId): string
    {
        $html = $this->renderFormGroupStart($setting);
        
        switch ($setting['type']) {
            case 'link':
            case 'article':  // Alias für link
                self::$linkCounter++;
                $html .= \rex_var_link::getWidget(self::$linkCounter, $name, $value, []);
                break;
                
            case 'linklist':
                self::$linklistCounter++;
                $html .= \rex_var_linklist::getWidget(self::$linklistCounter, $name, $value, []);
                break;
        }
        
        $html .= $this->renderFormGroupEnd($setting);
        
        return $html;
    }
}

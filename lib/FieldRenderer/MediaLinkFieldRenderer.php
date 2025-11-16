<?php

namespace FriendsOfRedaxo\TemplateManager\FieldRenderer;

use FriendsOfRedaxo\TemplateManager\AbstractFieldRenderer;

/**
 * Renderer für REDAXO Media/Link Widgets
 * 
 * Unterstützt: media, medialist, link, linklist
 */
class MediaLinkFieldRenderer extends AbstractFieldRenderer
{
    private static int $mediaCounter = 0;
    private static int $medialistCounter = 0;
    private static int $linkCounter = 0;
    private static int $linklistCounter = 0;
    
    public function supports(string $type): bool
    {
        return in_array($type, ['media', 'medialist', 'link', 'linklist'], true);
    }
    
    public function render(array $setting, string $value, string $name, int $clangId): string
    {
        $html = $this->renderFormGroupStart($setting);
        
        switch ($setting['type']) {
            case 'media':
                self::$mediaCounter++;
                $html .= \rex_var_media::getWidget(self::$mediaCounter, $name, $value, []);
                break;
                
            case 'medialist':
                self::$medialistCounter++;
                $html .= \rex_var_medialist::getWidget(self::$medialistCounter, $name, $value, []);
                break;
                
            case 'link':
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

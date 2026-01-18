<?php

namespace FriendsOfRedaxo\TemplateManager\FieldRenderer;

use FriendsOfRedaxo\TemplateManager\AbstractFieldRenderer;

/**
 * Renderer für REDAXO Media Widgets
 * 
 * Unterstützt: media, medialist
 */
class MediaFieldRenderer extends AbstractFieldRenderer
{
    private static int $mediaCounter = 0;
    private static int $medialistCounter = 0;
    
    public function supports(string $type): bool
    {
        return in_array($type, ['media', 'medialist'], true);
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
        }
        
        $html .= $this->renderFormGroupEnd($setting);
        
        return $html;
    }
}

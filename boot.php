<?php

use FriendsOfRedaxo\TemplateManager\TemplateManager;

// Autoload für Template Manager Klassen
rex_autoload::addDirectory(__DIR__ . '/lib/');

// Template Settings sind direkt über die Klasse verfügbar:
// TemplateManager::get('tm_company_name')
// Keine weitere Initialisierung nötig im Frontend


// Extension Point: Template-Liste im Backend erweitern
if (rex::isBackend()) {
    rex_extension::register('PACKAGES_INCLUDED', function() {
        if (rex_be_controller::getCurrentPage() === 'templates') {
            rex_extension::register('OUTPUT_FILTER', function(rex_extension_point $ep) {
                $content = $ep->getSubject();
                
                // Template-Zeilen mit tm_ Key finden und Key klickbar machen
                $content = preg_replace_callback(
                    '#(<td data-title="Schlüssel">)(tm_\w+)(</td>.*?template_id=)(\d+)#s',
                    function($matches) {
                        $openingTag = $matches[1];  // '<td data-title="Schlüssel">'
                        $templateKey = $matches[2]; // 'tm_whatever'
                        $middlePart = $matches[3];  // '</td>...template_id='
                        $templateId = $matches[4];  // '123'
                        
                        // Nur den Key-Teil mit Link ersetzen, Rest bleibt gleich
                        $url = rex_url::backendPage('template_manager/config', ['template_id' => $templateId]);
                        $linkedKey = '<a href="' . $url . '" ' .
                            'title="Template Manager Einstellungen konfigurieren">' .
                            '<i class="rex-icon fa-cog"></i> ' .
                            htmlspecialchars($templateKey) . 
                            '</a>';
                        
                        return $openingTag . $linkedKey . $middlePart . $templateId;
                    },
                    $content
                );
                
                return $content;
            }, rex_extension::LATE);
        }
    });
}

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
                    '#<td data-title="Schlüssel">(tm_\w+)</td>.*?template_id=(\d+)#s',
                    function($matches) {
                        $templateKey = $matches[1];
                        $templateId = $matches[2];
                        
                        // Key mit Config-Link + Icon ersetzen
                        $linkedKey = '<td data-title="Schlüssel">' .
                            '<a href="index.php?page=template_manager/config&template_id=' . $templateId . '" ' .
                            'title="Template Manager Einstellungen konfigurieren">' .
                            '<i class="rex-icon fa-cog" title="Template Manager Einstellungen"></i> ' .
                            htmlspecialchars($templateKey) . 
                            '</a>' .
                            '</td>';
                        
                        return $linkedKey . substr($matches[0], strlen($matches[1]) + 29); // 29 = Länge von '<td data-title="Schlüssel">' + '</td>'
                    },
                    $content
                );
                
                return $content;
            }, rex_extension::LATE);
        }
    });
}

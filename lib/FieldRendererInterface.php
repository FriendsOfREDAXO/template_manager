<?php

namespace FriendsOfRedaxo\TemplateManager;

/**
 * Interface für Template Manager Field Renderer
 * 
 * Externe Addons können eigene Feldtypen registrieren, indem sie
 * dieses Interface implementieren und über den Extension Point
 * TEMPLATE_MANAGER_FIELD_RENDERERS registrieren.
 * 
 * @example
 * rex_extension::register('TEMPLATE_MANAGER_FIELD_RENDERERS', function($ep) {
 *     $renderers = $ep->getSubject();
 *     $renderers[] = new MyCustomFieldRenderer();
 *     return $renderers;
 * });
 */
interface FieldRendererInterface
{
    /**
     * Prüft ob dieser Renderer den gegebenen Feldtyp unterstützt
     * 
     * @param string $type Feldtyp (z.B. 'text', 'select', 'my_custom_field')
     * @return bool True wenn dieser Renderer den Typ unterstützt
     */
    public function supports(string $type): bool;
    
    /**
     * Rendert das Formular-Feld als HTML
     * 
     * @param array $setting Setting-Array mit keys: key, type, label, default, description, options
     * @param string $value Aktueller Wert des Felds
     * @param string $name HTML name-Attribut (bereits mit clang_id verschachtelt)
     * @param int $clangId Aktuelle Sprach-ID
     * @return string HTML-Code für das Formular-Feld
     */
    public function render(array $setting, string $value, string $name, int $clangId): string;
}

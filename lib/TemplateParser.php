<?php

namespace FriendsOfRedaxo\TemplateManager;

use rex;
use rex_sql;

/**
 * Template Parser
 * 
 * Liest DOMAIN_SETTINGS aus Template-DocBlocks
 */
class TemplateParser
{
    /**
     * Parst DOMAIN_SETTINGS aus Template-Content
     * 
     * @param string $templateContent Template PHP-Code
     * @return array Geparste Settings
     */
    public static function parseSettings(string $templateContent): array
    {
        $settings = [];
        
        // DocBlock extrahieren
        if (preg_match('#/\*\*.*?DOMAIN_SETTINGS.*?\*/#s', $templateContent, $matches)) {
            $docBlock = $matches[0];
            
            // Setting-Zeilen extrahieren
            // Format: * feldname: typ|Label|Standardwert|Beschreibung
            // Unterstützte Typen: text, textarea, email, url, media, select, checkbox, link, linklist
            // WICHTIG: Nur Felder mit tm_ Prefix werden geparst!
            preg_match_all('#\*\s+(tm_\w+):\s*([^|]+)\|([^|]*)\|([^|]*)\|(.*)$#m', $docBlock, $settingMatches, PREG_SET_ORDER);
            
            foreach ($settingMatches as $match) {
                $key = trim($match[1]);
                $type = trim($match[2]);
                $label = trim($match[3]);
                $defaultValue = trim($match[4]);
                $description = trim($match[5]);
                
                $settings[$key] = [
                    'key' => $key,
                    'type' => $type,
                    'label' => $label,
                    'default' => $defaultValue,
                    'description' => $description,
                    'options' => self::parseOptions($type, $defaultValue)
                ];
            }
        }
        
        return $settings;
    }
    
    /**
     * Parst Optionen aus Select-Feldern
     * 
     * @param string $type Feldtyp
     * @param string $defaultValue Default-Wert (enthält Optionen bei select)
     * @return array|null Optionen oder null
     */
    private static function parseOptions(string $type, string $defaultValue): ?array
    {
        if ($type !== 'select') {
            return null;
        }
        
        // Bei select ist der "default" Teil die Optionsliste
        // Format: wert1,wert2,wert3 oder wert1:Label 1,wert2:Label 2
        $optionsPart = $defaultValue;
        
        if (empty($optionsPart)) {
            return [];
        }
        
        $options = [];
        $parts = explode(',', $optionsPart);
        
        foreach ($parts as $part) {
            $part = trim($part);
            
            if (str_contains($part, ':')) {
                // Format: wert:Label
                [$value, $label] = explode(':', $part, 2);
                $options[trim($value)] = trim($label);
            } else {
                // Format: wert (Label = Wert)
                $options[$part] = $part;
            }
        }
        
        return $options;
    }
    
    /**
     * Holt alle Templates mit ihren Settings
     * 
     * @return array Templates mit geparsten Settings
     */
    public static function getAllTemplates(): array
    {
        $templates = [];
        
        $sql = rex_sql::factory();
        $sql->setQuery('SELECT id, name, content FROM ' . rex::getTable('template') . ' ORDER BY name');
        
        foreach ($sql->getArray() as $row) {
            $settings = self::parseSettings($row['content']);
            
            // Nur Templates mit DOMAIN_SETTINGS aufnehmen
            if (!empty($settings)) {
                $templates[] = [
                    'id' => $row['id'],
                    'name' => $row['name'],
                    'settings' => $settings
                ];
            }
        }
        
        return $templates;
    }
    
    /**
     * Holt Settings für ein spezifisches Template
     * 
     * @param int $templateId Template-ID
     * @return array|null Settings oder null wenn nicht gefunden
     */
    public static function getTemplateSettings(int $templateId): ?array
    {
        $sql = rex_sql::factory();
        $sql->setQuery('SELECT content FROM ' . rex::getTable('template') . ' WHERE id = ?', [$templateId]);
        
        if ($sql->getRows() === 0) {
            return null;
        }
        
        return self::parseSettings($sql->getValue('content'));
    }
}

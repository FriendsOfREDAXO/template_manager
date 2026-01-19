<?php

namespace FriendsOfRedaxo\TemplateManager;

use rex;
use rex_sql;

/**
 * Template Parser.
 *
 * Liest DOMAIN_SETTINGS aus Template-DocBlocks
 */
class TemplateParser
{
    /**
     * Parst DOMAIN_SETTINGS aus Template-Content mit Gruppen-Unterstützung.
     *
     * @param string $templateContent Template PHP-Code
     * @return array Geparste Settings mit Gruppen
     */
    public static function parseSettings(string $templateContent): array
    {
        $settings = [];
        $groups = [];
        $currentGroup = null;

        // DocBlock extrahieren
        if (preg_match('#/\*\*.*?DOMAIN_SETTINGS.*?\*/#s', $templateContent, $matches)) {
            $docBlock = $matches[0];

            // Zeilen durchgehen und nach Gruppen + Settings suchen
            $lines = explode("\n", $docBlock);

            foreach ($lines as $line) {
                // Gruppen-Header erkennen:
                // * --- Gruppenname ---
                // * --- Gruppenname [fa-solid fa-icon] ---
                // * --- Gruppenname [fa-solid fa-icon] [admin,editor] ---
                // * --- Gruppenname [admin,editor] ---
                if (preg_match('#\*\s+---\s+(.+?)\s+(?:\[(fa-[^\]]+)\]\s+)?(?:\[([^\]]+)\]\s+)?---#', $line, $groupMatch)) {
                    $groupName = trim($groupMatch[1]);
                    $icon = isset($groupMatch[2]) ? trim($groupMatch[2]) : '';
                    $rolesString = $groupMatch[3] ?? '';

                    // Prüfe ob rolesString ein Font Awesome Icon ist (falls keine roles angegeben)
                    // Falls nur ein [xxx] vorhanden ist, könnte es Icon ODER Roles sein
                    if (empty($icon) && !empty($rolesString) && str_starts_with($rolesString, 'fa-')) {
                        $icon = $rolesString;
                        $rolesString = '';
                    }

                    $roles = !empty($rolesString) ? array_map('trim', explode(',', $rolesString)) : [];

                    $currentGroup = $groupName;
                    $groups[$currentGroup] = [
                        'name' => $groupName,
                        'icon' => $icon,
                        'roles' => $roles, // Leeres Array = für alle sichtbar
                        'fields' => [],
                    ];
                    continue;
                }

                // Setting-Zeilen extrahieren
                // Format: * feldname: typ|Label|Standardwert|Beschreibung
                // WICHTIG: Nur Felder mit tm_ Prefix werden geparst!
                if (preg_match('#\*\s+(tm_\w+):\s*([^|]+)\|([^|]*)\|([^|]*)\|(.*)$#', $line, $match)) {
                    $key = trim($match[1]);
                    $type = trim($match[2]);
                    $label = trim($match[3]);
                    $defaultValue = trim($match[4]);
                    $description = trim($match[5]);

                    $setting = [
                        'key' => $key,
                        'type' => $type,
                        'label' => $label,
                        'default' => $defaultValue,
                        'description' => $description,
                        'options' => self::parseOptions($type, $defaultValue),
                        'group' => $currentGroup,
                    ];

                    $settings[$key] = $setting;

                    // Zu aktueller Gruppe hinzufügen
                    if ($currentGroup && isset($groups[$currentGroup])) {
                        $groups[$currentGroup]['fields'][] = $key;
                    }
                }
            }
        }

        return [
            'settings' => $settings,
            'groups' => $groups,
        ];
    }

    /**
     * Parst Optionen aus Select-Feldern.
     *
     * @param string $type Feldtyp
     * @param string $defaultValue Default-Wert (enthält Optionen bei select oder SQL bei sqlselect)
     * @return array|null Optionen oder null
     */
    private static function parseOptions(string $type, string $defaultValue): ?array
    {
        if ('sqlselect' === $type) {
            // Bei sqlselect ist der default-Wert die SQL-Query
            // Wird später in renderSettingField() ausgeführt
            return ['_sql_query' => $defaultValue];
        }

        if ('select' !== $type && 'colorselect' !== $type) {
            return null;
        }

        // Bei select/colorselect ist der "default" Teil die Optionsliste
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
     * Holt alle Templates mit ihren Settings.
     *
     * @return array Templates mit geparsten Settings
     */
    public static function getAllTemplates(): array
    {
        $templates = [];

        $sql = rex_sql::factory();
        $sql->setQuery('SELECT id, name, content FROM ' . rex::getTable('template') . ' ORDER BY name');

        foreach ($sql->getArray() as $row) {
            $parsed = self::parseSettings($row['content']);

            // Nur Templates mit DOMAIN_SETTINGS aufnehmen
            if (!empty($parsed['settings'])) {
                $templates[] = [
                    'id' => $row['id'],
                    'name' => $row['name'],
                    'settings' => $parsed['settings'],
                    'groups' => $parsed['groups'],
                ];
            }
        }

        return $templates;
    }

    /**
     * Holt Settings für ein spezifisches Template.
     *
     * @param int $templateId Template-ID
     * @return array|null Settings oder null wenn nicht gefunden
     */
    public static function getTemplateSettings(int $templateId): ?array
    {
        $sql = rex_sql::factory();
        $sql->setQuery('SELECT content FROM ' . rex::getTable('template') . ' WHERE id = ?', [$templateId]);

        if (0 === $sql->getRows()) {
            return null;
        }

        return self::parseSettings($sql->getValue('content'));
    }
}

<?php

namespace FriendsOfRedaxo\TemplateManager;

use rex;
use rex_clang;
use rex_sql;

/**
 * Global Variables
 *
 * Verwaltung von template- und domain-übergreifenden globalen Variablen.
 * Diese sind über TemplateManager::get() erreichbar (als Fallback-Werte).
 */
class GlobalVariables
{
    /**
     * Cache für geladene globale Variablen
     */
    private static ?array $cache = null;

    /**
     * Holt einen einzelnen globalen Variablen-Wert
     *
     * @param string $key Setting-Key
     * @param mixed $default Fallback-Wert wenn Variable nicht existiert
     * @param int|null $clangId Optionale Sprach-ID (null = aktuelle Sprache)
     * @return mixed Wert oder Default
     */
    public static function get(string $key, mixed $default = null, ?int $clangId = null): mixed
    {
        $all = self::getAll($clangId);

        return $all[$key] ?? $default;
    }

    /**
     * Holt alle globalen Variablen als Array
     *
     * @param int|null $clangId Optionale Sprach-ID (null = aktuelle Sprache)
     * @return array Key-Value Array
     */
    public static function getAll(?int $clangId = null): array
    {
        $resolvedClangId = $clangId ?? rex_clang::getCurrentId();

        if (!isset(self::$cache[$resolvedClangId])) {
            self::$cache[$resolvedClangId] = self::load($resolvedClangId);
        }

        return self::$cache[$resolvedClangId];
    }

    /**
     * Lädt globale Variablen aus der DB
     *
     * @param int $clangId Sprach-ID
     * @return array Key-Value Array
     */
    private static function load(int $clangId): array
    {
        $sql = rex_sql::factory();
        $sql->setQuery(
            'SELECT setting_key, setting_value FROM ' . rex::getTable('template_manager_globals') . ' WHERE clang_id = ? ORDER BY setting_key',
            [$clangId]
        );

        $values = [];
        foreach ($sql->getArray() as $row) {
            $values[$row['setting_key']] = $row['setting_value'];
        }

        return $values;
    }

    /**
     * Speichert eine globale Variable (Insert oder Update)
     *
     * @param string $key Setting-Key
     * @param string $value Wert
     * @param int $clangId Sprach-ID
     * @return bool Erfolg
     */
    public static function save(string $key, string $value, int $clangId): bool
    {
        $sql = rex_sql::factory();
        $sql->setQuery(
            'SELECT id FROM ' . rex::getTable('template_manager_globals') . ' WHERE setting_key = ? AND clang_id = ?',
            [$key, $clangId]
        );

        if ($sql->getRows() > 0) {
            $sql->setTable(rex::getTable('template_manager_globals'));
            $sql->setWhere(['id' => $sql->getValue('id')]);
            $sql->setValue('setting_value', $value);
            $sql->setValue('updated_date', date('Y-m-d H:i:s'));
            $sql->update();
        } else {
            $sql->setTable(rex::getTable('template_manager_globals'));
            $sql->setValue('setting_key', $key);
            $sql->setValue('setting_value', $value);
            $sql->setValue('clang_id', $clangId);
            $sql->setValue('created_date', date('Y-m-d H:i:s'));
            $sql->setValue('updated_date', date('Y-m-d H:i:s'));
            $sql->insert();
        }

        self::clearCache();

        return true;
    }

    /**
     * Löscht eine globale Variable
     *
     * @param string $key Setting-Key
     * @param int|null $clangId Sprach-ID (null = alle Sprachen)
     * @return bool Erfolg
     */
    public static function delete(string $key, ?int $clangId = null): bool
    {
        $sql = rex_sql::factory();

        if (null !== $clangId) {
            $sql->setQuery(
                'DELETE FROM ' . rex::getTable('template_manager_globals') . ' WHERE setting_key = ? AND clang_id = ?',
                [$key, $clangId]
            );
        } else {
            $sql->setQuery(
                'DELETE FROM ' . rex::getTable('template_manager_globals') . ' WHERE setting_key = ?',
                [$key]
            );
        }

        self::clearCache();

        return true;
    }

    /**
     * Holt alle eindeutigen Setting-Keys (über alle Sprachen)
     *
     * @return string[] Liste der Keys
     */
    public static function getAllKeys(): array
    {
        $sql = rex_sql::factory();
        $sql->setQuery(
            'SELECT DISTINCT setting_key FROM ' . rex::getTable('template_manager_globals') . ' ORDER BY setting_key'
        );

        $keys = [];
        foreach ($sql->getArray() as $row) {
            $keys[] = $row['setting_key'];
        }

        return $keys;
    }

    /**
     * Cache zurücksetzen
     */
    public static function clearCache(): void
    {
        self::$cache = null;
    }
}

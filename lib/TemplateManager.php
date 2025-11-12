<?php

namespace FriendsOfRedaxo\TemplateManager;

use rex;
use rex_article;
use rex_clang;
use rex_sql;
use rex_yrewrite;
use rex_yrewrite_domain;

/**
 * Template Manager
 * 
 * Lädt Template-Settings für Domain + Sprache
 * 
 * Verwendung im Template:
 * TemplateManager::get('tm_company_name')
 * TemplateManager::get('tm_primary_color', '#005d40')
 */
class TemplateManager
{
    /**
     * Cache für geladene Settings
     */
    private static ?array $cache = null;
    
    /**
     * Holt einen einzelnen Setting-Wert
     * 
     * @param string $key Setting-Key (z.B. 'tm_company_name')
     * @param mixed $default Fallback-Wert wenn Setting nicht existiert
     * @param int|null $domainId Optionale Domain-ID (null = aktuelle Domain)
     * @param int|null $clangId Optionale Sprach-ID (null = aktuelle Sprache)
     * @return mixed Setting-Wert oder Default
     */
    public static function get(string $key, mixed $default = null, ?int $domainId = null, ?int $clangId = null): mixed
    {
        $cacheKey = self::getCacheKey($domainId, $clangId);
        
        if (!isset(self::$cache[$cacheKey])) {
            self::loadCache($domainId, $clangId);
        }
        
        return self::$cache[$cacheKey][$key] ?? $default;
    }
    
    /**
     * Holt alle Settings als Array
     * 
     * @param int|null $domainId Optionale Domain-ID (null = aktuelle Domain)
     * @param int|null $clangId Optionale Sprach-ID (null = aktuelle Sprache)
     * @return array Alle Settings
     */
    public static function getAll(?int $domainId = null, ?int $clangId = null): array
    {
        $cacheKey = self::getCacheKey($domainId, $clangId);
        
        if (!isset(self::$cache[$cacheKey])) {
            self::loadCache($domainId, $clangId);
        }
        
        return self::$cache[$cacheKey];
    }
    
    /**
     * Lädt Settings in den Cache
     * 
     * @param int|null $domainId Domain-ID
     * @param int|null $clangId Sprach-ID
     */
    private static function loadCache(?int $domainId = null, ?int $clangId = null): void
    {
        $instance = new self();
        
        // Domain ermitteln
        $domain = null;
        if ($domainId !== null && class_exists('rex_yrewrite')) {
            $domain = rex_yrewrite::getDomainById($domainId);
        }
        
        $cacheKey = self::getCacheKey($domainId, $clangId);
        self::$cache[$cacheKey] = $instance->getDomainConfig($domain, $clangId);
    }
    
    /**
     * Erstellt Cache-Key für Domain/Sprache Kombination
     * 
     * @param int|null $domainId Domain-ID
     * @param int|null $clangId Sprach-ID
     * @return string Cache-Key
     */
    private static function getCacheKey(?int $domainId = null, ?int $clangId = null): string
    {
        $domainId = $domainId ?? (class_exists('rex_yrewrite') && rex_yrewrite::getCurrentDomain() ? rex_yrewrite::getCurrentDomain()->getId() : 0);
        $clangId = $clangId ?? rex_clang::getCurrentId();
        
        return "d{$domainId}_c{$clangId}";
    }
    
    /**
     * Cache zurücksetzen (für Tests)
     */
    public static function clearCache(): void
    {
        self::$cache = null;
    }
    
    /**
     * Holt Domain-spezifische Config für aktuelles Template
     * 
     * @param rex_yrewrite_domain|null $domain YRewrite Domain
     * @param int|null $clangId Sprach-ID (null = aktuelle Sprache)
     * @return array Settings-Array
     */
    public function getDomainConfig(?rex_yrewrite_domain $domain = null, ?int $clangId = null): array
    {
        // Aktuelle Domain ermitteln
        if ($domain === null) {
            $domain = \rex_yrewrite::getCurrentDomain();
        }
        
        if ($domain === null) {
            return [];
        }
        
        // Aktuelle Sprache ermitteln
        if ($clangId === null) {
            $clangId = rex_clang::getCurrentId();
        }
        
        // Template-ID des aktuellen Artikels ermitteln
        $article = rex_article::getCurrent();
        if (!$article) {
            return [];
        }
        
        $templateId = $article->getTemplateId();
        if (!$templateId) {
            return [];
        }
        
        return $this->getTemplateConfigForDomain($templateId, $domain->getId(), $clangId);
    }
    
    /**
     * Holt Template-Config für spezifische Domain + Sprache
     * 
     * @param int $templateId Template-ID
     * @param int|string $domainId Domain-ID
     * @param int $clangId Sprach-ID
     * @return array Settings-Array
     */
    public function getTemplateConfigForDomain(int $templateId, int|string $domainId, int $clangId): array
    {
        // Sicherstellen dass Domain-ID ein int ist
        $domainId = (int) $domainId;
        // Template-Settings aus DocBlock parsen
        $templateSettings = TemplateParser::getTemplateSettings($templateId);
        
        if (empty($templateSettings)) {
            return [];
        }
        
        // Gespeicherte Werte aus DB laden
        $savedValues = $this->loadSavedSettings($templateId, $domainId, $clangId);
        
        // Fallback auf erste Sprache
        $fallbackValues = [];
        if ($clangId !== rex_clang::getStartId()) {
            $fallbackValues = $this->loadSavedSettings($templateId, $domainId, rex_clang::getStartId());
        }
        
        // Config-Array zusammenbauen
        $config = [];
        
        foreach ($templateSettings as $key => $setting) {
            // Priorität: Gespeicherter Wert > Fallback Sprache > Default aus DocBlock
            if (isset($savedValues[$key])) {
                $config[$key] = $savedValues[$key];
            } elseif (isset($fallbackValues[$key])) {
                $config[$key] = $fallbackValues[$key];
            } else {
                $config[$key] = $setting['default'];
            }
        }
        
        return $config;
    }
    
    /**
     * Lädt gespeicherte Settings aus DB
     * 
     * @param int $templateId Template-ID
     * @param int $domainId Domain-ID
     * @param int $clangId Sprach-ID
     * @return array Key-Value Array
     */
    private function loadSavedSettings(int $templateId, int $domainId, int $clangId): array
    {
        $sql = rex_sql::factory();
        $sql->setQuery('
            SELECT setting_key, setting_value 
            FROM ' . \rex::getTable('template_settings') . ' 
            WHERE template_id = ? AND domain_id = ? AND clang_id = ?
        ', [$templateId, $domainId, $clangId]);
        
        $values = [];
        foreach ($sql->getArray() as $row) {
            $values[$row['setting_key']] = $row['setting_value'];
        }
        
        return $values;
    }
    
    /**
     * Speichert Settings für Template + Domain + Sprache
     * 
     * @param int $templateId Template-ID
     * @param int $domainId Domain-ID
     * @param int $clangId Sprach-ID
     * @param array $settings Key-Value Array
     * @return bool Erfolg
     */
    public function saveSettings(int $templateId, int $domainId, int $clangId, array $settings): bool
    {
        $sql = rex_sql::factory();
        
        foreach ($settings as $key => $value) {
            // Bestehenden Eintrag suchen
            $sql->setQuery('
                SELECT id FROM ' . \rex::getTable('template_settings') . ' 
                WHERE template_id = ? AND domain_id = ? AND clang_id = ? AND setting_key = ?
            ', [$templateId, $domainId, $clangId, $key]);
            
            if ($sql->getRows() > 0) {
                // Update
                $sql->setTable(\rex::getTable('template_settings'));
                $sql->setWhere(['id' => $sql->getValue('id')]);
                $sql->setValue('setting_value', $value);
                $sql->setValue('updated_date', date('Y-m-d H:i:s'));
                $sql->update();
            } else {
                // Insert
                $sql->setTable(\rex::getTable('template_settings'));
                $sql->setValue('template_id', $templateId);
                $sql->setValue('domain_id', $domainId);
                $sql->setValue('clang_id', $clangId);
                $sql->setValue('setting_key', $key);
                $sql->setValue('setting_value', $value);
                $sql->setValue('created_date', date('Y-m-d H:i:s'));
                $sql->setValue('updated_date', date('Y-m-d H:i:s'));
                $sql->insert();
            }
        }
        
        return true;
    }
}

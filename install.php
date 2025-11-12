<?php

$sql = rex_sql::factory();

// Tabelle für Template Settings erstellen
$sql->setQuery('
    CREATE TABLE IF NOT EXISTS `' . rex::getTable('template_settings') . '` (
        `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
        `template_id` int(10) unsigned NOT NULL,
        `domain_id` int(10) unsigned NOT NULL,
        `clang_id` int(10) unsigned NOT NULL,
        `setting_key` varchar(255) NOT NULL,
        `setting_value` text,
        `created_date` datetime DEFAULT NULL,
        `updated_date` datetime DEFAULT NULL,
        PRIMARY KEY (`id`),
        UNIQUE KEY `unique_setting` (`template_id`, `domain_id`, `clang_id`, `setting_key`),
        KEY `template_id` (`template_id`),
        KEY `domain_id` (`domain_id`),
        KEY `clang_id` (`clang_id`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
');

<?php

$sql = rex_sql::factory();
$sql->setQuery('DROP TABLE IF EXISTS `' . rex::getTable('template_settings') . '`');
$sql->setQuery('DROP TABLE IF EXISTS `' . rex::getTable('template_manager_globals') . '`');

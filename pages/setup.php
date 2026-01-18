<?php

$addon = rex_addon::get('template_manager');

// POST: Demo-Template importieren
if (rex_post('import', 'bool')) {
    $demoTemplateFile = $addon->getPath('install/demo_template.php');
    
    if (file_exists($demoTemplateFile)) {
        $demoContent = file_get_contents($demoTemplateFile);
        $templateKey = 'tm_modern_business';
        $templateName = 'Modern Business (Demo)';
        
        // Prüfen ob Template mit diesem Key bereits existiert
        $existingSql = rex_sql::factory();
        $existingSql->setQuery('SELECT id FROM ' . rex::getTable('template') . ' WHERE `key` = :key LIMIT 1', ['key' => $templateKey]);
        $existingId = $existingSql->getRows() > 0 ? (int) $existingSql->getValue('id') : null;
        
        // Template erstellen oder aktualisieren
        $templateSql = rex_sql::factory();
        $templateSql->setTable(rex::getTable('template'));
        $templateSql->setValue('key', $templateKey);
        $templateSql->setValue('name', $templateName);
        $templateSql->setValue('content', $demoContent);
        $templateSql->setValue('active', 1);
        
        // Attributes für Ctypes, Modules, Categories (leer bei Demo-Template)
        $attributes = [
            'ctype' => [],
            'modules' => [1 => ['all' => 1]], // Alle Module erlaubt
            'categories' => ['all' => 1] // Alle Kategorien erlaubt
        ];
        $templateSql->setArrayValue('attributes', $attributes);
        
        try {
            if ($existingId !== null) {
                // Vorhandenes Template aktualisieren
                $templateSql->addGlobalUpdateFields();
                $templateSql->setWhere(['id' => $existingId]);
                $templateSql->update();
                $templateId = $existingId;
                $isUpdate = true;
                
                // Extension Point für Update
                rex_extension::registerPoint(new rex_extension_point('TEMPLATE_UPDATED', '', [
                    'id' => $templateId,
                    'key' => $templateKey,
                    'name' => $templateName,
                    'content' => $demoContent,
                    'active' => 1,
                ]));
            } else {
                // Neues Template erstellen
                $templateSql->addGlobalCreateFields();
                $templateSql->insert();
                $templateId = (int) $templateSql->getLastId();
                $isUpdate = false;
                
                // Extension Point für neues Template
                rex_extension::registerPoint(new rex_extension_point('TEMPLATE_ADDED', '', [
                    'id' => $templateId,
                    'key' => $templateKey,
                    'name' => $templateName,
                    'content' => $demoContent,
                    'active' => 1,
                ]));
            }
            
            // Template-Cache löschen
            rex_template_cache::delete($templateId);
            
            // Erfolgsmeldung
            if ($isUpdate) {
                $content = '<p class="text-success"><i class="rex-icon fa-check"></i> ' . $addon->i18n('template_manager_setup_import_updated', $templateId) . '</p>';
            } else {
                $content = '<p class="text-success"><i class="rex-icon fa-check"></i> ' . $addon->i18n('template_manager_setup_import_success', $templateId) . '</p>';
            }
            $content .= '<p><a href="' . rex_url::backendPage('template_manager/config', ['template_id' => $templateId]) . '" class="btn btn-primary">Jetzt konfigurieren</a></p>';
            
            $fragment = new rex_fragment();
            $fragment->setVar('title', $addon->i18n('setup'), false);
            $fragment->setVar('body', $content, false);
            echo $fragment->parse('core/page/section.php');
            return;
        } catch (rex_sql_exception $e) {
            $content = '<p class="text-danger"><i class="rex-icon fa-exclamation-triangle"></i> ' . $addon->i18n('template_manager_setup_import_error', $e->getMessage()) . '</p>';
            
            $fragment = new rex_fragment();
            $fragment->setVar('title', $addon->i18n('setup'), false);
            $fragment->setVar('body', $content, false);
            echo $fragment->parse('core/page/section.php');
            return;
        }
    } else {
        $content = '<p class="text-danger"><i class="rex-icon fa-exclamation-triangle"></i> ' . $addon->i18n('template_manager_setup_import_notfound') . '</p>';
        
        $fragment = new rex_fragment();
        $fragment->setVar('title', $addon->i18n('setup'), false);
        $fragment->setVar('body', $content, false);
        echo $fragment->parse('core/page/section.php');
        return;
    }
}

// POST: Cleanup - entferne Settings von gelöschten Templates
if (rex_post('cleanup', 'bool')) {
    $sql = rex_sql::factory();
    
    // Alle Template-IDs aus rex_template holen
    $templateIds = [];
    $templates = $sql->getArray('SELECT id FROM ' . rex::getTable('template'));
    foreach ($templates as $tpl) {
        $templateIds[] = (int)$tpl['id'];
    }
    
    // Settings von nicht existierenden Templates löschen
    $deleteSql = rex_sql::factory();
    if (!empty($templateIds)) {
        $deleteSql->setQuery('DELETE FROM ' . rex::getTable('template_settings') . ' WHERE template_id NOT IN (' . implode(',', $templateIds) . ')');
    } else {
        // Alle Settings löschen wenn keine Templates existieren
        $deleteSql->setQuery('DELETE FROM ' . rex::getTable('template_settings'));
    }
    
    $deletedRows = $deleteSql->getRows();
    
    $content = '<p class="text-success"><i class="rex-icon fa-check"></i> ' . $addon->i18n('template_manager_setup_cleanup_success', $deletedRows) . '</p>';
    
    $fragment = new rex_fragment();
    $fragment->setVar('title', $addon->i18n('setup'), false);
    $fragment->setVar('body', $content, false);
    echo $fragment->parse('core/page/section.php');
    return;
}

// Formular
$content = '<div class="rex-addon-output">';

// Demo-Template Import
$content .= '<h3><i class="rex-icon fa-download"></i> ' . $addon->i18n('template_manager_setup_import_title') . '</h3>';
$content .= '<p>' . $addon->i18n('template_manager_setup_import_desc') . '</p>';
$content .= '<h4>' . $addon->i18n('template_manager_setup_import_features') . '</h4>';
$content .= '<ul>';
$content .= '<li>' . $addon->i18n('template_manager_setup_import_feature_1') . '</li>';
$content .= '<li>' . $addon->i18n('template_manager_setup_import_feature_2') . '</li>';
$content .= '<li>' . $addon->i18n('template_manager_setup_import_feature_3') . '</li>';
$content .= '<li>' . $addon->i18n('template_manager_setup_import_feature_4') . '</li>';
$content .= '<li>' . $addon->i18n('template_manager_setup_import_feature_5') . '</li>';
$content .= '<li>' . $addon->i18n('template_manager_setup_import_feature_6') . '</li>';
$content .= '<li>' . $addon->i18n('template_manager_setup_import_feature_7') . '</li>';
$content .= '</ul>';

$content .= '<form method="post" style="margin-bottom: 3rem;">';
$content .= '<button type="submit" name="import" value="1" class="btn btn-primary">';
$content .= '<i class="rex-icon fa-download"></i> ' . $addon->i18n('template_manager_setup_import_button');
$content .= '</button>';
$content .= '</form>';

// Cleanup
$content .= '<hr style="margin: 2rem 0;">';
$content .= '<h3><i class="rex-icon fa-broom"></i> ' . $addon->i18n('template_manager_setup_cleanup_title') . '</h3>';
$content .= '<p>' . $addon->i18n('template_manager_setup_cleanup_desc') . '</p>';
$content .= '<p class="text-muted"><small>' . $addon->i18n('template_manager_setup_cleanup_hint') . '</small></p>';

$content .= '<form method="post">';
$content .= '<button type="submit" name="cleanup" value="1" class="btn btn-warning">';
$content .= '<i class="rex-icon fa-broom"></i> ' . $addon->i18n('template_manager_setup_cleanup_button');
$content .= '</button>';
$content .= '</form>';

$content .= '</div>';

$fragment = new rex_fragment();
$fragment->setVar('title', $addon->i18n('setup'), false);
$fragment->setVar('body', $content, false);
echo $fragment->parse('core/page/section.php');

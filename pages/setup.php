<?php

$addon = rex_addon::get('template_manager');

// POST: Demo-Template importieren
if (rex_post('import', 'bool')) {
    $demoTemplateFile = $addon->getPath('install/demo_template.php');
    
    if (file_exists($demoTemplateFile)) {
        $demoContent = file_get_contents($demoTemplateFile);
        
        // Template erstellen
        $templateSql = rex_sql::factory();
        $templateSql->setTable(rex::getTable('template'));
        $templateSql->setValue('key', 'tm_modern_business');
        $templateSql->setValue('name', 'Modern Business (Demo)');
        $templateSql->setValue('content', $demoContent);
        $templateSql->setValue('active', 1);
        $templateSql->setValue('createdate', date('Y-m-d H:i:s'));
        $templateSql->setValue('updatedate', date('Y-m-d H:i:s'));
        $templateSql->setValue('createuser', rex::getUser() ? rex::getUser()->getLogin() : 'system');
        $templateSql->setValue('updateuser', rex::getUser() ? rex::getUser()->getLogin() : 'system');
        
        try {
            $templateSql->insert();
            $templateId = $templateSql->getLastId();
            
            // Template-Cache komplett leeren
            rex_delete_cache();
            
            // Extension Point aufrufen um Template-Cache zu invalidieren
            rex_extension::registerPoint(new rex_extension_point('TEMPLATE_ADDED', $templateId));
            
            $content = '<p class="text-success"><i class="rex-icon fa-check"></i> ' . $addon->i18n('template_manager_setup_import_success', $templateId) . '</p>';
            
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

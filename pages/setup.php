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
            
            // Template-Cache leeren
            rex_delete_cache();
            
            $content = '<p class="text-success"><i class="rex-icon fa-check"></i> Demo-Template "Modern Business (Demo)" wurde erfolgreich importiert! Template-ID: ' . $templateSql->getLastId() . '</p>';
            
            $fragment = new rex_fragment();
            $fragment->setVar('title', 'Setup', false);
            $fragment->setVar('body', $content, false);
            echo $fragment->parse('core/page/section.php');
            return;
        } catch (rex_sql_exception $e) {
            $content = '<p class="text-danger"><i class="rex-icon fa-exclamation-triangle"></i> Fehler beim Importieren: ' . $e->getMessage() . '</p>';
            
            $fragment = new rex_fragment();
            $fragment->setVar('title', 'Setup', false);
            $fragment->setVar('body', $content, false);
            echo $fragment->parse('core/page/section.php');
            return;
        }
    } else {
        $content = '<p class="text-danger"><i class="rex-icon fa-exclamation-triangle"></i> Demo-Template-Datei nicht gefunden!</p>';
        
        $fragment = new rex_fragment();
        $fragment->setVar('title', 'Setup', false);
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
    
    $content = '<p class="text-success"><i class="rex-icon fa-check"></i> Cleanup abgeschlossen. ' . $deletedRows . ' verwaiste Einträge entfernt.</p>';
    
    $fragment = new rex_fragment();
    $fragment->setVar('title', 'Setup', false);
    $fragment->setVar('body', $content, false);
    echo $fragment->parse('core/page/section.php');
    return;
}

// Formular
$content = '<div class="rex-addon-output">';

// Demo-Template Import
$content .= '<h3><i class="rex-icon fa-download"></i> Demo-Template importieren</h3>';
$content .= '<p>Importiert das vorkonfigurierte "Modern Business" Template mit DOMAIN_SETTINGS.</p>';
$content .= '<h4>Features:</h4>';
$content .= '<ul>';
$content .= '<li>5 essenzielle Einstellungen (Firmenname, Farbe, E-Mail, Footer-Links, Breadcrumbs)</li>';
$content .= '<li>Modernes, schlankes CSS ohne Framework-Abhängigkeiten</li>';
$content .= '<li>CSS Custom Properties für einfaches Theming</li>';
$content .= '<li>Dark/Light Mode Support</li>';
$content .= '<li>Vollständig barrierefrei (WCAG 2.1 AA)</li>';
$content .= '<li>Responsive Design</li>';
$content .= '<li>Inline JavaScript mit REDAXO Nonce (CSP-sicher)</li>';
$content .= '</ul>';

$content .= '<form method="post" style="margin-bottom: 3rem;">';
$content .= '<button type="submit" name="import" value="1" class="btn btn-primary">';
$content .= '<i class="rex-icon fa-download"></i> Demo-Template jetzt importieren';
$content .= '</button>';
$content .= '</form>';

// Cleanup
$content .= '<hr style="margin: 2rem 0;">';
$content .= '<h3><i class="rex-icon fa-broom"></i> Datenbank-Cleanup</h3>';
$content .= '<p>Entfernt Settings von gelöschten Templates aus der Datenbank.</p>';
$content .= '<p class="text-muted"><small>Dies ist nützlich wenn Templates gelöscht wurden und deren Settings in der Datenbank verblieben sind.</small></p>';

$content .= '<form method="post">';
$content .= '<button type="submit" name="cleanup" value="1" class="btn btn-warning">';
$content .= '<i class="rex-icon fa-broom"></i> Verwaiste Settings entfernen';
$content .= '</button>';
$content .= '</form>';

$content .= '</div>';

$fragment = new rex_fragment();
$fragment->setVar('title', 'Setup', false);
$fragment->setVar('body', $content, false);
echo $fragment->parse('core/page/section.php');

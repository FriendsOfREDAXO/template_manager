<?php

use FriendsOfRedaxo\TemplateManager\TemplateParser;

$addon = rex_addon::get('template_manager');

// Übersicht über Templates mit DOMAIN_SETTINGS
$parser = new TemplateParser();
$templates = $parser->getAllTemplates();
$domains = rex_yrewrite::getDomains();

$content = '<div class="rex-addon-output">';

// Kurze Einleitung
$content .= '<p>' . $addon->i18n('template_manager_intro') . '</p>';

// Templates-Liste
if (!empty($templates)) {
    $content .= '<h3>' . $addon->i18n('template_manager_available_templates') . '</h3>';
    
    $content .= '<table class="table table-striped">';
    $content .= '<thead>';
    $content .= '<tr>';
    $content .= '<th>' . $addon->i18n('template_manager_template_name') . '</th>';
    $content .= '<th>' . $addon->i18n('template_manager_settings_count') . '</th>';
    $content .= '<th>' . $addon->i18n('template_manager_actions') . '</th>';
    $content .= '</tr>';
    $content .= '</thead>';
    $content .= '<tbody>';
    
    foreach ($templates as $tpl) {
        $content .= '<tr>';
        $content .= '<td><strong>' . rex_escape($tpl['name']) . '</strong></td>';
        $content .= '<td>' . count($tpl['settings']) . ' ' . $addon->i18n('template_manager_settings') . '</td>';
        $content .= '<td>';
        $content .= '<a href="?page=template_manager/config&template_id=' . $tpl['id'] . '" class="btn btn-primary btn-xs">';
        $content .= '<i class="rex-icon fa-edit"></i> ' . $addon->i18n('template_manager_configure');
        $content .= '</a>';
        $content .= '</td>';
        $content .= '</tr>';
    }
    
    $content .= '</tbody>';
    $content .= '</table>';
} else {
    // Panel statt Alert
    $content .= '<div class="panel panel-default">';
    $content .= '<div class="panel-heading"><strong>' . $addon->i18n('template_manager_no_templates') . '</strong></div>';
    $content .= '<div class="panel-body">';
    $content .= '<p>' . $addon->i18n('template_manager_no_templates_hint') . '</p>';
    $content .= '<pre>/**
 * DOMAIN_SETTINGS
 * tm_company_name: text|Firmenname|Meine Firma|Offizieller Firmenname
 * tm_logo: media|Logo||Hauptlogo
 */</pre>';
    $content .= '</div>';
    $content .= '</div>';
}

$content .= '</div>'; // rex-addon-output

$fragment = new rex_fragment();
$fragment->setVar('body', $content, false);
echo $fragment->parse('core/page/section.php');

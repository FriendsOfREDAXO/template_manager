<?php

use FriendsOfRedaxo\TemplateManager\TemplateParser;

$addon = rex_addon::get('template_manager');

// Nur für Admins
if (!rex::getUser() || !rex::getUser()->isAdmin()) {
    echo rex_view::error('Keine Berechtigung.');
    return;
}

// POST: Definition speichern
if (rex_post('save_definition', 'bool')) {
    $definition = rex_post('global_settings_definition', 'string');
    $addon->setConfig('global_settings_definition', $definition);
    echo rex_view::success($addon->i18n('template_manager_definitions_saved'));
}

// Aktuelle Definition laden
$definition = $addon->getConfig('global_settings_definition', '');

// Formular für Definition
$content = '<form action="' . rex_url::currentBackendPage() . '" method="post">';
$content .= '<input type="hidden" name="save_definition" value="1" />';

$n = [];
$n['label'] = '<label for="global_settings_definition">' . $addon->i18n('template_manager_global_definition') . '</label>';
$n['field'] = '<textarea class="form-control rex-code" id="global_settings_definition" name="global_settings_definition" rows="15" style="font-family: monospace;">' . rex_escape($definition) . '</textarea>';
$n['note'] = $addon->i18n('template_manager_global_definition_note');

$formElements = [$n];
$fragment = new rex_fragment();
$fragment->setVar('elements', $formElements, false);
$content .= $fragment->parse('core/form/container.php');

$formElements = [];
$n = [];
$n['field'] = '<button class="btn btn-save" type="submit" name="save_definition" value="1">' . $addon->i18n('template_manager_save_definition') . '</button>';
$formElements[] = $n;

$fragment = new rex_fragment();
$fragment->setVar('elements', $formElements, false);
$content .= $fragment->parse('core/form/submit.php');
$content .= '</form>';

$fragment = new rex_fragment();
$fragment->setVar('title', $addon->i18n('template_manager_global_definition_title'));
$fragment->setVar('body', $content, false);
echo $fragment->parse('core/page/section.php');

<?php

use FriendsOfRedaxo\TemplateManager\GlobalVariables;

$addon = rex_addon::get('template_manager');
$clangs = rex_clang::getAll();
$startClangId = rex_clang::getStartId();

// POST: Variable löschen
if (rex_post('delete', 'bool')) {
    $deleteKey = rex_post('delete_key', 'string', '');
    if ('' !== $deleteKey && preg_match('/^tm_\w+$/', $deleteKey)) {
        GlobalVariables::delete($deleteKey);
        // Auch TemplateManager-Cache leeren
        \FriendsOfRedaxo\TemplateManager\TemplateManager::clearCache();
        echo rex_view::success($addon->i18n('template_manager_globals_deleted', rex_escape($deleteKey)));
    }
}

// POST: Variable speichern (neu oder bearbeiten)
if (rex_post('save', 'bool')) {
    $saveKey = trim(rex_post('setting_key', 'string', ''));
    $allValues = rex_post('setting_value', 'array', []);

    if ('' !== $saveKey && preg_match('/^tm_\w+$/', $saveKey)) {
        foreach ($clangs as $clang) {
            $value = $allValues[$clang->getId()] ?? '';
            GlobalVariables::save($saveKey, $value, $clang->getId());
        }
        \FriendsOfRedaxo\TemplateManager\TemplateManager::clearCache();
        echo rex_view::success($addon->i18n('template_manager_globals_saved'));
    } else {
        echo rex_view::error($addon->i18n('template_manager_globals_key_required'));
    }
}

// Alle Keys laden
$allKeys = GlobalVariables::getAllKeys();

// --- Formular: neue Variable anlegen / bearbeiten ---
$editKey = rex_request('edit_key', 'string', '');
$isEdit = '' !== $editKey;

$formTitle = $isEdit
    ? $addon->i18n('template_manager_globals_edit') . ' <small class="rex-primary-id">' . rex_escape($editKey) . '</small>'
    : $addon->i18n('template_manager_globals_add');

$formBody = '<form method="post">';
$formBody .= '<input type="hidden" name="save" value="1">';

// Key-Feld
$formBody .= '<div class="form-group">';
$formBody .= '<label for="tm-global-key">' . $addon->i18n('template_manager_globals_key') . '</label>';
if ($isEdit) {
    $formBody .= '<input type="hidden" name="setting_key" value="' . rex_escape($editKey) . '">';
    $formBody .= '<input type="text" class="form-control" id="tm-global-key" value="' . rex_escape($editKey) . '" readonly>';
} else {
    $formBody .= '<input type="text" class="form-control" id="tm-global-key" name="setting_key" placeholder="tm_my_variable" pattern="tm_\w+" required>';
    $formBody .= '<span class="help-block">' . $addon->i18n('template_manager_globals_key_hint') . '</span>';
}
$formBody .= '</div>';

// Wert-Felder pro Sprache
foreach ($clangs as $clang) {
    $currentValue = $isEdit ? GlobalVariables::get($editKey, '', $clang->getId()) : '';
    $label = rex_escape($clang->getName());
    if ($clang->getId() === $startClangId) {
        $label .= ' <span class="label label-info">Fallback</span>';
    }
    $formBody .= '<div class="form-group">';
    $formBody .= '<label>' . $label . '</label>';
    $formBody .= '<input type="text" class="form-control" name="setting_value[' . $clang->getId() . ']" value="' . rex_escape((string) $currentValue) . '">';
    $formBody .= '</div>';
}

$formBody .= '<button class="btn btn-save rex-form-aligned" type="submit">';
$formBody .= '<i class="rex-icon rex-icon-save"></i> ' . $addon->i18n('template_manager_save');
$formBody .= '</button>';

if ($isEdit) {
    $formBody .= ' <a href="' . rex_url::backendPage('template_manager/globals') . '" class="btn btn-abort">';
    $formBody .= $addon->i18n('template_manager_globals_cancel');
    $formBody .= '</a>';
}

$formBody .= '</form>';

$fragment = new rex_fragment();
$fragment->setVar('title', $formTitle, false);
$fragment->setVar('body', $formBody, false);
echo $fragment->parse('core/page/section.php');

// --- Tabelle: vorhandene Variablen ---
$tableBody = '';

if (!empty($allKeys)) {
    $tableBody .= '<table class="table table-striped">';
    $tableBody .= '<thead><tr>';
    $tableBody .= '<th>' . $addon->i18n('template_manager_globals_key') . '</th>';

    foreach ($clangs as $clang) {
        $tableBody .= '<th>' . rex_escape($clang->getName());
        if ($clang->getId() === $startClangId) {
            $tableBody .= ' <span class="label label-info">Fallback</span>';
        }
        $tableBody .= '</th>';
    }

    $tableBody .= '<th>' . $addon->i18n('template_manager_actions') . '</th>';
    $tableBody .= '</tr></thead><tbody>';

    foreach ($allKeys as $key) {
        $tableBody .= '<tr>';
        $tableBody .= '<td><code>' . rex_escape($key) . '</code></td>';

        foreach ($clangs as $clang) {
            $val = GlobalVariables::get($key, '', $clang->getId());
            $tableBody .= '<td>' . rex_escape((string) $val) . '</td>';
        }

        $editUrl = rex_url::backendPage('template_manager/globals', ['edit_key' => $key]);
        $tableBody .= '<td>';
        $tableBody .= '<a href="' . $editUrl . '" class="btn btn-edit btn-xs">';
        $tableBody .= '<i class="rex-icon fa-edit"></i> ' . $addon->i18n('template_manager_globals_edit_action');
        $tableBody .= '</a> ';
        $tableBody .= '<form method="post" style="display:inline-block" onsubmit="return confirm(\'' . rex_escape($addon->i18n('template_manager_globals_delete_confirm', $key)) . '\')">';
        $tableBody .= '<input type="hidden" name="delete" value="1">';
        $tableBody .= '<input type="hidden" name="delete_key" value="' . rex_escape($key) . '">';
        $tableBody .= '<button type="submit" class="btn btn-delete btn-xs">';
        $tableBody .= '<i class="rex-icon rex-icon-delete"></i> ' . $addon->i18n('template_manager_globals_delete');
        $tableBody .= '</button>';
        $tableBody .= '</form>';
        $tableBody .= '</td>';
        $tableBody .= '</tr>';
    }

    $tableBody .= '</tbody></table>';
} else {
    $tableBody .= '<p class="text-muted">' . $addon->i18n('template_manager_globals_empty') . '</p>';
}

$fragment = new rex_fragment();
$fragment->setVar('title', $addon->i18n('template_manager_globals_list'), false);
$fragment->setVar('body', $tableBody, false);
echo $fragment->parse('core/page/section.php');

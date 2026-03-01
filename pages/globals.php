<?php

use FriendsOfRedaxo\TemplateManager\FieldRendererManager;
use FriendsOfRedaxo\TemplateManager\TemplateManager;
use FriendsOfRedaxo\TemplateManager\TemplateParser;

$addon = rex_addon::get('template_manager');
$manager = new TemplateManager();

// POST: Settings speichern
if (rex_post('save', 'bool')) {
    $allSettings = rex_post('settings', 'array', []);

    foreach ($allSettings as $clangId => $settings) {
        if (!empty($settings)) {
            $manager->saveGlobalSettings((int) $clangId, $settings);
        }
    }

    echo rex_view::success($addon->i18n('template_manager_values_saved'));
}

// Aktuelle Definition laden
$definition = $addon->getConfig('global_settings_definition', '');
$parsed = TemplateParser::parseSettings($definition);
$clangs = rex_clang::getAll();

if (empty($parsed['settings'])) {
    echo rex_view::info($addon->i18n('template_manager_no_global_settings'));
    return;
}

// Formular-Body mit Tabs aufbauen (identisch zu config.php)
$panel = '';
$panel .= '<div class="tab-content">';

$user = rex::getUser();
$isAdmin = $user && $user->isAdmin();

foreach ($clangs as $clang) {
    $active = $clang->getId() === rex_clang::getStartId() ? 'active in' : '';
    $panel .= '<div role="tabpanel" class="tab-pane fade ' . $active . '" id="global-lang-' . $clang->getId() . '">';

    // Sprach-Info im Tab
    $panel .= '<div class="alert alert-info" style="margin-top: 1rem;">';
    $panel .= '<strong><i class="rex-icon fa-language"></i> ' . $addon->i18n('template_manager_config_language_label') . '</strong> ';
    $panel .= '<strong>' . rex_escape($clang->getName()) . '</strong>';
    if ($clang->getId() === rex_clang::getStartId()) {
        $panel .= ' <span class="label label-info">Fallback</span>';
    }
    $panel .= '</div>';

    // Gespeicherte Werte laden
    $savedValues = $manager->getGlobalConfig($clang->getId());

    // Gruppen-basierte Darstellung mit Akkordeons
    if (!empty($parsed['groups'])) {
        $panel .= '<div class="panel-group" id="accordion-global-lang-' . $clang->getId() . '">';

        $groupIndex = 0;
        foreach ($parsed['groups'] as $groupKey => $group) {
            // Rechte prüfen
            $hasAccess = $isAdmin;
            if (!$isAdmin) {
                if (empty($group['roles'])) {
                    $hasAccess = true;
                } else {
                    foreach ($group['roles'] as $role) {
                        if ($user && $user->hasPerm($role)) {
                            $hasAccess = true;
                            break;
                        }
                    }
                }
            }

            if (!$hasAccess) {
                continue;
            }

            $collapseId = 'collapse-global-' . $clang->getId() . '-' . $groupIndex;
            $isFirstGroup = 0 === $groupIndex;

            $panel .= '<div class="panel panel-default">';
            $panel .= '<div class="panel-heading" role="tab">';
            $panel .= '<h4 class="panel-title">';
            $panel .= '<a role="button" data-toggle="collapse" data-parent="#accordion-global-lang-' . $clang->getId() . '" ';
            $panel .= 'href="#' . $collapseId . '" aria-expanded="' . ($isFirstGroup ? 'true' : 'false') . '">';
            $panel .= '<i class="rex-icon fa-chevron-down"></i> ';

            if (!empty($group['icon'])) {
                $panel .= '<i class="' . rex_escape($group['icon']) . '"></i> ';
            }

            $panel .= rex_escape($group['name']);

            if (!empty($group['roles']) && !$isAdmin) {
                $panel .= ' <small class="label label-info">' . implode(', ', array_map('rex_escape', $group['roles'])) . '</small>';
            }

            $panel .= '</a>';
            $panel .= '</h4>';
            $panel .= '</div>';

            $panel .= '<div id="' . $collapseId . '" class="panel-collapse collapse' . ($isFirstGroup ? ' in' : '') . '">';
            $panel .= '<div class="panel-body">';

            foreach ($group['fields'] as $fieldKey) {
                if (isset($parsed['settings'][$fieldKey])) {
                    $setting = $parsed['settings'][$fieldKey];
                    $panel .= FieldRendererManager::renderField($setting, $savedValues[$setting['key']] ?? $setting['default'], $clang->getId());
                }
            }

            $panel .= '</div>'; // panel-body
            $panel .= '</div>'; // collapse
            $panel .= '</div>'; // panel

            ++$groupIndex;
        }

        $panel .= '</div>'; // panel-group
    } else {
        // Keine Gruppen: Felder direkt
        foreach ($parsed['settings'] as $setting) {
            $panel .= FieldRendererManager::renderField($setting, $savedValues[$setting['key']] ?? $setting['default'], $clang->getId());
        }
    }

    $panel .= '</div>'; // tab-pane
}

$panel .= '</div>'; // tab-content

// Tab-Navigation für Sprachen aufbauen
$options = '<ul class="nav nav-tabs" id="rex-js-global-manager-tabs">';
foreach ($clangs as $clang) {
    $options .= '<li><a href="#global-lang-' . $clang->getId() . '" data-toggle="tab">';
    $options .= rex_escape($clang->getName());
    if ($clang->getId() === rex_clang::getStartId()) {
        $options .= ' <span class="label label-info">Fallback</span>';
    }
    $options .= '</a></li>';
}
$options .= '</ul>';

// Save-Button
$buttons = '<button class="btn btn-save rex-form-aligned" type="submit" name="save" value="1">';
$buttons .= '<i class="rex-icon rex-icon-save"></i> ' . $addon->i18n('template_manager_save_values');
$buttons .= '</button>';

// Formular mit Fragment rendern
$fragment = new rex_fragment();
$fragment->setVar('class', 'edit', false);
$fragment->setVar('title', $addon->i18n('template_manager_global_values_title'), false);
$fragment->setVar('options', $options, false);
$fragment->setVar('body', $panel, false);
$fragment->setVar('buttons', $buttons, false);
$formContent = $fragment->parse('core/page/section.php');

echo '
<form method="post">
    <input type="hidden" name="save" value="1">
    ' . $formContent . '
</form>

<script type="text/javascript" nonce="' . rex_response::getNonce() . '">
jQuery(function($) {
    $("#rex-js-global-manager-tabs a:first").tab("show");
});
</script>
';

<?php

use FriendsOfRedaxo\TemplateManager\FieldRendererManager;
use FriendsOfRedaxo\TemplateManager\TemplateManager;
use FriendsOfRedaxo\TemplateManager\TemplateParser;

$addon = rex_addon::get('template_manager');

// Erfolgs-Meldung nach Import anzeigen
if (rex_request('import_success', 'int')) {
    $templateId = rex_request('template_id', 'int');
    echo rex_view::success($addon->i18n('template_manager_setup_import_success', $templateId));
}

// Alle Templates mit DOMAIN_SETTINGS holen
$parser = new TemplateParser();
$templates = $parser->getAllTemplates();

// Alle YRewrite Domains holen und sortieren (Default-Domain ans Ende)
$domains = rex_yrewrite::getDomains();
usort($domains, static function ($a, $b) {
    $aId = $a->getId();
    $bId = $b->getId();

    // Domains ohne ID (leer oder 0) ans Ende
    if (empty($aId) && !empty($bId)) {
        return 1;
    }
    if (!empty($aId) && empty($bId)) {
        return -1;
    }

    // Ansonsten alphabetisch nach Namen
    return strcmp($a->getName(), $b->getName());
});

// Alle Sprachen
$clangs = rex_clang::getAll();

// Template auswählen
$selectedTemplateId = rex_request('template_id', 'int', $templates[0]['id'] ?? 0);

// Domain ID ermitteln - array_key_first gibt bei YRewrite Domains einen String zurück!
$firstDomainId = 0;
if (!empty($domains)) {
    $firstDomain = reset($domains);
    $firstDomainId = $firstDomain ? $firstDomain->getId() : 0;
}
$selectedDomainId = (int) rex_request('domain_id', 'int', $firstDomainId);

// POST: Settings speichern
if (rex_post('save', 'bool')) {
    $templateId = rex_post('template_id', 'int');
    $domainId = rex_post('domain_id', 'int');
    $allSettings = rex_post('settings', 'array', []);

    $manager = new TemplateManager();

    // Für jede Sprache speichern
    foreach ($allSettings as $clangId => $settings) {
        if (!empty($settings)) {
            $manager->saveSettings($templateId, $domainId, (int) $clangId, $settings);
        }
    }

    echo rex_view::success($addon->i18n('template_manager_saved'));
}

// Template & Domain Auswahl
$content = '<div class="alert alert-info" style="box-shadow: 0 4px 12px rgba(0,0,0,0.15);">';
$content .= '<div class="row">';
$content .= '<div class="col-md-6">';
$content .= '<div class="form-group">';
$content .= '<label style="font-weight: 600; font-size: 14px;"><i class="rex-icon fa-file-code-o"></i> ' . $addon->i18n('template_manager_select_template') . '</label>';
$content .= '<select class="form-control selectpicker" data-size="10" data-live-search="true" id="template-select" onchange="window.location.href=\'?page=template_manager/config&template_id=\'+this.value+\'&domain_id=' . $selectedDomainId . '\'">';

foreach ($templates as $tpl) {
    $selected = $tpl['id'] === $selectedTemplateId ? 'selected' : '';
    $content .= '<option value="' . $tpl['id'] . '" ' . $selected . '>' . rex_escape($tpl['name']) . '</option>';
}

$content .= '</select>';
$content .= '</div>';
$content .= '</div>';

$content .= '<div class="col-md-6">';
$content .= '<div class="form-group">';
$content .= '<label style="font-weight: 600; font-size: 14px;"><i class="rex-icon fa-globe"></i> ' . $addon->i18n('template_manager_select_domain') . '</label>';
$content .= '<select class="form-control selectpicker" data-size="10" id="domain-select" onchange="window.location.href=\'?page=template_manager/config&template_id=' . $selectedTemplateId . '&domain_id=\'+this.value">';

foreach ($domains as $domain) {
    $selected = $domain->getId() === $selectedDomainId ? 'selected' : '';
    $content .= '<option value="' . $domain->getId() . '" ' . $selected . '>' . rex_escape($domain->getName()) . '</option>';
}

$content .= '</select>';
$content .= '</div>';
$content .= '</div>';
$content .= '</div>';

$content .= '</div>';

$content .= '<script nonce="' . rex_response::getNonce() . '">
jQuery(function($) {
    $(".selectpicker").selectpicker("refresh");
});
</script>';

// Template Settings laden
$templateData = null;
foreach ($templates as $tpl) {
    if ($tpl['id'] === $selectedTemplateId) {
        $templateData = $tpl;
        break;
    }
}

if (!$templateData) {
    $content = '<div class="panel panel-default">';
    $content .= '<div class="panel-body">';
    $content .= '<p>' . $addon->i18n('template_manager_no_settings') . '</p>';
    $content .= '</div>';
    $content .= '</div>';

    $fragment = new rex_fragment();
    $fragment->setVar('title', $addon->i18n('template_manager_settings'), false);
    $fragment->setVar('body', $content, false);
    echo $fragment->parse('core/page/section.php');
    exit;
}

// Formular-Body mit Tabs aufbauen
$panel = '';

// Sprach-Tabs Content
$panel .= '<div class="tab-content">';

// Aktuelle Domain-Info ermitteln
$currentDomainName = 'Unbekannt';
foreach ($domains as $domain) {
    if ($domain->getId() === $selectedDomainId) {
        $currentDomainName = $domain->getName();
        break;
    }
}

foreach ($clangs as $clang) {
    $active = $clang->getId() === rex_clang::getStartId() ? 'active in' : '';
    $panel .= '<div role="tabpanel" class="tab-pane fade ' . $active . '" id="lang-' . $clang->getId() . '">';

    // Nur Sprach-Info im Tab anzeigen (Domain + Template sind bereits oben sichtbar)
    $panel .= '<div class="alert alert-info" style="margin-top: 1rem;">';
    $panel .= '<strong><i class="rex-icon fa-language"></i> ' . $addon->i18n('template_manager_config_language_label') . '</strong> ';
    $panel .= '<strong>' . rex_escape($clang->getName()) . '</strong>';
    if ($clang->getId() === rex_clang::getStartId()) {
        $panel .= ' <span class="label label-info">Fallback</span>';
    }
    $panel .= '</div>';

    // Gespeicherte Werte laden
    $manager = new TemplateManager();
    $savedValues = $manager->getTemplateConfigForDomain($selectedTemplateId, $selectedDomainId, $clang->getId());

    // User-Rolle für Rechteprüfung
    $user = rex::getUser();
    $isAdmin = $user && $user->isAdmin();

    // Gruppen-basierte Darstellung mit Akkordeons
    if (!empty($templateData['groups'])) {
        $panel .= '<div class="panel-group" id="accordion-lang-' . $clang->getId() . '">';

        $groupIndex = 0;
        foreach ($templateData['groups'] as $groupKey => $group) {
            // Rechte prüfen: Admin sieht alles, andere nur wenn keine Rollen oder eigene Rolle vorhanden
            $hasAccess = $isAdmin;
            if (!$isAdmin) {
                if (empty($group['roles'])) {
                    // Keine Rollen = für alle sichtbar
                    $hasAccess = true;
                } else {
                    // Prüfen ob User eine der benötigten Rollen hat
                    foreach ($group['roles'] as $role) {
                        if ($user && $user->hasPerm($role)) {
                            $hasAccess = true;
                            break;
                        }
                    }
                }
            }

            if (!$hasAccess) {
                continue; // Gruppe überspringen wenn keine Rechte
            }

            $collapseId = 'collapse-' . $clang->getId() . '-' . $groupIndex;
            $isFirstGroup = 0 === $groupIndex;

            $panel .= '<div class="panel panel-default">';
            $panel .= '<div class="panel-heading" role="tab">';
            $panel .= '<h4 class="panel-title">';
            $panel .= '<a role="button" data-toggle="collapse" data-parent="#accordion-lang-' . $clang->getId() . '" ';
            $panel .= 'href="#' . $collapseId . '" aria-expanded="' . ($isFirstGroup ? 'true' : 'false') . '">';
            $panel .= '<i class="rex-icon fa-chevron-down"></i> ';

            // Optional: Font Awesome Icon vor dem Gruppennamen anzeigen
            if (!empty($group['icon'])) {
                $panel .= '<i class="' . rex_escape($group['icon']) . '"></i> ';
            }

            $panel .= rex_escape($group['name']);

            // Rollen-Badge anzeigen (nur für Non-Admins zur Info)
            if (!empty($group['roles']) && !$isAdmin) {
                $panel .= ' <small class="label label-info">' . implode(', ', array_map('rex_escape', $group['roles'])) . '</small>';
            }

            $panel .= '</a>';
            $panel .= '</h4>';
            $panel .= '</div>';

            $panel .= '<div id="' . $collapseId . '" class="panel-collapse collapse' . ($isFirstGroup ? ' in' : '') . '">';
            $panel .= '<div class="panel-body">';

            // Felder dieser Gruppe rendern
            foreach ($group['fields'] as $fieldKey) {
                if (isset($templateData['settings'][$fieldKey])) {
                    $setting = $templateData['settings'][$fieldKey];
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
        // Keine Gruppen: Alte Darstellung ohne Akkordeons
        foreach ($templateData['settings'] as $setting) {
            $panel .= FieldRendererManager::renderField($setting, $savedValues[$setting['key']] ?? $setting['default'], $clang->getId());
        }
    }

    $panel .= '</div>'; // tab-pane
}

$panel .= '</div>'; // tab-content

// Tab-Navigation für Sprachen aufbauen
$options = '<ul class="nav nav-tabs" id="rex-js-template-manager-tabs">';
foreach ($clangs as $clang) {
    $options .= '<li><a href="#lang-' . $clang->getId() . '" data-toggle="tab">';
    $options .= rex_escape($clang->getName());
    if ($clang->getId() === rex_clang::getStartId()) {
        $options .= ' <span class="label label-info">Fallback</span>';
    }
    $options .= '</a></li>';
}
$options .= '</ul>';

// Save-Button
$buttons = '<button class="btn btn-save rex-form-aligned" type="submit" name="save" value="1">';
$buttons .= '<i class="rex-icon rex-icon-save"></i> ' . $addon->i18n('template_manager_save');
$buttons .= '</button>';

// Formular mit Fragment rendern
$fragment = new rex_fragment();
$fragment->setVar('class', 'edit', false);
$fragment->setVar('title', $addon->i18n('template_manager_settings') . ' <small class="rex-primary-id">' . rex_escape($templateData['name']) . '</small>', false);
$fragment->setVar('options', $options, false);
$fragment->setVar('body', $panel, false);
$fragment->setVar('buttons', $buttons, false);
$formContent = $fragment->parse('core/page/section.php');

// Formular-Wrapper
$formContent = '
<form method="post">
    <input type="hidden" name="template_id" value="' . $selectedTemplateId . '">
    <input type="hidden" name="domain_id" value="' . $selectedDomainId . '">
    ' . $formContent . '
</form>

<script type="text/javascript" nonce="' . rex_response::getNonce() . '">
jQuery(function($) {
    // Ersten Tab aktiv setzen
    $("#rex-js-template-manager-tabs a:first").tab("show");
});
</script>
';

// Template & Domain Auswahl oben, dann Formular
$fragment = new rex_fragment();
$fragment->setVar('title', $addon->i18n('template_manager_configure'), false);
$fragment->setVar('body', $content, false);
echo $fragment->parse('core/page/section.php');

echo $formContent;

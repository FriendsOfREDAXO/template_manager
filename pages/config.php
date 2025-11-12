<?php

use FriendsOfRedaxo\TemplateManager\TemplateParser;
use FriendsOfRedaxo\TemplateManager\TemplateManager;

$addon = rex_addon::get('template_manager');

// Alle Templates mit DOMAIN_SETTINGS holen
$parser = new TemplateParser();
$templates = $parser->getAllTemplates();

// Alle YRewrite Domains holen
$domains = rex_yrewrite::getDomains();

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
            $manager->saveSettings($templateId, $domainId, (int)$clangId, $settings);
        }
    }
    
    echo rex_view::success($addon->i18n('template_manager_saved'));
}

// Template & Domain Auswahl
$content = '<div class="row">';
$content .= '<div class="col-md-6">';
$content .= '<div class="form-group">';
$content .= '<label>' . $addon->i18n('template_manager_select_template') . '</label>';
$content .= '<select class="form-control" id="template-select" onchange="window.location.href=\'?page=template_manager/config&template_id=\'+this.value+\'&domain_id=' . $selectedDomainId . '\'">';

foreach ($templates as $tpl) {
    $selected = $tpl['id'] === $selectedTemplateId ? 'selected' : '';
    $content .= '<option value="' . $tpl['id'] . '" ' . $selected . '>' . rex_escape($tpl['name']) . '</option>';
}

$content .= '</select>';
$content .= '</div>';
$content .= '</div>';

$content .= '<div class="col-md-6">';
$content .= '<div class="form-group">';
$content .= '<label>' . $addon->i18n('template_manager_select_domain') . '</label>';
$content .= '<select class="form-control" id="domain-select" onchange="window.location.href=\'?page=template_manager/config&template_id=' . $selectedTemplateId . '&domain_id=\'+this.value">';

// Domains sortieren: Default (ID 0) immer als letztes
$sortedDomains = [];
$defaultDomain = null;

foreach ($domains as $domain) {
    if ($domain->getId() === 0) {
        $defaultDomain = $domain;
    } else {
        $sortedDomains[] = $domain;
    }
}

// Default-Domain ans Ende hängen
if ($defaultDomain) {
    $sortedDomains[] = $defaultDomain;
}

foreach ($sortedDomains as $domain) {
    $selected = $domain->getId() === $selectedDomainId ? 'selected' : '';
    $content .= '<option value="' . $domain->getId() . '" ' . $selected . '>' . rex_escape($domain->getName()) . '</option>';
}

$content .= '</select>';
$content .= '</div>';
$content .= '</div>';
$content .= '</div>';

// Template Settings laden
$templateData = null;
foreach ($templates as $tpl) {
    if ($tpl['id'] === $selectedTemplateId) {
        $templateData = $tpl;
        break;
    }
}

if (!$templateData) {
    // Panel statt Warning
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

// Sprach-Tabs
$content .= '<ul class="nav nav-tabs" role="tablist">';
foreach ($clangs as $clang) {
    $active = $clang->getId() === rex_clang::getStartId() ? 'active' : '';
    $content .= '<li role="presentation" class="' . $active . '">';
    $content .= '<a href="#lang-' . $clang->getId() . '" role="tab" data-toggle="tab">';
    $content .= rex_escape($clang->getName());
    if ($clang->getId() === rex_clang::getStartId()) {
        $content .= ' <span class="label label-info">Fallback</span>';
    }
    $content .= '</a>';
    $content .= '</li>';
}
$content .= '</ul>';

// Ein gemeinsames Formular für alle Sprachen
$content .= '<form method="post">';
$content .= '<input type="hidden" name="template_id" value="' . $selectedTemplateId . '">';
$content .= '<input type="hidden" name="domain_id" value="' . $selectedDomainId . '">';

// Tab-Content
$content .= '<div class="tab-content">';

foreach ($clangs as $clang) {
    $active = $clang->getId() === rex_clang::getStartId() ? 'active' : '';
    $content .= '<div role="tabpanel" class="tab-pane ' . $active . '" id="lang-' . $clang->getId() . '">';
    
    // Gespeicherte Werte laden
    $manager = new TemplateManager();
    $savedValues = $manager->getTemplateConfigForDomain($selectedTemplateId, $selectedDomainId, $clang->getId());
    
    // Settings-Formular generieren
    $content .= '<div class="panel panel-default">';
    $content .= '<div class="panel-body">';
    
    foreach ($templateData['settings'] as $setting) {
        // Field-Name mit Sprach-ID: settings[clang_id][key]
        $content .= renderSettingField($setting, $savedValues[$setting['key']] ?? $setting['default'], $addon, $clang->getId());
    }
    
    $content .= '</div>';
    $content .= '</div>';
    
    $content .= '</div>'; // tab-pane
}

$content .= '</div>'; // tab-content

// Ein einzelner Speichern-Button für alle Sprachen
$content .= '<button type="submit" name="save" value="1" class="btn btn-primary">';
$content .= '<i class="rex-icon fa-save"></i> ' . $addon->i18n('template_manager_save');
$content .= '</button>';
$content .= '</form>';

$fragment = new rex_fragment();
$fragment->setVar('title', $addon->i18n('template_manager_settings'), false);
$fragment->setVar('body', $content, false);
echo $fragment->parse('core/page/section.php');


/**
 * Rendert ein einzelnes Setting-Feld
 */
function renderSettingField(array $setting, string $value, rex_addon $addon, int $clangId): string
{
    $html = '<div class="form-group">';
    $html .= '<label class="control-label">';
    $html .= rex_escape($setting['label']);
    if (!empty($setting['description'])) {
        $html .= ' <small class="text-muted">(' . rex_escape($setting['description']) . ')</small>';
    }
    $html .= '</label>';
    
    // Field-Name mit Sprach-ID verschachtelt
    $name = 'settings[' . $clangId . '][' . $setting['key'] . ']';
    
    switch ($setting['type']) {
        case 'textarea':
            $html .= '<textarea class="form-control" name="' . $name . '" rows="4">';
            $html .= rex_escape($value);
            $html .= '</textarea>';
            break;
            
        case 'select':
            $html .= '<select class="form-control" name="' . $name . '">';
            foreach ($setting['options'] as $optValue => $optLabel) {
                $selected = $optValue === $value ? 'selected' : '';
                $html .= '<option value="' . rex_escape($optValue) . '" ' . $selected . '>';
                $html .= rex_escape($optLabel);
                $html .= '</option>';
            }
            $html .= '</select>';
            break;
            
        case 'checkbox':
            $checked = $value === '1' || $value === 'true' ? 'checked' : '';
            $html .= '<div class="checkbox">';
            $html .= '<label>';
            $html .= '<input type="hidden" name="' . $name . '" value="0">';
            $html .= '<input type="checkbox" name="' . $name . '" value="1" ' . $checked . '>';
            $html .= ' ' . rex_escape($setting['description']);
            $html .= '</label>';
            $html .= '</div>';
            break;
            
        case 'media':
            $html .= '<div class="input-group">';
            $html .= '<input type="text" class="form-control" name="' . $name . '" value="' . rex_escape($value) . '" id="REX_MEDIA_' . $setting['key'] . '">';
            $html .= '<span class="input-group-btn">';
            $html .= '<a href="#" class="btn btn-default" onclick="openMediaPool(\'REX_MEDIA_' . $setting['key'] . '\'); return false;">';
            $html .= '<i class="rex-icon rex-icon-open-mediapool"></i>';
            $html .= '</a>';
            $html .= '</span>';
            $html .= '</div>';
            break;
            
        case 'link':
            // REDAXO Link Widget verwenden
            static $linkCounter = 0;
            $linkCounter++;
            $html .= rex_var_link::getWidget($linkCounter, $name, $value, []);
            break;
            
        case 'linklist':
            // REDAXO Linklist Widget verwenden
            static $linklistCounter = 0;
            $linklistCounter++;
            $html .= rex_var_linklist::getWidget($linklistCounter, $name, $value, []);
            break;
            
        default: // text, email, url
            $inputType = in_array($setting['type'], ['email', 'url']) ? $setting['type'] : 'text';
            $html .= '<input type="' . $inputType . '" class="form-control" name="' . $name . '" value="' . rex_escape($value) . '">';
            break;
    }
    
    if (!empty($setting['default'])) {
        $html .= '<p class="help-block">';
        $html .= $addon->i18n('template_manager_default_value') . ': ' . rex_escape($setting['default']);
        $html .= '</p>';
    }
    
    $html .= '</div>';
    
    return $html;
}

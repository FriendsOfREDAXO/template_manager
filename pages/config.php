<?php

use FriendsOfRedaxo\TemplateManager\TemplateParser;
use FriendsOfRedaxo\TemplateManager\TemplateManager;

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
usort($domains, function($a, $b) {
    $aId = $a->getId();
    $bId = $b->getId();
    
    // Domains ohne ID (leer oder 0) ans Ende
    if (empty($aId) && !empty($bId)) return 1;
    if (!empty($aId) && empty($bId)) return -1;
    
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

foreach ($domains as $domain) {
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
    
    // Domain + Sprach-Info im Tab anzeigen
    $panel .= '<div class="alert alert-info" style="margin-top: 1rem;">';
    $panel .= '<strong><i class="rex-icon fa-info-circle"></i> ' . $addon->i18n('template_manager_config_domain_language_info') . '</strong> ';
    $panel .= $addon->i18n('template_manager_config_domain_label') . ' <strong>' . rex_escape($currentDomainName) . '</strong> | ';
    $panel .= $addon->i18n('template_manager_config_language_label') . ' <strong>' . rex_escape($clang->getName()) . '</strong>';
    if ($clang->getId() === rex_clang::getStartId()) {
        $panel .= ' <span class="label label-info">Fallback</span>';
    }
    $panel .= '</div>';
    
    // Gespeicherte Werte laden
    $manager = new TemplateManager();
    $savedValues = $manager->getTemplateConfigForDomain($selectedTemplateId, $selectedDomainId, $clang->getId());
    
    // Settings-Formular generieren
    foreach ($templateData['settings'] as $setting) {
        // Field-Name mit Sprach-ID: settings[clang_id][key]
        $panel .= renderSettingField($setting, $savedValues[$setting['key']] ?? $setting['default'], $addon, $clang->getId());
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
            
        case 'colorselect':
            // Farb-Auswahl mit Bootstrap Selectpicker und visueller Anzeige
            $html .= '<select class="form-control selectpicker" name="' . $name . '" data-size="10">';
            
            foreach ($setting['options'] as $colorValue => $colorLabel) {
                $selected = $colorValue === $value ? 'selected' : '';
                
                // Farbiges Badge für visuelle Darstellung
                $badge = '<span style="display:inline-block;width:16px;height:16px;border-radius:3px;background:' . $colorValue . ';margin-right:8px;border:1px solid rgba(0,0,0,0.15);vertical-align:middle;"></span>';
                
                $html .= '<option value="' . rex_escape($colorValue) . '" ' . $selected . ' data-content="' . rex_escape($badge . $colorLabel) . '">';
                $html .= rex_escape($colorLabel);
                $html .= '</option>';
            }
            
            $html .= '</select>';
            
            // Selectpicker initialisieren
            $html .= '<script nonce="' . rex_response::getNonce() . '">
            jQuery(function($) {
                $("select[name=\'' . $name . '\']").selectpicker("refresh");
            });
            </script>';
            break;
            
        case 'sqlselect':
            // SQL-basierte Auswahl mit Bootstrap Selectpicker
            $sqlQuery = $setting['options']['_sql_query'] ?? '';
            
            if (empty($sqlQuery)) {
                $html .= '<p class="text-warning"><i class="rex-icon fa-exclamation-triangle"></i> Keine SQL-Query definiert.</p>';
                $html .= '<input type="hidden" name="' . $name . '" value="">';
                break;
            }
            
            // SQL ausführen (mit Sicherheits-Prüfung)
            try {
                $sql = rex_sql::factory();
                $sql->setQuery($sqlQuery);
                
                $html .= '<select class="form-control selectpicker" name="' . $name . '" data-live-search="true" data-size="10">';
                $html .= '<option value="">-- Bitte wählen --</option>';
                
                for ($i = 0; $i < $sql->getRows(); $i++) {
                    $row = $sql->getRow();
                    
                    // Erwartet: id und name Spalten (oder erstes und zweites Feld)
                    $optValue = $row['id'] ?? $row[array_key_first($row)] ?? '';
                    $optLabel = $row['name'] ?? $row[array_key_last($row)] ?? $optValue;
                    
                    $selected = $optValue == $value ? 'selected' : '';
                    
                    $html .= '<option value="' . rex_escape($optValue) . '" ' . $selected . '>';
                    $html .= rex_escape($optLabel);
                    $html .= '</option>';
                    
                    $sql->next();
                }
                
                $html .= '</select>';
                
                // Selectpicker initialisieren
                $html .= '<script nonce="' . rex_response::getNonce() . '">
                jQuery(function($) {
                    $("select[name=\'' . $name . '\']").selectpicker("refresh");
                });
                </script>';
                
            } catch (\Exception $e) {
                $html .= '<p class="text-danger"><i class="rex-icon fa-exclamation-triangle"></i> SQL-Fehler: ' . rex_escape($e->getMessage()) . '</p>';
                $html .= '<input type="hidden" name="' . $name . '" value="">';
            }
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
            
        case 'medialist':
            // REDAXO Medialist Widget verwenden
            static $medialistCounter = 0;
            $medialistCounter++;
            $html .= rex_var_medialist::getWidget($medialistCounter, $name, $value, []);
            break;
            
        case 'uikit_theme_select':
            // Spezielles UIKit Theme Auswahl-Feld
            if (rex_addon::get('uikit_theme_builder')->isAvailable()) {
                $themeManager = new \UikitThemeBuilder\UikitThemeBuilderManager();
                $themes = $themeManager->listThemes();
                
                $html .= '<select class="form-control selectpicker" name="' . $name . '" data-live-search="true" data-size="10">';
                $html .= '<option value="">-- Kein Theme --</option>';
                
                foreach ($themes as $themeName => $themeData) {
                    $selected = $value === $themeName ? 'selected' : '';
                    
                    // Theme-Farben laden für visuelle Darstellung
                    $colors = \UikitThemeBuilder\ThemeHelper::getThemeColors($themeManager, $themeName);
                    $primaryColor = $colors['primary'] ?? '#1e87f0';
                    
                    // Color Badge als data-content für Bootstrap Selectpicker
                    $badge = '<span style="display:inline-block;width:12px;height:12px;border-radius:2px;background:' . $primaryColor . ';margin-right:6px;border:1px solid rgba(0,0,0,0.1);"></span>';
                    
                    $html .= '<option value="' . rex_escape($themeName) . '" ' . $selected . ' data-content="' . rex_escape($badge . $themeName) . '">';
                    $html .= rex_escape($themeName);
                    $html .= '</option>';
                }
                
                $html .= '</select>';
                
                // Selectpicker initialisieren
                $html .= '<script nonce="' . rex_response::getNonce() . '">
                jQuery(function($) {
                    $("select[name=\'' . $name . '\']").selectpicker("refresh");
                });
                </script>';
            } else {
                $html .= '<p class="text-warning"><i class="rex-icon fa-exclamation-triangle"></i> UIKit Theme Builder Addon ist nicht installiert.</p>';
                $html .= '<input type="hidden" name="' . $name . '" value="">';
            }
            break;
        
        case 'banner_select':
            // UIKit Banner Designer Auswahl
            $sql = rex_sql::factory();
            $banners = $sql->getArray('SELECT id, name FROM ' . rex::getTable('uikit_banner_designs') . ' ORDER BY name ASC');
            
            // Eindeutige ID ohne Sonderzeichen für JavaScript
            $fieldId = 'banner_select_' . $clangId . '_' . preg_replace('/[^a-z0-9_]/i', '_', $setting['key']);
            
            $html .= '<select class="form-control" name="' . $name . '" id="' . $fieldId . '">';
            $html .= '<option value="">-- Kein Banner --</option>';
            
            foreach ($banners as $banner) {
                $selected = $value == $banner['id'] ? 'selected' : '';
                $html .= '<option value="' . (int)$banner['id'] . '" ' . $selected . '>';
                $html .= rex_escape($banner['name']);
                $html .= '</option>';
            }
            
            $html .= '</select>';
            
            // Vorschau-Link wenn Banner ausgewählt
            if (!empty($value) && is_numeric($value)) {
                $previewUrl = rex_url::backendPage('uikit_banner_design/preview', ['id' => (int)$value]);
                $html .= '<p class="help-block" data-preview-container>';
                $html .= '<a href="' . htmlspecialchars_decode($previewUrl) . '" target="_blank" class="btn btn-xs btn-default" style="margin-top: 5px;">';
                $html .= '<i class="rex-icon fa-eye"></i> Banner Vorschau';
                $html .= '</a>';
                $html .= '</p>';
            }
            
            // Live-Update für Vorschau-Link (ohne Selectpicker)
            $html .= '<script nonce="' . rex_response::getNonce() . '">
            jQuery(function($) {
                var $select = $("#' . $fieldId . '");
                
                // Live-Update des Vorschau-Links
                $select.on("change", function() {
                    var bannerId = $(this).val();
                    var $container = $select.parent().find("[data-preview-container]");
                    
                    if (bannerId && bannerId != "") {
                        var baseUrl = "' . htmlspecialchars_decode(rex_url::backendPage('uikit_banner_design/preview')) . '";
                        var previewUrl = baseUrl + "&id=" + bannerId;
                        
                        if ($container.length === 0) {
                            $container = $("<p class=\"help-block\" data-preview-container></p>").insertAfter($select);
                        }
                        $container.html("<a href=\"" + previewUrl + "\" target=\"_blank\" class=\"btn btn-xs btn-default\" style=\"margin-top: 5px;\"><i class=\"rex-icon fa-eye\"></i> Banner Vorschau</a>");
                    } else {
                        $container.remove();
                    }
                });
            });
            </script>';
            break;
            
        case 'color':
            // HTML5 Color Picker mit Text-Eingabe
            $html .= '<div class="input-group">';
            $html .= '<input type="color" class="form-control" name="' . $name . '" value="' . rex_escape($value) . '" style="max-width: 80px;">';
            $html .= '<input type="text" class="form-control" value="' . rex_escape($value) . '" readonly style="font-family: monospace;">';
            $html .= '</div>';
            $html .= '<script nonce="' . rex_response::getNonce() . '">
            jQuery(function($) {
                $("input[name=\'' . $name . '\']").on("change", function() {
                    $(this).next("input").val($(this).val());
                });
            });
            </script>';
            break;
            
        default: // text, email, url, tel, number, date, datetime-local, time
            $validTypes = ['email', 'url', 'tel', 'number', 'date', 'datetime-local', 'time'];
            $inputType = in_array($setting['type'], $validTypes) ? $setting['type'] : 'text';
            
            $extraAttrs = '';
            if ($setting['type'] === 'number') {
                $extraAttrs = ' step="any"'; // Erlaubt Dezimalzahlen
            }
            
            $html .= '<input type="' . $inputType . '" class="form-control" name="' . $name . '" value="' . rex_escape($value) . '"' . $extraAttrs . '>';
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

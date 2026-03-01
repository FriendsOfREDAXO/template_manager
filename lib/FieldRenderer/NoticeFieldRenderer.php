<?php

namespace FriendsOfRedaxo\TemplateManager\FieldRenderer;

use FriendsOfRedaxo\TemplateManager\AbstractFieldRenderer;

/**
 * Renderer für Hinweis-/Notiz-Felder (notice).
 *
 * Rendert eine nicht-editierbare Bootstrap-Alert-Box im Einstellungsformular.
 * Das Feld speichert keinen Wert – es dient rein zur Anzeige von Hinweisen,
 * Warnungen oder Notizen für Redakteure.
 *
 * DocBlock-Format:
 *   key: notice|Label|Typ|Nachrichtentext
 *
 * Typen (Bootstrap 3 Alert-Klassen):
 *   info    → blau   (Standard)
 *   success → grün
 *   warning → gelb/orange
 *   danger  → rot
 *
 * Beispiel:
 *   tm_hinweis:     notice|Hinweis||Bitte immer alle Pflichtfelder ausfüllen.
 *   tm_warnung:     notice|Achtung|warning|Das Logo sollte mindestens 300×100 px groß sein.
 *   tm_kritisch:    notice|Wichtig|danger|Dieses Feld wird im Header verwendet!
 */
class NoticeFieldRenderer extends AbstractFieldRenderer
{
    private const ALLOWED_TYPES = ['info', 'success', 'warning', 'danger'];

    public function supports(string $type): bool
    {
        return $type === 'notice';
    }

    public function render(array $setting, string $value, string $name, int $clangId): string
    {
        // Alert-Typ aus „default"-Feld lesen, Fallback: info
        $alertType = trim((string) ($setting['default'] ?? 'info'));
        if (!in_array($alertType, self::ALLOWED_TYPES, true)) {
            $alertType = 'info';
        }

        $icon = match ($alertType) {
            'success' => 'fa-check-circle',
            'warning' => 'fa-exclamation-triangle',
            'danger'  => 'fa-times-circle',
            default   => 'fa-info-circle',
        };

        $label   = \rex_escape($setting['label']);
        $message = \rex_escape($setting['description'] ?? '');

        $html  = '<div class="alert alert-' . $alertType . '" style="margin-bottom:12px;">';
        if ($label !== '') {
            $html .= '<strong><i class="rex-icon ' . $icon . '"></i> ' . $label . '</strong>';
            if ($message !== '') {
                $html .= '<br>';
            }
        }
        $html .= $message;
        $html .= '</div>';

        return $html;
    }
}

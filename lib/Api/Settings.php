<?php

namespace FriendsOfRedaxo\TemplateManager\Api;

use FriendsOfRedaxo\TemplateManager\FieldRendererManager;
use FriendsOfRedaxo\TemplateManager\TemplateManager;
use FriendsOfRedaxo\TemplateManager\TemplateParser;
use rex;
use rex_api_function;
use rex_api_result;
use rex_clang;
use rex_csrf_token;
use rex_i18n;
use rex_response;
use rex_yrewrite;

/**
 * Einstellungen im Fenster bearbeiten (Overlay im Backend, siehe assets/template_manager.js).
 *
 * GET  ?rex-api-call=template_manager_settings&key=tm_…&domain_id=…[&template_id=…][&clang=…]
 *      Liefert die Gruppe, zu der das Feld gehört, als Formular-HTML (JSON: title, subtitle, html, page_url).
 *
 * POST dieselbe Adresse, Body: settings[<clang>][<key>]=… (multipart/form-data)
 *      Speichert nur die Felder dieser Gruppe – andere Schlüssel werden ignoriert.
 *
 * Rechte: Template-Manager-Recht wie die Einstellungsseite; Gruppen mit Rollen nur für berechtigte Benutzer.
 */
class Settings extends rex_api_function
{
    public function execute(): rex_api_result
    {
        rex_response::cleanOutputBuffers();

        $user = rex::getUser();
        if (null === $user || !($user->isAdmin() || $user->hasPerm('template_manager[]'))) {
            $this->fail(rex_response::HTTP_FORBIDDEN, 'Permission denied');
        }

        $key = (string) preg_replace('/[^\w-]/', '', rex_request('key', 'string', ''));
        $templateId = rex_request('template_id', 'int', 0) ?: (int) TemplateManager::findTemplateId('' !== $key ? $key : null);
        $domainId = rex_request('domain_id', 'int', 0);
        $clangId = rex_request('clang', 'int', 0) ?: rex_clang::getStartId();

        $template = null;
        foreach (TemplateParser::getAllTemplates() as $t) {
            if ((int) $t['id'] === $templateId) {
                $template = $t;
                break;
            }
        }
        if (null === $template || !isset($template['settings'][$key])) {
            $this->fail(rex_response::HTTP_NOT_FOUND, 'Setting not found');
        }

        // Gruppe des Feldes (ohne Gruppen: nur das Feld selbst)
        $group = ['name' => $template['settings'][$key]['label'] ?? $key, 'icon' => '', 'roles' => [], 'fields' => [$key]];
        foreach ($template['groups'] ?? [] as $g) {
            if (in_array($key, $g['fields'], true)) {
                $group = $g;
                break;
            }
        }
        if (!$this->canAccessGroup($group)) {
            $this->fail(rex_response::HTTP_FORBIDDEN, 'Permission denied');
        }

        if ('post' === rex_request_method()) {
            $this->save($templateId, $domainId, $clangId, $group['fields']);
        }

        $manager = new TemplateManager();
        $saved = $manager->getTemplateConfigForDomain($templateId, $domainId, $clangId);
        $html = '';
        foreach ($group['fields'] as $fieldKey) {
            if (!isset($template['settings'][$fieldKey])) {
                continue;
            }
            $setting = $template['settings'][$fieldKey];
            $html .= '<div class="tm-field' . ($fieldKey === $key ? ' tm-field--focus' : '') . '" data-tm-key="' . rex_escape($fieldKey) . '">';
            $html .= FieldRendererManager::renderField($setting, (string) ($saved[$fieldKey] ?? $setting['default']), $clangId);
            $html .= '</div>';
        }

        $domain = rex_yrewrite::getDomainById($domainId);
        rex_response::sendJson([
            'title' => (string) $group['name'],
            'icon' => (string) ($group['icon'] ?? ''),
            'subtitle' => trim($template['name'] . ($domain ? ' · ' . $domain->getName() : '') . (count(rex_clang::getAll()) > 1 ? ' · ' . rex_clang::get($clangId)?->getName() : '')),
            'html' => $html,
            'page_url' => TemplateManager::getSettingsUrl($key, $domainId, $templateId),
        ]);
        exit;
    }

    /** @param list<string> $allowed */
    private function save(int $templateId, int $domainId, int $clangId, array $allowed): void
    {
        if (!rex_csrf_token::factory(self::class)->isValid()) {
            $this->fail(rex_response::HTTP_FORBIDDEN, rex_i18n::msg('csrf_token_invalid'));
        }
        $posted = rex_post('settings', 'array', []);
        $values = is_array($posted[$clangId] ?? null) ? $posted[$clangId] : [];
        $values = array_intersect_key($values, array_flip($allowed));
        if ([] !== $values) {
            (new TemplateManager())->saveSettings($templateId, $domainId, $clangId, array_map(static fn ($v): string => is_array($v) ? implode(',', $v) : (string) $v, $values));
            TemplateManager::clearCache();
        }
        rex_response::sendJson(['success' => true, 'message' => rex_i18n::msg('template_manager_saved')]);
        exit;
    }

    /** @param array{roles?: list<string>} $group */
    private function canAccessGroup(array $group): bool
    {
        $user = rex::getUser();
        if (null === $user) {
            return false;
        }
        if ($user->isAdmin() || empty($group['roles'])) {
            return true;
        }
        foreach ($group['roles'] as $role) {
            if ($user->hasPerm($role)) {
                return true;
            }
        }

        return false;
    }

    private function fail(string $status, string $message): never
    {
        rex_response::setStatus($status);
        rex_response::sendJson(['error' => $message]);
        exit;
    }

    protected function requiresCsrfProtection(): bool
    {
        // GET liefert nur das Formular; POST prüft das Token selbst (siehe save())
        return false;
    }
}

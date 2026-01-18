<?php

namespace FriendsOfRedaxo\TemplateManager\FieldRenderer;

use FriendsOfRedaxo\TemplateManager\AbstractFieldRenderer;

/**
 * Renderer für Social Media Links mit Repeater und Sortierung
 * 
 * Unterstützt Font Awesome und UIKit Icons
 * Format: JSON-Array mit icon, url, label
 * 
 * Icon-Modus über default-Wert konfigurierbar:
 * - fa = nur Font Awesome
 * - uk = nur UIKit  
 * - both oder leer = beide Icon-Sets
 * 
 * Beispiel: tm_social_links: social_links|Social Media Links|fa|Nur Font Awesome Icons
 */
class SocialLinksFieldRenderer extends AbstractFieldRenderer
{
    private static int $instanceCounter = 0;
    
    /** @var string Aktueller Icon-Modus für diese Instanz */
    private string $iconMode = 'both';
    
    /**
     * Vordefinierte Social Media Icons (Font Awesome + UIKit)
     */
    private const SOCIAL_ICONS = [
        // Font Awesome Icons
        'fa-facebook' => ['label' => 'Facebook', 'group' => 'Font Awesome'],
        'fa-facebook-f' => ['label' => 'Facebook (f)', 'group' => 'Font Awesome'],
        'fa-twitter' => ['label' => 'Twitter/X', 'group' => 'Font Awesome'],
        'fa-x-twitter' => ['label' => 'X (Twitter)', 'group' => 'Font Awesome'],
        'fa-instagram' => ['label' => 'Instagram', 'group' => 'Font Awesome'],
        'fa-linkedin' => ['label' => 'LinkedIn', 'group' => 'Font Awesome'],
        'fa-linkedin-in' => ['label' => 'LinkedIn (in)', 'group' => 'Font Awesome'],
        'fa-xing' => ['label' => 'Xing', 'group' => 'Font Awesome'],
        'fa-youtube' => ['label' => 'YouTube', 'group' => 'Font Awesome'],
        'fa-tiktok' => ['label' => 'TikTok', 'group' => 'Font Awesome'],
        'fa-pinterest' => ['label' => 'Pinterest', 'group' => 'Font Awesome'],
        'fa-whatsapp' => ['label' => 'WhatsApp', 'group' => 'Font Awesome'],
        'fa-telegram' => ['label' => 'Telegram', 'group' => 'Font Awesome'],
        'fa-github' => ['label' => 'GitHub', 'group' => 'Font Awesome'],
        'fa-gitlab' => ['label' => 'GitLab', 'group' => 'Font Awesome'],
        'fa-discord' => ['label' => 'Discord', 'group' => 'Font Awesome'],
        'fa-slack' => ['label' => 'Slack', 'group' => 'Font Awesome'],
        'fa-mastodon' => ['label' => 'Mastodon', 'group' => 'Font Awesome'],
        'fa-threads' => ['label' => 'Threads', 'group' => 'Font Awesome'],
        'fa-bluesky' => ['label' => 'Bluesky', 'group' => 'Font Awesome'],
        'fa-reddit' => ['label' => 'Reddit', 'group' => 'Font Awesome'],
        'fa-snapchat' => ['label' => 'Snapchat', 'group' => 'Font Awesome'],
        'fa-vimeo' => ['label' => 'Vimeo', 'group' => 'Font Awesome'],
        'fa-dribbble' => ['label' => 'Dribbble', 'group' => 'Font Awesome'],
        'fa-behance' => ['label' => 'Behance', 'group' => 'Font Awesome'],
        'fa-flickr' => ['label' => 'Flickr', 'group' => 'Font Awesome'],
        'fa-spotify' => ['label' => 'Spotify', 'group' => 'Font Awesome'],
        'fa-soundcloud' => ['label' => 'SoundCloud', 'group' => 'Font Awesome'],
        'fa-twitch' => ['label' => 'Twitch', 'group' => 'Font Awesome'],
        'fa-rss' => ['label' => 'RSS Feed', 'group' => 'Font Awesome'],
        'fa-envelope' => ['label' => 'E-Mail', 'group' => 'Font Awesome'],
        'fa-phone' => ['label' => 'Telefon', 'group' => 'Font Awesome'],
        'fa-globe' => ['label' => 'Webseite', 'group' => 'Font Awesome'],
        'fa-link' => ['label' => 'Link', 'group' => 'Font Awesome'],
        
        // UIKit Icons
        'uk-icon-facebook' => ['label' => 'Facebook', 'group' => 'UIKit'],
        'uk-icon-twitter' => ['label' => 'Twitter', 'group' => 'UIKit'],
        'uk-icon-instagram' => ['label' => 'Instagram', 'group' => 'UIKit'],
        'uk-icon-linkedin' => ['label' => 'LinkedIn', 'group' => 'UIKit'],
        'uk-icon-youtube' => ['label' => 'YouTube', 'group' => 'UIKit'],
        'uk-icon-tiktok' => ['label' => 'TikTok', 'group' => 'UIKit'],
        'uk-icon-pinterest' => ['label' => 'Pinterest', 'group' => 'UIKit'],
        'uk-icon-whatsapp' => ['label' => 'WhatsApp', 'group' => 'UIKit'],
        'uk-icon-github' => ['label' => 'GitHub', 'group' => 'UIKit'],
        'uk-icon-discord' => ['label' => 'Discord', 'group' => 'UIKit'],
        'uk-icon-mastodon' => ['label' => 'Mastodon', 'group' => 'UIKit'],
        'uk-icon-reddit' => ['label' => 'Reddit', 'group' => 'UIKit'],
        'uk-icon-dribbble' => ['label' => 'Dribbble', 'group' => 'UIKit'],
        'uk-icon-behance' => ['label' => 'Behance', 'group' => 'UIKit'],
        'uk-icon-flickr' => ['label' => 'Flickr', 'group' => 'UIKit'],
        'uk-icon-vimeo' => ['label' => 'Vimeo', 'group' => 'UIKit'],
        'uk-icon-mail' => ['label' => 'E-Mail', 'group' => 'UIKit'],
        'uk-icon-receiver' => ['label' => 'Telefon', 'group' => 'UIKit'],
        'uk-icon-world' => ['label' => 'Webseite', 'group' => 'UIKit'],
        'uk-icon-link' => ['label' => 'Link', 'group' => 'UIKit'],
        'uk-icon-rss' => ['label' => 'RSS', 'group' => 'UIKit'],
    ];
    
    public function supports(string $type): bool
    {
        return $type === 'social_links';
    }
    
    /**
     * Icons basierend auf Modus filtern
     * @return array<string, array{label: string, group: string}>
     */
    private function getFilteredIcons(): array
    {
        $icons = self::SOCIAL_ICONS;
        
        if ($this->iconMode === 'fa') {
            return array_filter($icons, fn($data) => $data['group'] === 'Font Awesome');
        }
        
        if ($this->iconMode === 'uk') {
            return array_filter($icons, fn($data) => $data['group'] === 'UIKit');
        }
        
        return $icons;
    }
    
    public function render(array $setting, string $value, string $name, int $clangId): string
    {
        self::$instanceCounter++;
        $instanceId = 'social-links-' . self::$instanceCounter;
        
        // Icon-Modus aus default-Wert lesen (fa, uk, both)
        $this->iconMode = strtolower(trim($setting['default'] ?? 'both'));
        if (!in_array($this->iconMode, ['fa', 'uk', 'both'], true)) {
            $this->iconMode = 'both';
        }
        
        // JSON dekodieren oder leeres Array
        $links = [];
        if (!empty($value)) {
            $decoded = json_decode($value, true);
            if (is_array($decoded)) {
                $links = $decoded;
            }
        }
        
        $html = '<div class="form-group">';
        $html .= '<label class="control-label">' . \rex_escape($setting['label']) . '</label>';
        
        if (!empty($setting['description'])) {
            $html .= '<p class="help-block">' . nl2br(\rex_escape($setting['description'])) . '</p>';
        }
        
        // Icon-Modus Hinweis
        $modeLabels = ['fa' => 'Font Awesome', 'uk' => 'UIKit', 'both' => 'Font Awesome + UIKit'];
        $html .= '<p class="help-block text-muted"><small><i class="rex-icon fa-info-circle"></i> Icon-Set: ' . $modeLabels[$this->iconMode] . '</small></p>';
        
        // Container für Social Links mit Icon-Modus als data-Attribut
        $html .= '<div class="social-links-container" id="' . $instanceId . '" data-name="' . \rex_escape($name) . '" data-icon-mode="' . $this->iconMode . '">';
        
        // Hidden Input für JSON-Wert
        $html .= '<input type="hidden" name="' . $name . '" class="social-links-value" value="' . \rex_escape($value) . '">';
        
        // Items Container (sortierbar)
        $html .= '<div class="social-links-items">';
        
        foreach ($links as $index => $link) {
            $html .= $this->renderItem($index, $link);
        }
        
        $html .= '</div>'; // .social-links-items
        
        // Add Button
        $html .= '<div class="social-links-actions" style="margin-top: 10px;">';
        $html .= '<button type="button" class="btn btn-primary btn-sm social-links-add">';
        $html .= '<i class="rex-icon fa-plus"></i> Social Link hinzufügen';
        $html .= '</button>';
        $html .= '</div>';
        
        $html .= '</div>'; // .social-links-container
        $html .= '</div>'; // .form-group
        
        // CSS und JavaScript (nur einmal)
        if (self::$instanceCounter === 1) {
            $html .= $this->getStyles();
            $html .= $this->getScript();
        }
        
        return $html;
    }
    
    /**
     * Einzelnes Item rendern
     */
    private function renderItem(int $index, array $link): string
    {
        $icon = $link['icon'] ?? '';
        $url = $link['url'] ?? '';
        $label = $link['label'] ?? '';
        
        $html = '<div class="social-links-item panel panel-default" data-index="' . $index . '">';
        $html .= '<div class="panel-body" style="display: flex; gap: 10px; align-items: center; flex-wrap: wrap;">';
        
        // Sortier-Handle
        $html .= '<div class="social-links-handle" style="cursor: move; padding: 5px;" title="Ziehen zum Sortieren">';
        $html .= '<i class="rex-icon fa-bars"></i>';
        $html .= '</div>';
        
        // Icon-Auswahl (gefiltert nach Modus)
        $html .= '<div style="flex: 0 0 200px;">';
        $html .= '<select class="form-control social-links-icon">';
        $html .= '<option value="">-- Icon wählen --</option>';
        
        // Gruppierte Icons (gefiltert)
        $filteredIcons = $this->getFilteredIcons();
        $groups = [];
        foreach ($filteredIcons as $iconClass => $iconData) {
            $groups[$iconData['group']][$iconClass] = $iconData['label'];
        }
        
        foreach ($groups as $groupName => $icons) {
            $html .= '<optgroup label="' . \rex_escape($groupName) . '">';
            foreach ($icons as $iconClass => $iconLabel) {
                $selected = ($icon === $iconClass) ? ' selected' : '';
                $html .= '<option value="' . \rex_escape($iconClass) . '"' . $selected . '>' . \rex_escape($iconLabel) . '</option>';
            }
            $html .= '</optgroup>';
        }
        
        $html .= '</select>';
        $html .= '</div>';
        
        // Icon-Vorschau
        $html .= '<div class="social-links-icon-preview" style="width: 30px; text-align: center; font-size: 18px;">';
        $html .= $this->renderIconPreview($icon);
        $html .= '</div>';
        
        // URL
        $html .= '<div style="flex: 1; min-width: 200px;">';
        $html .= '<input type="text" class="form-control social-links-url" placeholder="https://..." value="' . \rex_escape($url) . '">';
        $html .= '</div>';
        
        // Label (optional)
        $html .= '<div style="flex: 0 0 150px;">';
        $html .= '<input type="text" class="form-control social-links-label" placeholder="Label (optional)" value="' . \rex_escape($label) . '">';
        $html .= '</div>';
        
        // Sortier-Pfeile
        $html .= '<div class="btn-group social-links-sort">';
        $html .= '<button type="button" class="btn btn-default btn-xs social-links-up" title="Nach oben"><i class="rex-icon fa-chevron-up"></i></button>';
        $html .= '<button type="button" class="btn btn-default btn-xs social-links-down" title="Nach unten"><i class="rex-icon fa-chevron-down"></i></button>';
        $html .= '</div>';
        
        // Löschen
        $html .= '<button type="button" class="btn btn-danger btn-xs social-links-remove" title="Entfernen">';
        $html .= '<i class="rex-icon fa-trash"></i>';
        $html .= '</button>';
        
        $html .= '</div>'; // .panel-body
        $html .= '</div>'; // .social-links-item
        
        return $html;
    }
    
    /**
     * Icon-Vorschau rendern
     */
    private function renderIconPreview(string $icon): string
    {
        if (empty($icon)) {
            return '<i class="rex-icon fa-question" style="color: #ccc;"></i>';
        }
        
        if (str_starts_with($icon, 'uk-icon-')) {
            $ukIcon = str_replace('uk-icon-', '', $icon);
            return '<span uk-icon="icon: ' . \rex_escape($ukIcon) . '"></span>';
        }
        
        return '<i class="rex-icon ' . \rex_escape($icon) . '"></i>';
    }
    
    /**
     * CSS-Styles
     */
    private function getStyles(): string
    {
        return '
<style>
.social-links-container {
    max-width: 100%;
}
.social-links-items {
    display: flex;
    flex-direction: column;
    gap: 8px;
}
.social-links-item {
    margin: 0;
    transition: box-shadow 0.2s ease;
}
.social-links-item:hover {
    box-shadow: 0 2px 8px rgba(0,0,0,0.1);
}
.social-links-item.dragging {
    opacity: 0.5;
    box-shadow: 0 4px 12px rgba(0,0,0,0.2);
}
.social-links-handle:hover {
    color: #337ab7;
}
.social-links-icon-preview {
    display: flex;
    align-items: center;
    justify-content: center;
}
.social-links-sort .btn {
    padding: 2px 6px;
}
@media (max-width: 768px) {
    .social-links-item .panel-body {
        flex-direction: column;
        align-items: stretch !important;
    }
    .social-links-item .panel-body > div {
        flex: 1 1 100% !important;
        min-width: 100% !important;
    }
    .social-links-handle {
        display: none;
    }
}
</style>';
    }
    
    /**
     * JavaScript
     */
    private function getScript(): string
    {
        // Alle Icon-Daten für JavaScript
        $allIconsJson = json_encode(self::SOCIAL_ICONS);
        $nonce = $this->getNonce();
        
        return '<script nonce="' . $nonce . '">
(function() {
    const ALL_SOCIAL_ICONS = ' . $allIconsJson . ';
    
    // Icons nach Modus filtern
    function getFilteredIcons(mode) {
        if (mode === "fa") {
            var filtered = {};
            for (var key in ALL_SOCIAL_ICONS) {
                if (ALL_SOCIAL_ICONS[key].group === "Font Awesome") {
                    filtered[key] = ALL_SOCIAL_ICONS[key];
                }
            }
            return filtered;
        }
        if (mode === "uk") {
            var filtered = {};
            for (var key in ALL_SOCIAL_ICONS) {
                if (ALL_SOCIAL_ICONS[key].group === "UIKit") {
                    filtered[key] = ALL_SOCIAL_ICONS[key];
                }
            }
            return filtered;
        }
        return ALL_SOCIAL_ICONS;
    }
    
    // Icon-Vorschau rendern
    function renderIconPreview(icon) {
        if (!icon) {
            return \'<i class="rex-icon fa-question" style="color: #ccc;"></i>\';
        }
        if (icon.startsWith("uk-icon-")) {
            const ukIcon = icon.replace("uk-icon-", "");
            return \'<span uk-icon="icon: \' + ukIcon + \'"></span>\';
        }
        return \'<i class="rex-icon \' + icon + \'"></i>\';
    }
    
    // Wert aktualisieren
    function updateValue(container) {
        const items = container.querySelectorAll(".social-links-item");
        const links = [];
        
        items.forEach(function(item) {
            const icon = item.querySelector(".social-links-icon").value;
            const url = item.querySelector(".social-links-url").value;
            const label = item.querySelector(".social-links-label").value;
            
            if (icon || url) {
                links.push({ icon: icon, url: url, label: label });
            }
        });
        
        container.querySelector(".social-links-value").value = JSON.stringify(links);
    }
    
    // Neues Item erstellen
    function createItem(container, data) {
        data = data || {};
        const items = container.querySelector(".social-links-items");
        const index = items.children.length;
        
        // Icon-Modus aus Container-Attribut lesen
        const iconMode = container.dataset.iconMode || "both";
        const SOCIAL_ICONS = getFilteredIcons(iconMode);
        
        const item = document.createElement("div");
        item.className = "social-links-item panel panel-default";
        item.dataset.index = index;
        
        // Icon-Optionen generieren
        var iconOptions = \'<option value="">-- Icon wählen --</option>\';
        var groups = {};
        
        for (var iconClass in SOCIAL_ICONS) {
            var iconData = SOCIAL_ICONS[iconClass];
            if (!groups[iconData.group]) groups[iconData.group] = [];
            groups[iconData.group].push({ cls: iconClass, label: iconData.label });
        }
        
        for (var groupName in groups) {
            iconOptions += \'<optgroup label="\' + groupName + \'">\';
            groups[groupName].forEach(function(icon) {
                var selected = (data.icon === icon.cls) ? " selected" : "";
                iconOptions += \'<option value="\' + icon.cls + \'"\' + selected + \'>\' + icon.label + \'</option>\';
            });
            iconOptions += \'</optgroup>\';
        }
        
        item.innerHTML = \'<div class="panel-body" style="display: flex; gap: 10px; align-items: center; flex-wrap: wrap;">\' +
            \'<div class="social-links-handle" style="cursor: move; padding: 5px;" title="Ziehen zum Sortieren">\' +
                \'<i class="rex-icon fa-bars"></i>\' +
            \'</div>\' +
            \'<div style="flex: 0 0 200px;">\' +
                \'<select class="form-control social-links-icon">\' + iconOptions + \'</select>\' +
            \'</div>\' +
            \'<div class="social-links-icon-preview" style="width: 30px; text-align: center; font-size: 18px;">\' +
                renderIconPreview(data.icon || "") +
            \'</div>\' +
            \'<div style="flex: 1; min-width: 200px;">\' +
                \'<input type="text" class="form-control social-links-url" placeholder="https://..." value="\' + (data.url || "") + \'">\' +
            \'</div>\' +
            \'<div style="flex: 0 0 150px;">\' +
                \'<input type="text" class="form-control social-links-label" placeholder="Label (optional)" value="\' + (data.label || "") + \'">\' +
            \'</div>\' +
            \'<div class="btn-group social-links-sort">\' +
                \'<button type="button" class="btn btn-default btn-xs social-links-up" title="Nach oben"><i class="rex-icon fa-chevron-up"></i></button>\' +
                \'<button type="button" class="btn btn-default btn-xs social-links-down" title="Nach unten"><i class="rex-icon fa-chevron-down"></i></button>\' +
            \'</div>\' +
            \'<button type="button" class="btn btn-danger btn-xs social-links-remove" title="Entfernen">\' +
                \'<i class="rex-icon fa-trash"></i>\' +
            \'</button>\' +
        \'</div>\';
        
        items.appendChild(item);
        bindItemEvents(item, container);
        updateValue(container);
    }
    
    // Item-Events binden
    function bindItemEvents(item, container) {
        // Icon-Auswahl
        item.querySelector(".social-links-icon").addEventListener("change", function() {
            var preview = item.querySelector(".social-links-icon-preview");
            preview.innerHTML = renderIconPreview(this.value);
            updateValue(container);
        });
        
        // URL/Label ändern
        item.querySelector(".social-links-url").addEventListener("input", function() { updateValue(container); });
        item.querySelector(".social-links-label").addEventListener("input", function() { updateValue(container); });
        
        // Nach oben
        item.querySelector(".social-links-up").addEventListener("click", function() {
            var prev = item.previousElementSibling;
            if (prev) {
                item.parentNode.insertBefore(item, prev);
                updateValue(container);
            }
        });
        
        // Nach unten
        item.querySelector(".social-links-down").addEventListener("click", function() {
            var next = item.nextElementSibling;
            if (next) {
                item.parentNode.insertBefore(next, item);
                updateValue(container);
            }
        });
        
        // Entfernen
        item.querySelector(".social-links-remove").addEventListener("click", function() {
            if (confirm("Diesen Social Link wirklich entfernen?")) {
                item.remove();
                updateValue(container);
            }
        });
        
        // Drag & Drop
        item.setAttribute("draggable", "true");
        
        item.addEventListener("dragstart", function(e) {
            item.classList.add("dragging");
            e.dataTransfer.effectAllowed = "move";
        });
        
        item.addEventListener("dragend", function() {
            item.classList.remove("dragging");
            updateValue(container);
        });
        
        item.addEventListener("dragover", function(e) {
            e.preventDefault();
            var dragging = container.querySelector(".dragging");
            if (dragging && dragging !== item) {
                var rect = item.getBoundingClientRect();
                var mid = rect.top + rect.height / 2;
                if (e.clientY < mid) {
                    item.parentNode.insertBefore(dragging, item);
                } else {
                    item.parentNode.insertBefore(dragging, item.nextSibling);
                }
            }
        });
    }
    
    // Initialisierung
    document.querySelectorAll(".social-links-container").forEach(function(container) {
        // Bestehende Items initialisieren
        container.querySelectorAll(".social-links-item").forEach(function(item) {
            bindItemEvents(item, container);
        });
        
        // Add-Button
        container.querySelector(".social-links-add").addEventListener("click", function() {
            createItem(container);
        });
    });
})();
</script>';
    }
    
    /**
     * Nonce für Script-Tags
     */
    private function getNonce(): string
    {
        return \rex_response::getNonce();
    }
}

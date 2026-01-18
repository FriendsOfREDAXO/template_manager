<?php

namespace FriendsOfRedaxo\TemplateManager;

/**
 * Widget für externe Link-Listen mit Repeater-Funktionalität
 * Format: Name|URL|Beschreibung (optional) - ein Link pro Zeile
 */
class ExternalLinklistWidget
{
    /**
     * Rendert das Widget für externe Link-Listen
     * 
     * @param string $name Feldname
     * @param string $value Aktueller Wert (Zeilen im Format: Name|URL|Beschreibung)
     * @param string $label Label des Feldes
     * @param string $description Beschreibung/Hinweistext
     * @return string HTML des Widgets
     */
    public static function render(string $name, string $value, string $label, string $description = ''): string
    {
        $escapedValue = htmlspecialchars($value ?? '', ENT_QUOTES, 'UTF-8');
        $escapedLabel = htmlspecialchars($label, ENT_QUOTES, 'UTF-8');
        $escapedName = htmlspecialchars($name, ENT_QUOTES, 'UTF-8');
        
        $html = '<div class="form-group">';
        $html .= '<label class="control-label" for="' . $escapedName . '">' . $escapedLabel . '</label>';
        
        if (!empty($description)) {
            $html .= '<p class="help-block">' . nl2br(htmlspecialchars($description, ENT_QUOTES, 'UTF-8')) . '</p>';
        }
        
        // Hinweistext zum Format
        $html .= '<div class="alert alert-info" style="margin-bottom: 10px;">';
        $html .= '<strong>Format:</strong> Ein Link pro Zeile<br>';
        $html .= '<code>Name|URL|Beschreibung (optional)</code><br><br>';
        $html .= '<strong>Beispiel:</strong><br>';
        $html .= '<code>WDFV|https://wdfv.de|Westdeutscher Fußballverband<br>';
        $html .= 'FVN|https://fvn.de|Fußballverband Niederrhein</code>';
        $html .= '</div>';
        
        $html .= '<textarea 
            class="form-control" 
            id="' . $escapedName . '" 
            name="' . $escapedName . '" 
            rows="8"
            placeholder="Name|URL|Beschreibung&#10;Name|URL|Beschreibung&#10;..."
            style="font-family: monospace; font-size: 13px;">' . $escapedValue . '</textarea>';
        
        // Live-Vorschau der Links
        $html .= '<div class="external-linklist-preview" style="margin-top: 15px; padding: 10px; background: #f8f9fa; border: 1px solid #dee2e6; border-radius: 4px; display: none;">';
        $html .= '<strong>Vorschau:</strong>';
        $html .= '<ul class="preview-list" style="margin-top: 10px; margin-bottom: 0;"></ul>';
        $html .= '</div>';
        
        // JavaScript für Live-Vorschau
        $html .= '<script>
        (function() {
            const textarea = document.getElementById("' . $escapedName . '");
            const preview = textarea.parentElement.querySelector(".external-linklist-preview");
            const previewList = preview.querySelector(".preview-list");
            
            function updatePreview() {
                const value = textarea.value.trim();
                
                if (!value) {
                    preview.style.display = "none";
                    return;
                }
                
                const lines = value.split("\\n").filter(line => {
                    line = line.trim();
                    return line && !line.startsWith("#") && !line.startsWith("//");
                });
                
                if (lines.length === 0) {
                    preview.style.display = "none";
                    return;
                }
                
                preview.style.display = "block";
                previewList.innerHTML = "";
                
                lines.forEach((line, index) => {
                    const parts = line.split("|").map(p => p.trim());
                    const li = document.createElement("li");
                    
                    if (parts.length < 2) {
                        li.innerHTML = "<span style=\"color: #dc3545;\">❌ Zeile " + (index + 1) + ": Ungültiges Format (mindestens Name|URL erforderlich)</span>";
                        li.style.marginBottom = "5px";
                    } else {
                        const name = parts[0];
                        const url = parts[1];
                        const desc = parts[2] || "";
                        
                        // URL-Validierung
                        const urlPattern = /^https?:\\/\\/.+/i;
                        const isValidUrl = urlPattern.test(url);
                        
                        if (!isValidUrl) {
                            li.innerHTML = "<span style=\"color: #dc3545;\">❌ " + name + ": Ungültige URL (muss mit http:// oder https:// beginnen)</span>";
                            li.style.marginBottom = "5px";
                        } else {
                            const descText = desc ? " <small style=\"color: #6c757d;\">(" + desc + ")</small>" : "";
                            li.innerHTML = "<span style=\"color: #28a745;\">✓</span> <a href=\"" + url + "\" target=\"_blank\" rel=\"noopener\">" + name + "</a>" + descText;
                            li.style.marginBottom = "5px";
                        }
                    }
                    
                    previewList.appendChild(li);
                });
            }
            
            textarea.addEventListener("input", updatePreview);
            textarea.addEventListener("change", updatePreview);
            
            // Initial update
            updatePreview();
        })();
        </script>';
        
        $html .= '</div>';
        
        return $html;
    }
    
    /**
     * Parsed eine externe Link-Liste in ein Array
     * 
     * @param string $value Rohdaten (Name|URL|Beschreibung pro Zeile)
     * @return array Array mit Links: [['name' => '...', 'url' => '...', 'description' => '...'], ...]
     */
    public static function parse(string $value): array
    {
        if (empty($value)) {
            return [];
        }
        
        $links = [];
        $lines = array_filter(array_map('trim', explode("\n", $value)));
        
        foreach ($lines as $line) {
            // Kommentare überspringen
            if (strpos($line, '#') === 0 || strpos($line, '//') === 0) {
                continue;
            }
            
            $parts = array_map('trim', explode('|', $line));
            
            // Mindestens Name und URL erforderlich
            if (count($parts) < 2) {
                continue;
            }
            
            // URL-Validierung
            if (!filter_var($parts[1], FILTER_VALIDATE_URL)) {
                continue;
            }
            
            $links[] = [
                'name' => $parts[0],
                'url' => $parts[1],
                'description' => $parts[2] ?? ''
            ];
        }
        
        return $links;
    }
    
    /**
     * Rendert HTML für geparste Links
     * 
     * @param string $value Rohdaten
     * @param bool $openInNewTab Links in neuem Tab öffnen
     * @return string HTML <li>-Elemente
     */
    public static function renderHtml(string $value, bool $openInNewTab = true): string
    {
        $links = self::parse($value);
        
        if (empty($links)) {
            return '';
        }
        
        $html = '';
        $target = $openInNewTab ? ' target="_blank" rel="noopener noreferrer"' : '';
        
        foreach ($links as $link) {
            $name = htmlspecialchars($link['name'], ENT_QUOTES, 'UTF-8');
            $url = htmlspecialchars($link['url'], ENT_QUOTES, 'UTF-8');
            $description = !empty($link['description']) 
                ? htmlspecialchars($link['description'], ENT_QUOTES, 'UTF-8') 
                : '';
            
            $titleAttr = !empty($description) ? ' title="' . $description . '"' : '';
            
            $html .= '<li><a href="' . $url . '"' . $target . $titleAttr . '>' . $name . '</a></li>';
        }
        
        return $html;
    }
}

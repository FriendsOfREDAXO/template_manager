<?php

namespace FriendsOfRedaxo\TemplateManager\FieldRenderer;

use FriendsOfRedaxo\TemplateManager\AbstractFieldRenderer;

/**
 * Renderer für externe Link-Listen mit Live-Vorschau
 */
class ExternalLinklistFieldRenderer extends AbstractFieldRenderer
{
    public function supports(string $type): bool
    {
        return $type === 'external_linklist';
    }
    
    public function render(array $setting, string $value, string $name, int $clangId): string
    {
        $html = '<div class="form-group">';
        $html .= '<label class="control-label">' . \rex_escape($setting['label']) . '</label>';
        
        if (!empty($setting['description'])) {
            $html .= '<p class="help-block">' . nl2br(\rex_escape($setting['description'])) . '</p>';
        }
        
        // Hinweistext zum Format (collapsible)
        $collapseId = 'external-linklist-help-' . md5($name);
        $html .= '<p style="margin-bottom: 10px;">';
        $html .= '<a class="btn btn-xs btn-info" data-toggle="collapse" href="#' . $collapseId . '" role="button" aria-expanded="false">';
        $html .= '<i class="rex-icon fa-question-circle"></i> Format-Hilfe anzeigen';
        $html .= '</a>';
        $html .= '</p>';
        
        $html .= '<div class="collapse" id="' . $collapseId . '" style="margin-bottom: 10px;">';
        $html .= '<div class="alert alert-info">';
        $html .= '<strong><i class="rex-icon fa-info-circle"></i> Format:</strong> Ein Link pro Zeile<br>';
        $html .= '<code>Name|URL|Beschreibung (optional)</code><br><br>';
        $html .= '<strong>Beispiel:</strong><br>';
        $html .= '<code>Partner A|https://www.example.com|Beschreibung für Partner A<br>';
        $html .= 'Partner B|https://www.example.org|Beschreibung für Partner B</code>';
        $html .= '</div>';
        $html .= '</div>';
        
        $html .= '<textarea 
            class="form-control external-linklist-input" 
            id="' . \rex_escape($name) . '" 
            name="' . $name . '" 
            rows="8"
            placeholder="Name|URL|Beschreibung&#10;Name|URL|Beschreibung&#10;..."
            style="font-family: Monaco, Menlo, Consolas, monospace; font-size: 13px;">' . \rex_escape($value) . '</textarea>';
        
        // Live-Vorschau Container
        $html .= '<div class="external-linklist-preview" style="margin-top: 15px; padding: 15px; background: #f8f9fa; border: 1px solid #dee2e6; border-radius: 4px; display: none;">';
        $html .= '<strong><i class="rex-icon fa-eye"></i> Live-Vorschau:</strong>';
        $html .= '<ul class="preview-list" style="margin-top: 10px; margin-bottom: 0; list-style: none; padding-left: 0;"></ul>';
        $html .= '</div>';
        
        // JavaScript für Live-Vorschau (einmalig pro Seite)
        $fieldId = 'external_linklist_' . md5($name);
        $html .= '<script nonce="' . \rex_response::getNonce() . '">
        (function() {
            const textarea = document.getElementById("' . \rex_escape($name) . '");
            if (!textarea) return;
            
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
                    li.style.marginBottom = "8px";
                    li.style.paddingLeft = "5px";
                    
                    if (parts.length < 2) {
                        li.innerHTML = "<span style=\'color: #dc3545;\'><i class=\'rex-icon fa-times-circle\'></i> Zeile " + (index + 1) + ": Ungültiges Format (mindestens Name|URL erforderlich)</span>";
                    } else {
                        const name = parts[0];
                        const url = parts[1];
                        const desc = parts[2] || "";
                        
                        // URL-Validierung
                        const urlPattern = /^https?:\\/\\/.+/i;
                        const isValidUrl = urlPattern.test(url);
                        
                        if (!isValidUrl) {
                            li.innerHTML = "<span style=\'color: #dc3545;\'><i class=\'rex-icon fa-times-circle\'></i> <strong>" + name + ":</strong> Ungültige URL (muss mit http:// oder https:// beginnen)</span>";
                        } else {
                            const descText = desc ? " <small style=\'color: #6c757d;\'>— " + desc + "</small>" : "";
                            li.innerHTML = "<span style=\'color: #28a745;\'><i class=\'rex-icon fa-check-circle\'></i></span> <a href=\"" + url + "\" target=\"_blank\" rel=\"noopener noreferrer\" style=\'font-weight: 500;\'>" + name + "</a>" + descText;
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
}

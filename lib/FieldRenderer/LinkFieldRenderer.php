<?php

namespace FriendsOfRedaxo\TemplateManager\FieldRenderer;

use FriendsOfRedaxo\TemplateManager\AbstractFieldRenderer;

/**
 * Renderer für REDAXO Link Widgets
 * 
 * Unterstützt: link, linklist, article (Alias für link)
 */
class LinkFieldRenderer extends AbstractFieldRenderer
{
    private static int $linkCounter = 0;
    private static int $linklistCounter = 0;
    private static bool $jsIncluded = false;
    
    public function supports(string $type): bool
    {
        return in_array($type, ['link', 'linklist', 'article'], true);
    }
    
    public function render(array $setting, string $value, string $name, int $clangId): string
    {
        $html = $this->renderFormGroupStart($setting);
        
        switch ($setting['type']) {
            case 'link':
            case 'article':
                self::$linkCounter++;
                $widgetId = self::$linkCounter;
                
                // Widget mit Wrapper für JS-Erkennung
                $widget = \rex_var_link::getWidget($widgetId, $name, $value, []);
                $html .= '<div class="tm-link-widget-wrapper" data-widget-id="' . $widgetId . '" data-clang="' . $clangId . '">' . $widget . '</div>';
                break;
                
            case 'linklist':
                self::$linklistCounter++;
                $html .= \rex_var_linklist::getWidget(self::$linklistCounter, $name, $value, []);
                break;
        }
        
        $html .= $this->renderFormGroupEnd($setting);
        
        // JavaScript nur einmal einfügen
        if (!self::$jsIncluded) {
            self::$jsIncluded = true;
            $html .= $this->getEditScript();
        }
        
        return $html;
    }
    
    /**
     * JavaScript für Edit-Button - fügt Button per JS hinzu
     */
    private function getEditScript(): string
    {
        $editTitle = \rex_i18n::msg('content_editarticle');
        
        return <<<JS
<script>
(function() {
    // Edit-Buttons zu allen Link-Widgets hinzufügen
    document.querySelectorAll('.tm-link-widget-wrapper').forEach(function(wrapper) {
        const widgetId = wrapper.dataset.widgetId;
        const clang = wrapper.dataset.clang || 1;
        const btnGroup = wrapper.querySelector('.input-group-btn');
        
        if (btnGroup && !btnGroup.querySelector('.tm-link-edit')) {
            const editBtn = document.createElement('a');
            editBtn.href = '#';
            editBtn.className = 'btn btn-popup tm-link-edit';
            editBtn.title = '{$editTitle}';
            editBtn.dataset.widgetId = widgetId;
            editBtn.dataset.clang = clang;
            editBtn.innerHTML = '<i class="rex-icon fa-pencil"></i>';
            btnGroup.appendChild(editBtn);
        }
    });
    
    // Click-Handler für Edit-Buttons
    document.addEventListener('click', function(e) {
        const btn = e.target.closest('.tm-link-edit');
        if (!btn) return;
        
        e.preventDefault();
        
        const widgetId = btn.dataset.widgetId;
        const clang = btn.dataset.clang || 1;
        const input = document.getElementById('REX_LINK_' + widgetId);
        
        if (!input || !input.value || input.value === '') {
            alert('Kein Artikel ausgewählt');
            return;
        }
        
        const articleId = parseInt(input.value, 10);
        if (isNaN(articleId) || articleId < 1) {
            alert('Kein gültiger Artikel ausgewählt');
            return;
        }
        
        // Artikel im Backend öffnen
        const url = 'index.php?page=content/edit&article_id=' + articleId + '&clang=' + clang + '&mode=edit';
        window.open(url, '_blank');
    });
})();
</script>
JS;
    }
}

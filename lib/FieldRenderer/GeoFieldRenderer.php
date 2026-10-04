<?php

namespace FriendsOfRedaxo\TemplateManager\FieldRenderer;

use FriendsOfRedaxo\TemplateManager\AbstractFieldRenderer;

/**
 * Renderer für Koordinaten (Feldtyp „geo“): gespeichert als „Breite,Länge“.
 *
 * Mit dem AddOn vector_maps erhält das Feld den Kartenpicker (Karte, Adresssuche, Übernahme per Klick);
 * ohne vector_maps bleibt es ein Textfeld mit Formatprüfung.
 *
 * Syntax: tm_geo: geo|Koordinaten||Hinweis
 */
class GeoFieldRenderer extends AbstractFieldRenderer
{
    public function supports(string $type): bool
    {
        return in_array($type, ['geo', 'coordinates'], true);
    }

    public function render(array $setting, string $value, string $name, int $clangId): string
    {
        $placeholder = 'z. B. 52.520008,13.404954';
        $html = $this->renderFormGroupStart($setting);

        if (\rex_addon::get('vector_maps')->isAvailable() && class_exists(\KLXM\VectorMaps\Picker\PickerWidget::class)) {
            $id = 'tm-geo-' . preg_replace('/[^\w-]/', '', (string) $setting['key']) . '-' . $clangId;
            $html .= \KLXM\VectorMaps\Picker\PickerWidget::factory($name, $id)->setValue($value)->setPlaceholder($placeholder)->parse();
        } else {
            $html .= '<input type="text" class="form-control" name="' . $name . '" value="' . \rex_escape($value) . '" placeholder="' . \rex_escape($placeholder) . '"'
                . ' pattern="^\s*-?\d{1,2}(\.\d+)?\s*[,;]\s*-?\d{1,3}(\.\d+)?\s*$" inputmode="decimal" autocomplete="off">';
        }

        $html .= $this->renderFormGroupEnd($setting);

        return $html;
    }
}

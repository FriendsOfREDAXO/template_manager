<?php

namespace FriendsOfRedaxo\TemplateManager\FieldRenderer;

use FriendsOfRedaxo\TemplateManager\AbstractFieldRenderer;

/**
 * Renderer für strukturierte Öffnungszeiten
 * 
 * Features:
 * - Reguläre Öffnungszeiten pro Wochentag
 * - Mehrere Zeitfenster pro Tag (z.B. für Mittagspausen)
 * - 24h geöffnet / Geschlossen Optionen
 * - Sonderöffnungszeiten für Feiertage
 * - Zeiten kopieren (auf andere Tage übertragen)
 * - Live-Vorschau
 * 
 * Format: JSON mit regular (Wochentage) und special (Sonderzeiten)
 * 
 * Beispiel: tm_opening_hours: opening_hours|Öffnungszeiten||Geschäftszeiten inkl. Feiertage
 */
class OpeningHoursFieldRenderer extends AbstractFieldRenderer
{
    private static int $instanceCounter = 0;
    
    /**
     * Wochentage mit deutschen Labels
     */
    private const WEEKDAYS = [
        'monday' => 'Montag',
        'tuesday' => 'Dienstag',
        'wednesday' => 'Mittwoch',
        'thursday' => 'Donnerstag',
        'friday' => 'Freitag',
        'saturday' => 'Samstag',
        'sunday' => 'Sonntag',
    ];
    
    /**
     * Vordefinierte Feiertage (Deutschland)
     */
    private const HOLIDAYS = [
        'neujahr' => ['name' => 'Neujahr', 'date' => '01-01'],
        'karfreitag' => ['name' => 'Karfreitag', 'date' => 'easter-2'],
        'ostersonntag' => ['name' => 'Ostersonntag', 'date' => 'easter'],
        'ostermontag' => ['name' => 'Ostermontag', 'date' => 'easter+1'],
        'tag_der_arbeit' => ['name' => 'Tag der Arbeit', 'date' => '05-01'],
        'christi_himmelfahrt' => ['name' => 'Christi Himmelfahrt', 'date' => 'easter+39'],
        'pfingstsonntag' => ['name' => 'Pfingstsonntag', 'date' => 'easter+49'],
        'pfingstmontag' => ['name' => 'Pfingstmontag', 'date' => 'easter+50'],
        'fronleichnam' => ['name' => 'Fronleichnam', 'date' => 'easter+60'],
        'tag_der_einheit' => ['name' => 'Tag der Deutschen Einheit', 'date' => '10-03'],
        'allerheiligen' => ['name' => 'Allerheiligen', 'date' => '11-01'],
        'heiligabend' => ['name' => 'Heiligabend', 'date' => '12-24'],
        'weihnachten_1' => ['name' => '1. Weihnachtstag', 'date' => '12-25'],
        'weihnachten_2' => ['name' => '2. Weihnachtstag', 'date' => '12-26'],
        'silvester' => ['name' => 'Silvester', 'date' => '12-31'],
    ];
    
    public function supports(string $type): bool
    {
        return $type === 'opening_hours';
    }
    
    public function render(array $setting, string $value, string $name, int $clangId): string
    {
        self::$instanceCounter++;
        $instanceId = 'opening-hours-' . self::$instanceCounter;
        
        // JSON dekodieren oder Standardstruktur
        $data = $this->parseValue($value);
        
        $html = '<div class="form-group">';
        $html .= '<label class="control-label">' . \rex_escape($setting['label']) . '</label>';
        
        if (!empty($setting['description'])) {
            $html .= '<p class="help-block">' . nl2br(\rex_escape($setting['description'])) . '</p>';
        }
        
        // Container
        $html .= '<div class="opening-hours-container" id="' . $instanceId . '" data-name="' . \rex_escape($name) . '">';
        
        // Hidden Input für JSON-Wert
        $html .= '<input type="hidden" name="' . $name . '" class="opening-hours-value" value="' . \rex_escape($value) . '">';
        
        // Tabs für Regular / Special
        $html .= '<ul class="nav nav-tabs" role="tablist">';
        $html .= '<li role="presentation" class="active"><a href="#' . $instanceId . '-regular" aria-controls="regular" role="tab" data-toggle="tab"><i class="rex-icon fa-clock-o"></i> Reguläre Zeiten</a></li>';
        $html .= '<li role="presentation"><a href="#' . $instanceId . '-special" aria-controls="special" role="tab" data-toggle="tab"><i class="rex-icon fa-calendar"></i> Sonderzeiten / Feiertage</a></li>';
        $html .= '<li role="presentation"><a href="#' . $instanceId . '-preview" aria-controls="preview" role="tab" data-toggle="tab"><i class="rex-icon fa-eye"></i> Vorschau</a></li>';
        $html .= '</ul>';
        
        $html .= '<div class="tab-content" style="padding: 15px; border: 1px solid #ddd; border-top: none; border-radius: 0 0 4px 4px;">';
        
        // Tab: Reguläre Zeiten
        $html .= '<div role="tabpanel" class="tab-pane active" id="' . $instanceId . '-regular">';
        $html .= $this->renderRegularHours($data['regular'] ?? []);
        
        // Hinweis-Textfeld
        $html .= '<div class="opening-hours-note" style="margin-top: 20px; padding-top: 15px; border-top: 1px solid #eee;">';
        $html .= '<label style="font-weight: 600; margin-bottom: 5px; display: block;"><i class="rex-icon fa-info-circle"></i> Zusätzlicher Hinweis</label>';
        $html .= '<input type="text" class="form-control opening-hours-note-input" placeholder="z.B. Weitere Termine nach Absprache, Mittagspause von 12-13 Uhr..." value="' . \rex_escape($data['note'] ?? '') . '" style="max-width: 500px;">';
        $html .= '<p class="help-block" style="margin-top: 5px; font-size: 12px;">Optionaler Freitext, der unter den Öffnungszeiten angezeigt wird.</p>';
        $html .= '</div>';
        
        $html .= '</div>';
        
        // Tab: Sonderzeiten
        $html .= '<div role="tabpanel" class="tab-pane" id="' . $instanceId . '-special">';
        $html .= $this->renderSpecialHours($data['special'] ?? []);
        $html .= '</div>';
        
        // Tab: Vorschau
        $html .= '<div role="tabpanel" class="tab-pane" id="' . $instanceId . '-preview">';
        $html .= '<div class="opening-hours-preview"></div>';
        $html .= '</div>';
        
        $html .= '</div>'; // .tab-content
        $html .= '</div>'; // .opening-hours-container
        $html .= '</div>'; // .form-group
        
        // CSS und JavaScript nur einmal einbinden
        if (self::$instanceCounter === 1) {
            $html .= $this->getStyles();
            $html .= $this->getScript();
        }
        
        return $html;
    }
    
    /**
     * Wert parsen oder Standardstruktur zurückgeben
     */
    private function parseValue(string $value): array
    {
        if (!empty($value)) {
            $decoded = json_decode($value, true);
            if (is_array($decoded)) {
                // Auto-Repair: Feiertage reparieren, die fälschlicherweise holiday: false haben
                $decoded = $this->repairHolidayFlags($decoded);
                return $decoded;
            }
        }
        
        // Standardstruktur
        return [
            'regular' => [
                'monday' => ['status' => 'open', 'times' => [['open' => '09:00', 'close' => '17:00']]],
                'tuesday' => ['status' => 'open', 'times' => [['open' => '09:00', 'close' => '17:00']]],
                'wednesday' => ['status' => 'open', 'times' => [['open' => '09:00', 'close' => '17:00']]],
                'thursday' => ['status' => 'open', 'times' => [['open' => '09:00', 'close' => '17:00']]],
                'friday' => ['status' => 'open', 'times' => [['open' => '09:00', 'close' => '17:00']]],
                'saturday' => ['status' => 'closed', 'times' => []],
                'sunday' => ['status' => 'closed', 'times' => []],
            ],
            'special' => [],
        ];
    }
    
    /**
     * Repariert Feiertage, die fälschlicherweise holiday: false haben
     * Erkennt Easter-Format (easter+X, easter-X), MM-DD Format und Namen bekannter Feiertage
     */
    private function repairHolidayFlags(array $data): array
    {
        if (!isset($data['special']) || !is_array($data['special'])) {
            return $data;
        }
        
        // Index: Name => korrektes Datum
        $holidayNameToDate = [];
        $knownHolidayDates = [];
        foreach (self::HOLIDAYS as $holiday) {
            $holidayNameToDate[$holiday['name']] = $holiday['date'];
            $knownHolidayDates[$holiday['date']] = true;
        }
        
        foreach ($data['special'] as $index => $entry) {
            $date = $entry['date'] ?? '';
            $name = $entry['name'] ?? '';
            
            // Prüfe anhand des Namens ob es ein bekannter Feiertag ist
            if (isset($holidayNameToDate[$name])) {
                // Korrektes Datum wiederherstellen falls es ein konkretes Datum ist (YYYY-MM-DD)
                if (preg_match('/^\d{4}-\d{2}-\d{2}$/', $date)) {
                    $data['special'][$index]['date'] = $holidayNameToDate[$name];
                }
                $data['special'][$index]['holiday'] = true;
                continue;
            }
            
            // Überspringe wenn bereits als Feiertag markiert
            if (!empty($entry['holiday'])) {
                continue;
            }
            
            // Prüfe auf Easter-Format (easter+X oder easter-X)
            if (preg_match('/^easter[+-]\d+$/', $date)) {
                $data['special'][$index]['holiday'] = true;
                continue;
            }
            
            // Prüfe auf MM-DD Format (z.B. 12-25, 10-03)
            if (preg_match('/^\d{2}-\d{2}$/', $date) && isset($knownHolidayDates[$date])) {
                $data['special'][$index]['holiday'] = true;
                continue;
            }
        }
        
        return $data;
    }
    
    /**
     * Reguläre Öffnungszeiten rendern
     */
    private function renderRegularHours(array $regular): string
    {
        $html = '<div class="opening-hours-regular">';
        
        // Schnellaktionen
        $html .= '<div class="oh-quick-actions" style="margin-bottom: 15px; padding: 10px; background: #f5f5f5; border-radius: 4px;">';
        $html .= '<span style="margin-right: 10px;"><strong>Schnellaktionen:</strong></span>';
        $html .= '<button type="button" class="btn btn-xs btn-default oh-copy-to-all" title="Montag auf alle Werktage kopieren"><i class="rex-icon fa-copy"></i> Mo → Werktage</button> ';
        $html .= '<button type="button" class="btn btn-xs btn-default oh-set-all-closed" title="Alle Tage als geschlossen markieren"><i class="rex-icon fa-ban"></i> Alle geschlossen</button> ';
        $html .= '<button type="button" class="btn btn-xs btn-default oh-reset-default" title="Auf Standardzeiten zurücksetzen"><i class="rex-icon fa-refresh"></i> Zurücksetzen</button>';
        $html .= '</div>';
        
        // Wochentage
        foreach (self::WEEKDAYS as $day => $label) {
            $dayData = $regular[$day] ?? ['status' => 'closed', 'times' => []];
            $html .= $this->renderDayRow($day, $label, $dayData);
        }
        
        $html .= '</div>';
        
        return $html;
    }
    
    /**
     * Einzelnen Wochentag rendern
     */
    private function renderDayRow(string $day, string $label, array $dayData): string
    {
        $status = $dayData['status'] ?? 'closed';
        $times = $dayData['times'] ?? [];
        
        $html = '<div class="oh-day-row" data-day="' . $day . '" style="display: flex; align-items: flex-start; padding: 10px; margin-bottom: 5px; background: #fafafa; border-radius: 4px; border-left: 3px solid ' . ($status === 'closed' ? '#d9534f' : ($status === '24h' ? '#5cb85c' : '#5bc0de')) . ';">';
        
        // Tag-Label
        $html .= '<div class="oh-day-label" style="width: 100px; font-weight: 600; padding-top: 5px;">' . $label . '</div>';
        
        // Status-Auswahl
        $html .= '<div class="oh-day-status" style="width: 150px;">';
        $html .= '<select class="form-control input-sm oh-status-select">';
        $html .= '<option value="open"' . ($status === 'open' ? ' selected' : '') . '>Geöffnet</option>';
        $html .= '<option value="closed"' . ($status === 'closed' ? ' selected' : '') . '>Geschlossen</option>';
        $html .= '<option value="24h"' . ($status === '24h' ? ' selected' : '') . '>24h geöffnet</option>';
        $html .= '</select>';
        $html .= '</div>';
        
        // Zeitfenster
        $html .= '<div class="oh-day-times" style="flex: 1; margin-left: 15px;' . ($status !== 'open' ? ' display: none;' : '') . '">';
        
        if (empty($times)) {
            $times = [['open' => '09:00', 'close' => '17:00']];
        }
        
        foreach ($times as $index => $time) {
            $html .= $this->renderTimeSlot($index, $time, count($times) > 1);
        }
        
        // Button zum Hinzufügen weiterer Zeitfenster
        $html .= '<button type="button" class="btn btn-xs btn-success oh-add-time" style="margin-top: 5px;" title="Weiteres Zeitfenster (z.B. nach Mittagspause)">';
        $html .= '<i class="rex-icon fa-plus"></i> Zeitfenster';
        $html .= '</button>';
        
        $html .= '</div>';
        
        // Kopier-Button
        $html .= '<div class="oh-day-actions" style="margin-left: 10px;">';
        $html .= '<div class="btn-group">';
        $html .= '<button type="button" class="btn btn-xs btn-default dropdown-toggle" data-toggle="dropdown" title="Kopieren auf...">';
        $html .= '<i class="rex-icon fa-copy"></i> <span class="caret"></span>';
        $html .= '</button>';
        $html .= '<ul class="dropdown-menu dropdown-menu-right oh-copy-menu">';
        foreach (self::WEEKDAYS as $targetDay => $targetLabel) {
            if ($targetDay !== $day) {
                $html .= '<li><a href="#" data-target="' . $targetDay . '">' . $targetLabel . '</a></li>';
            }
        }
        $html .= '<li class="divider"></li>';
        $html .= '<li><a href="#" data-target="weekdays">Alle Werktage (Mo-Fr)</a></li>';
        $html .= '<li><a href="#" data-target="weekend">Wochenende (Sa-So)</a></li>';
        $html .= '<li><a href="#" data-target="all">Alle Tage</a></li>';
        $html .= '</ul>';
        $html .= '</div>';
        $html .= '</div>';
        
        $html .= '</div>';
        
        return $html;
    }
    
    /**
     * Einzelnes Zeitfenster rendern
     */
    private function renderTimeSlot(int $index, array $time, bool $showRemove = false): string
    {
        $open = $time['open'] ?? '09:00';
        $close = $time['close'] ?? '17:00';
        
        $html = '<div class="oh-time-slot" style="display: flex; align-items: center; gap: 8px; margin-bottom: 5px;">';
        $html .= '<input type="time" class="form-control input-sm oh-time-open" value="' . \rex_escape($open) . '" style="width: 110px;">';
        $html .= '<span style="color: #666;">–</span>';
        $html .= '<input type="time" class="form-control input-sm oh-time-close" value="' . \rex_escape($close) . '" style="width: 110px;">';
        
        if ($showRemove || $index > 0) {
            $html .= '<button type="button" class="btn btn-xs btn-danger oh-remove-time" title="Zeitfenster entfernen">';
            $html .= '<i class="rex-icon fa-trash"></i>';
            $html .= '</button>';
        }
        
        $html .= '</div>';
        
        return $html;
    }
    
    /**
     * Sonderöffnungszeiten rendern
     */
    private function renderSpecialHours(array $special): string
    {
        $html = '<div class="opening-hours-special">';
        
        // Info
        $html .= '<div class="alert alert-info" style="margin-bottom: 15px;">';
        $html .= '<i class="rex-icon fa-info-circle"></i> ';
        $html .= 'Hier können Sie abweichende Öffnungszeiten für Feiertage oder besondere Tage festlegen. Diese überschreiben die regulären Zeiten.';
        $html .= '</div>';
        
        // Schnell-Hinzufügen für Feiertage
        $html .= '<div class="oh-holiday-presets" style="margin-bottom: 15px; padding: 10px; background: #f5f5f5; border-radius: 4px;">';
        $html .= '<label style="display: block; margin-bottom: 8px;"><strong>Feiertag hinzufügen:</strong></label>';
        $html .= '<div style="display: flex; gap: 10px; flex-wrap: wrap;">';
        $html .= '<select class="form-control input-sm oh-holiday-select" style="width: 200px;">';
        $html .= '<option value="">-- Feiertag wählen --</option>';
        foreach (self::HOLIDAYS as $key => $holiday) {
            $html .= '<option value="' . $key . '">' . \rex_escape($holiday['name']) . '</option>';
        }
        $html .= '</select>';
        $html .= '<select class="form-control input-sm oh-holiday-status" style="width: 150px;">';
        $html .= '<option value="closed">Geschlossen</option>';
        $html .= '<option value="open">Geöffnet</option>';
        $html .= '</select>';
        $html .= '<button type="button" class="btn btn-sm btn-primary oh-add-holiday"><i class="rex-icon fa-plus"></i> Hinzufügen</button>';
        $html .= '</div>';
        $html .= '</div>';
        
        // Manuell hinzufügen
        $html .= '<div style="margin-bottom: 15px;">';
        $html .= '<button type="button" class="btn btn-sm btn-success oh-add-special"><i class="rex-icon fa-plus"></i> Individuelles Datum hinzufügen</button>';
        $html .= '</div>';
        
        // Liste der Sonderzeiten
        $html .= '<div class="oh-special-list">';
        
        if (!empty($special)) {
            foreach ($special as $index => $entry) {
                $html .= $this->renderSpecialEntry($index, $entry);
            }
        }
        
        $html .= '</div>';
        
        $html .= '</div>';
        
        return $html;
    }
    
    /**
     * Einzelnen Sonderzeiten-Eintrag rendern
     */
    private function renderSpecialEntry(int $index, array $entry): string
    {
        $date = $entry['date'] ?? '';
        $name = $entry['name'] ?? '';
        $status = $entry['status'] ?? 'closed';
        $times = $entry['times'] ?? [];
        $isHoliday = $entry['holiday'] ?? false;
        
        $html = '<div class="oh-special-entry panel panel-default" data-index="' . $index . '"' . ($isHoliday ? ' data-holiday="true"' : '') . '>';
        $html .= '<div class="panel-body" style="display: flex; align-items: flex-start; gap: 10px; flex-wrap: wrap;">';
        
        // Datum
        $html .= '<div style="flex: 0 0 140px;">';
        if ($isHoliday) {
            $html .= '<input type="text" class="form-control input-sm oh-special-date" value="' . \rex_escape($date) . '" readonly style="background: #f5f5f5;">';
            $html .= '<small class="text-muted">Beweglich</small>';
        } else {
            $html .= '<input type="date" class="form-control input-sm oh-special-date" value="' . \rex_escape($date) . '">';
        }
        $html .= '</div>';
        
        // Name/Bezeichnung
        $html .= '<div style="flex: 0 0 180px;">';
        $html .= '<input type="text" class="form-control input-sm oh-special-name" value="' . \rex_escape($name) . '" placeholder="Bezeichnung">';
        $html .= '</div>';
        
        // Status
        $html .= '<div style="flex: 0 0 130px;">';
        $html .= '<select class="form-control input-sm oh-special-status">';
        $html .= '<option value="open"' . ($status === 'open' ? ' selected' : '') . '>Geöffnet</option>';
        $html .= '<option value="closed"' . ($status === 'closed' ? ' selected' : '') . '>Geschlossen</option>';
        $html .= '</select>';
        $html .= '</div>';
        
        // Zeiten (nur wenn geöffnet)
        $html .= '<div class="oh-special-times" style="flex: 1;' . ($status !== 'open' ? ' display: none;' : '') . '">';
        
        if (empty($times)) {
            $times = [['open' => '09:00', 'close' => '14:00']];
        }
        
        foreach ($times as $timeIndex => $time) {
            $html .= $this->renderTimeSlot($timeIndex, $time, count($times) > 1);
        }
        
        $html .= '<button type="button" class="btn btn-xs btn-success oh-add-special-time" style="margin-top: 3px;">';
        $html .= '<i class="rex-icon fa-plus"></i>';
        $html .= '</button>';
        
        $html .= '</div>';
        
        // Löschen
        $html .= '<div>';
        $html .= '<button type="button" class="btn btn-xs btn-danger oh-remove-special" title="Entfernen">';
        $html .= '<i class="rex-icon fa-trash"></i>';
        $html .= '</button>';
        $html .= '</div>';
        
        $html .= '</div>';
        $html .= '</div>';
        
        return $html;
    }
    
    /**
     * CSS-Styles
     */
    private function getStyles(): string
    {
        return '
<style>
.opening-hours-container {
    max-width: 100%;
}

.opening-hours-container .nav-tabs {
    margin-bottom: 0;
}

.oh-day-row {
    transition: border-color 0.3s ease, background 0.3s ease;
}

.oh-day-row:hover {
    background: #f0f0f0 !important;
}

.oh-time-slot input[type="time"] {
    font-family: monospace;
}

.oh-special-entry {
    margin-bottom: 10px;
}

.oh-special-entry:last-child {
    margin-bottom: 0;
}

/* Vorschau-Styles */
.oh-preview-table {
    width: 100%;
    border-collapse: collapse;
}

.oh-preview-table th,
.oh-preview-table td {
    padding: 8px 12px;
    text-align: left;
    border-bottom: 1px solid #eee;
}

.oh-preview-table tr:last-child td {
    border-bottom: none;
}

.oh-preview-table .oh-closed {
    color: #d9534f;
}

.oh-preview-table .oh-24h {
    color: #5cb85c;
    font-weight: 600;
}

.oh-preview-table .oh-today {
    background: #f0f7ff;
    font-weight: 600;
}

.oh-preview-special {
    margin-top: 20px;
    padding-top: 15px;
    border-top: 2px solid #eee;
}

@media (max-width: 768px) {
    .oh-day-row {
        flex-wrap: wrap;
    }
    
    .oh-day-label {
        width: 100% !important;
        margin-bottom: 8px;
    }
    
    .oh-day-status {
        width: 100% !important;
        margin-bottom: 8px;
    }
    
    .oh-day-times {
        width: 100%;
        margin-left: 0 !important;
    }
}
</style>';
    }
    
    /**
     * JavaScript
     */
    private function getScript(): string
    {
        $holidaysJson = json_encode(self::HOLIDAYS);
        $weekdaysJson = json_encode(self::WEEKDAYS);
        $nonce = \rex_response::getNonce();
        
        return '<script nonce="' . $nonce . '">
(function() {
    const HOLIDAYS = ' . $holidaysJson . ';
    const WEEKDAYS = ' . $weekdaysJson . ';
    const WEEKDAY_KEYS = Object.keys(WEEKDAYS);
    
    // Container initialisieren
    document.querySelectorAll(".opening-hours-container").forEach(initContainer);
    
    function initContainer(container) {
        const valueInput = container.querySelector(".opening-hours-value");
        
        // Event-Delegation für alle Aktionen
        container.addEventListener("change", handleChange);
        container.addEventListener("click", handleClick);
        container.addEventListener("input", debounce(updateValue, 300));
        
        function handleChange(e) {
            const target = e.target;
            
            // Status-Änderung bei Wochentag
            if (target.classList.contains("oh-status-select")) {
                const row = target.closest(".oh-day-row");
                const timesDiv = row.querySelector(".oh-day-times");
                const status = target.value;
                
                // Zeiten ein-/ausblenden
                timesDiv.style.display = (status === "open") ? "" : "none";
                
                // Rahmenfarbe aktualisieren
                const colors = { closed: "#d9534f", "24h": "#5cb85c", open: "#5bc0de" };
                row.style.borderLeftColor = colors[status] || "#5bc0de";
                
                updateValue();
            }
            
            // Status-Änderung bei Sonderzeit
            if (target.classList.contains("oh-special-status")) {
                const entry = target.closest(".oh-special-entry");
                const timesDiv = entry.querySelector(".oh-special-times");
                timesDiv.style.display = (target.value === "open") ? "" : "none";
                updateValue();
            }
        }
        
        function handleClick(e) {
            const target = e.target.closest("button, a");
            if (!target) return;
            
            // Zeitfenster hinzufügen
            if (target.classList.contains("oh-add-time")) {
                e.preventDefault();
                const timesDiv = target.closest(".oh-day-times");
                const slot = createTimeSlot("09:00", "17:00", true);
                timesDiv.insertBefore(slot, target);
                updateValue();
            }
            
            // Zeitfenster entfernen
            if (target.classList.contains("oh-remove-time")) {
                e.preventDefault();
                const slot = target.closest(".oh-time-slot");
                slot.remove();
                updateValue();
            }
            
            // Kopieren-Menü
            if (target.closest(".oh-copy-menu")) {
                e.preventDefault();
                const link = target.closest("a");
                if (!link) return;
                
                const sourceRow = target.closest(".oh-day-row");
                const targetDays = link.dataset.target;
                copyDayTo(sourceRow, targetDays);
            }
            
            // Schnellaktion: Mo → Werktage
            if (target.classList.contains("oh-copy-to-all")) {
                e.preventDefault();
                const mondayRow = container.querySelector(".oh-day-row[data-day=\"monday\"]");
                copyDayTo(mondayRow, "weekdays");
            }
            
            // Schnellaktion: Alle geschlossen
            if (target.classList.contains("oh-set-all-closed")) {
                e.preventDefault();
                container.querySelectorAll(".oh-day-row").forEach(row => {
                    row.querySelector(".oh-status-select").value = "closed";
                    row.querySelector(".oh-day-times").style.display = "none";
                    row.style.borderLeftColor = "#d9534f";
                });
                updateValue();
            }
            
            // Schnellaktion: Zurücksetzen
            if (target.classList.contains("oh-reset-default")) {
                e.preventDefault();
                if (!confirm("Alle Zeiten auf Standard zurücksetzen?")) return;
                
                container.querySelectorAll(".oh-day-row").forEach(row => {
                    const day = row.dataset.day;
                    const isWeekend = (day === "saturday" || day === "sunday");
                    const status = isWeekend ? "closed" : "open";
                    
                    row.querySelector(".oh-status-select").value = status;
                    const timesDiv = row.querySelector(".oh-day-times");
                    timesDiv.style.display = isWeekend ? "none" : "";
                    row.style.borderLeftColor = isWeekend ? "#d9534f" : "#5bc0de";
                    
                    // Zeiten zurücksetzen
                    timesDiv.querySelectorAll(".oh-time-slot").forEach((slot, i) => {
                        if (i === 0) {
                            slot.querySelector(".oh-time-open").value = "09:00";
                            slot.querySelector(".oh-time-close").value = "17:00";
                        } else {
                            slot.remove();
                        }
                    });
                });
                updateValue();
            }
            
            // Feiertag hinzufügen
            if (target.classList.contains("oh-add-holiday")) {
                e.preventDefault();
                const select = container.querySelector(".oh-holiday-select");
                const statusSelect = container.querySelector(".oh-holiday-status");
                const holidayKey = select.value;
                
                if (!holidayKey) {
                    alert("Bitte einen Feiertag auswählen.");
                    return;
                }
                
                const holiday = HOLIDAYS[holidayKey];
                const list = container.querySelector(".oh-special-list");
                const index = list.children.length;
                
                const entry = createSpecialEntry(index, {
                    date: holiday.date,
                    name: holiday.name,
                    status: statusSelect.value,
                    times: [{ open: "09:00", close: "14:00" }],
                    holiday: true
                });
                
                list.appendChild(entry);
                select.value = "";
                updateValue();
            }
            
            // Individuelles Datum hinzufügen
            if (target.classList.contains("oh-add-special")) {
                e.preventDefault();
                const list = container.querySelector(".oh-special-list");
                const index = list.children.length;
                const today = new Date().toISOString().split("T")[0];
                
                const entry = createSpecialEntry(index, {
                    date: today,
                    name: "",
                    status: "closed",
                    times: [{ open: "09:00", close: "14:00" }],
                    holiday: false
                });
                
                list.appendChild(entry);
                updateValue();
            }
            
            // Sonderzeit entfernen
            if (target.classList.contains("oh-remove-special")) {
                e.preventDefault();
                target.closest(".oh-special-entry").remove();
                updateValue();
            }
            
            // Zeitfenster bei Sonderzeit hinzufügen
            if (target.classList.contains("oh-add-special-time")) {
                e.preventDefault();
                const timesDiv = target.closest(".oh-special-times");
                const slot = createTimeSlot("09:00", "17:00", true);
                timesDiv.insertBefore(slot, target);
                updateValue();
            }
        }
        
        function copyDayTo(sourceRow, targetDays) {
            const sourceStatus = sourceRow.querySelector(".oh-status-select").value;
            const sourceTimes = [];
            sourceRow.querySelectorAll(".oh-time-slot").forEach(slot => {
                sourceTimes.push({
                    open: slot.querySelector(".oh-time-open").value,
                    close: slot.querySelector(".oh-time-close").value
                });
            });
            
            let targets = [];
            if (targetDays === "weekdays") {
                targets = ["monday", "tuesday", "wednesday", "thursday", "friday"];
            } else if (targetDays === "weekend") {
                targets = ["saturday", "sunday"];
            } else if (targetDays === "all") {
                targets = WEEKDAY_KEYS;
            } else {
                targets = [targetDays];
            }
            
            targets.forEach(day => {
                if (day === sourceRow.dataset.day) return;
                
                const row = container.querySelector(".oh-day-row[data-day=\"" + day + "\"]");
                if (!row) return;
                
                // Status setzen
                row.querySelector(".oh-status-select").value = sourceStatus;
                const timesDiv = row.querySelector(".oh-day-times");
                timesDiv.style.display = (sourceStatus === "open") ? "" : "none";
                
                const colors = { closed: "#d9534f", "24h": "#5cb85c", open: "#5bc0de" };
                row.style.borderLeftColor = colors[sourceStatus] || "#5bc0de";
                
                // Zeiten kopieren
                timesDiv.querySelectorAll(".oh-time-slot").forEach(s => s.remove());
                sourceTimes.forEach((time, i) => {
                    const slot = createTimeSlot(time.open, time.close, i > 0);
                    timesDiv.insertBefore(slot, timesDiv.querySelector(".oh-add-time"));
                });
            });
            
            updateValue();
        }
        
        function createTimeSlot(openTime, closeTime, showRemove) {
            const div = document.createElement("div");
            div.className = "oh-time-slot";
            div.style.cssText = "display: flex; align-items: center; gap: 8px; margin-bottom: 5px;";
            
            div.innerHTML = \'<input type="time" class="form-control input-sm oh-time-open" value="\' + openTime + \'" style="width: 110px;">\' +
                \'<span style="color: #666;">–</span>\' +
                \'<input type="time" class="form-control input-sm oh-time-close" value="\' + closeTime + \'" style="width: 110px;">\' +
                (showRemove ? \'<button type="button" class="btn btn-xs btn-danger oh-remove-time" title="Zeitfenster entfernen"><i class="rex-icon fa-trash"></i></button>\' : "");
            
            return div;
        }
        
        function createSpecialEntry(index, data) {
            const div = document.createElement("div");
            div.className = "oh-special-entry panel panel-default";
            div.dataset.index = index;
            
            let timesHtml = "";
            const times = data.times || [{ open: "09:00", close: "14:00" }];
            times.forEach((time, i) => {
                timesHtml += \'<div class="oh-time-slot" style="display: flex; align-items: center; gap: 8px; margin-bottom: 5px;">\' +
                    \'<input type="time" class="form-control input-sm oh-time-open" value="\' + time.open + \'" style="width: 110px;">\' +
                    \'<span style="color: #666;">–</span>\' +
                    \'<input type="time" class="form-control input-sm oh-time-close" value="\' + time.close + \'" style="width: 110px;">\' +
                    (i > 0 ? \'<button type="button" class="btn btn-xs btn-danger oh-remove-time" title="Entfernen"><i class="rex-icon fa-trash"></i></button>\' : "") +
                    \'</div>\';
            });
            
            const dateInput = data.holiday 
                ? \'<input type="text" class="form-control input-sm oh-special-date" value="\' + data.date + \'" readonly style="background: #f5f5f5;"><small class="text-muted">Beweglich</small>\'
                : \'<input type="date" class="form-control input-sm oh-special-date" value="\' + data.date + \'">\';
            
            div.innerHTML = \'<div class="panel-body" style="display: flex; align-items: flex-start; gap: 10px; flex-wrap: wrap;">\' +
                \'<div style="flex: 0 0 140px;">\' + dateInput + \'</div>\' +
                \'<div style="flex: 0 0 180px;"><input type="text" class="form-control input-sm oh-special-name" value="\' + (data.name || "") + \'" placeholder="Bezeichnung"></div>\' +
                \'<div style="flex: 0 0 130px;"><select class="form-control input-sm oh-special-status">\' +
                    \'<option value="open"\' + (data.status === "open" ? " selected" : "") + \'>Geöffnet</option>\' +
                    \'<option value="closed"\' + (data.status === "closed" ? " selected" : "") + \'>Geschlossen</option>\' +
                \'</select></div>\' +
                \'<div class="oh-special-times" style="flex: 1;\' + (data.status !== "open" ? " display: none;" : "") + \'">\' +
                    timesHtml +
                    \'<button type="button" class="btn btn-xs btn-success oh-add-special-time" style="margin-top: 3px;"><i class="rex-icon fa-plus"></i></button>\' +
                \'</div>\' +
                \'<div><button type="button" class="btn btn-xs btn-danger oh-remove-special" title="Entfernen"><i class="rex-icon fa-trash"></i></button></div>\' +
            \'</div>\';
            
            if (data.holiday) {
                div.dataset.holiday = "true";
            }
            
            return div;
        }
        
        function updateValue() {
            const data = {
                regular: {},
                special: [],
                note: ""
            };
            
            // Reguläre Zeiten sammeln
            container.querySelectorAll(".oh-day-row").forEach(row => {
                const day = row.dataset.day;
                const status = row.querySelector(".oh-status-select").value;
                const times = [];
                
                if (status === "open") {
                    row.querySelectorAll(".oh-time-slot").forEach(slot => {
                        times.push({
                            open: slot.querySelector(".oh-time-open").value,
                            close: slot.querySelector(".oh-time-close").value
                        });
                    });
                }
                
                data.regular[day] = { status: status, times: times };
            });
            
            // Sonderzeiten sammeln
            container.querySelectorAll(".oh-special-entry").forEach(entry => {
                const status = entry.querySelector(".oh-special-status").value;
                const times = [];
                
                if (status === "open") {
                    entry.querySelectorAll(".oh-time-slot").forEach(slot => {
                        times.push({
                            open: slot.querySelector(".oh-time-open").value,
                            close: slot.querySelector(".oh-time-close").value
                        });
                    });
                }
                
                data.special.push({
                    date: entry.querySelector(".oh-special-date").value,
                    name: entry.querySelector(".oh-special-name").value,
                    status: status,
                    times: times,
                    holiday: entry.dataset.holiday === "true"
                });
            });
            
            // Hinweis-Text sammeln
            const noteInput = container.querySelector(".opening-hours-note-input");
            if (noteInput) {
                data.note = noteInput.value.trim();
            }
            
            valueInput.value = JSON.stringify(data);
            updatePreview();
        }
        
        function updatePreview() {
            const previewDiv = container.querySelector(".opening-hours-preview");
            if (!previewDiv) return;
            
            const today = new Date();
            const todayDay = WEEKDAY_KEYS[today.getDay() === 0 ? 6 : today.getDay() - 1];
            
            let html = \'<table class="oh-preview-table">\';
            html += \'<thead><tr><th>Tag</th><th>Öffnungszeiten</th></tr></thead>\';
            html += \'<tbody>\';
            
            container.querySelectorAll(".oh-day-row").forEach(row => {
                const day = row.dataset.day;
                const status = row.querySelector(".oh-status-select").value;
                const isToday = (day === todayDay);
                
                html += \'<tr class="\' + (isToday ? "oh-today" : "") + \'">\';
                html += \'<td>\' + WEEKDAYS[day] + (isToday ? " <small>(heute)</small>" : "") + \'</td>\';
                
                if (status === "closed") {
                    html += \'<td class="oh-closed">Geschlossen</td>\';
                } else if (status === "24h") {
                    html += \'<td class="oh-24h">24 Stunden geöffnet</td>\';
                } else {
                    const times = [];
                    row.querySelectorAll(".oh-time-slot").forEach(slot => {
                        const open = slot.querySelector(".oh-time-open").value;
                        const close = slot.querySelector(".oh-time-close").value;
                        if (open && close) {
                            times.push(open + " – " + close + " Uhr");
                        }
                    });
                    html += \'<td>\' + (times.length ? times.join(", ") : "–") + \'</td>\';
                }
                
                html += \'</tr>\';
            });
            
            html += \'</tbody></table>\';
            
            // Sonderzeiten
            const specials = container.querySelectorAll(".oh-special-entry");
            if (specials.length > 0) {
                html += \'<div class="oh-preview-special">\';
                html += \'<h4><i class="rex-icon fa-calendar"></i> Sonderöffnungszeiten</h4>\';
                html += \'<table class="oh-preview-table"><tbody>\';
                
                specials.forEach(entry => {
                    const date = entry.querySelector(".oh-special-date").value;
                    const name = entry.querySelector(".oh-special-name").value;
                    const status = entry.querySelector(".oh-special-status").value;
                    
                    html += \'<tr>\';
                    html += \'<td>\' + (name || date) + (name && date.indexOf("-") === 2 ? "" : (name ? " <small>(" + formatDate(date) + ")</small>" : "")) + \'</td>\';
                    
                    if (status === "closed") {
                        html += \'<td class="oh-closed">Geschlossen</td>\';
                    } else {
                        const times = [];
                        entry.querySelectorAll(".oh-time-slot").forEach(slot => {
                            const open = slot.querySelector(".oh-time-open").value;
                            const close = slot.querySelector(".oh-time-close").value;
                            if (open && close) {
                                times.push(open + " – " + close + " Uhr");
                            }
                        });
                        html += \'<td>\' + (times.length ? times.join(", ") : "–") + \'</td>\';
                    }
                    
                    html += \'</tr>\';
                });
                
                html += \'</tbody></table></div>\';
            }
            
            previewDiv.innerHTML = html;
        }
        
        function formatDate(dateStr) {
            if (dateStr.indexOf("easter") !== -1) return "beweglich";
            if (dateStr.match(/^\\d{2}-\\d{2}$/)) {
                const [month, day] = dateStr.split("-");
                return day + "." + month + ".";
            }
            const parts = dateStr.split("-");
            if (parts.length === 3) {
                return parts[2] + "." + parts[1] + "." + parts[0];
            }
            return dateStr;
        }
        
        function debounce(fn, delay) {
            let timer;
            return function(...args) {
                clearTimeout(timer);
                timer = setTimeout(() => fn.apply(this, args), delay);
            };
        }
        
        // Initial preview
        updateValue();
    }
})();
</script>';
    }
}

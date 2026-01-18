<?php

namespace FriendsOfRedaxo\TemplateManager;

/**
 * Helper-Klasse für die Frontend-Ausgabe von Öffnungszeiten
 * 
 * Ermöglicht individuelle Gestaltung und Übersetzung der Öffnungszeiten.
 * 
 * Beispiel:
 * ```php
 * use FriendsOfRedaxo\TemplateManager\OpeningHoursHelper;
 * use FriendsOfRedaxo\TemplateManager\TemplateManager;
 * 
 * $helper = new OpeningHoursHelper(TemplateManager::get('tm_opening_hours'));
 * 
 * // Reguläre Zeiten als Array
 * foreach ($helper->getRegular() as $day) {
 *     echo $day['label'] . ': ' . $day['formatted'];
 * }
 * 
 * // Prüfen ob gerade geöffnet
 * if ($helper->isOpenNow()) {
 *     echo 'Wir haben geöffnet!';
 * }
 * ```
 */
class OpeningHoursHelper
{
    private array $data = [];
    private string $locale = 'de';
    
    /**
     * Übersetzbare Texte (können überschrieben werden)
     */
    private array $translations = [
        'de' => [
            'weekdays' => [
                'monday' => 'Montag',
                'tuesday' => 'Dienstag',
                'wednesday' => 'Mittwoch',
                'thursday' => 'Donnerstag',
                'friday' => 'Freitag',
                'saturday' => 'Samstag',
                'sunday' => 'Sonntag',
            ],
            'weekdays_short' => [
                'monday' => 'Mo',
                'tuesday' => 'Di',
                'wednesday' => 'Mi',
                'thursday' => 'Do',
                'friday' => 'Fr',
                'saturday' => 'Sa',
                'sunday' => 'So',
            ],
            'status' => [
                'closed' => 'Geschlossen',
                'open_24h' => '24 Stunden geöffnet',
                'open' => 'Geöffnet',
            ],
            'labels' => [
                'today' => 'heute',
                'opening_hours' => 'Öffnungszeiten',
                'special_hours' => 'Sonderöffnungszeiten',
                'we_are_open' => 'Wir haben geöffnet',
                'we_are_closed' => 'Wir haben geschlossen',
                'opens_at' => 'Öffnet um',
                'closes_at' => 'Schließt um',
                'time_suffix' => 'Uhr',
            ],
        ],
        'en' => [
            'weekdays' => [
                'monday' => 'Monday',
                'tuesday' => 'Tuesday',
                'wednesday' => 'Wednesday',
                'thursday' => 'Thursday',
                'friday' => 'Friday',
                'saturday' => 'Saturday',
                'sunday' => 'Sunday',
            ],
            'weekdays_short' => [
                'monday' => 'Mon',
                'tuesday' => 'Tue',
                'wednesday' => 'Wed',
                'thursday' => 'Thu',
                'friday' => 'Fri',
                'saturday' => 'Sat',
                'sunday' => 'Sun',
            ],
            'status' => [
                'closed' => 'Closed',
                'open_24h' => 'Open 24 hours',
                'open' => 'Open',
            ],
            'labels' => [
                'today' => 'today',
                'opening_hours' => 'Opening Hours',
                'special_hours' => 'Special Hours',
                'we_are_open' => 'We are open',
                'we_are_closed' => 'We are closed',
                'opens_at' => 'Opens at',
                'closes_at' => 'Closes at',
                'time_suffix' => '',
            ],
        ],
    ];
    
    /**
     * Wochentag-Keys in korrekter Reihenfolge
     */
    private const WEEKDAY_ORDER = [
        'monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday', 'sunday'
    ];
    
    /**
     * Constructor
     * 
     * @param string|null $json JSON-String aus TemplateManager::get()
     * @param string $locale Sprache für Übersetzungen ('de', 'en' oder eigene)
     */
    public function __construct(?string $json, string $locale = 'de')
    {
        $this->locale = $locale;
        
        if (!empty($json)) {
            $decoded = json_decode($json, true);
            if (is_array($decoded)) {
                $this->data = $decoded;
            }
        }
    }
    
    /**
     * Eigene Übersetzungen setzen
     * 
     * @param string $locale Sprach-Key
     * @param array $translations Übersetzungs-Array (siehe $this->translations für Struktur)
     * @return self
     */
    public function setTranslations(string $locale, array $translations): self
    {
        $this->translations[$locale] = array_replace_recursive(
            $this->translations[$this->locale] ?? $this->translations['de'],
            $translations
        );
        $this->locale = $locale;
        return $this;
    }
    
    /**
     * Sprache ändern
     * 
     * @param string $locale Sprach-Key
     * @return self
     */
    public function setLocale(string $locale): self
    {
        $this->locale = $locale;
        return $this;
    }
    
    /**
     * Übersetzung abrufen
     * 
     * @param string $key Punkt-separierter Key (z.B. 'weekdays.monday', 'status.closed')
     * @param string|null $fallback Fallback-Wert
     * @return string
     */
    public function translate(string $key, ?string $fallback = null): string
    {
        $translations = $this->translations[$this->locale] ?? $this->translations['de'];
        $parts = explode('.', $key);
        
        $value = $translations;
        foreach ($parts as $part) {
            if (!is_array($value) || !isset($value[$part])) {
                return $fallback ?? $key;
            }
            $value = $value[$part];
        }
        
        return is_string($value) ? $value : ($fallback ?? $key);
    }
    
    /**
     * Prüft ob Daten vorhanden sind
     * 
     * @return bool
     */
    public function hasData(): bool
    {
        return !empty($this->data) && isset($this->data['regular']);
    }
    
    /**
     * Hinweis-Text abrufen (z.B. "Weitere Termine nach Absprache")
     * 
     * @return string|null
     */
    public function getNote(): ?string
    {
        $note = $this->data['note'] ?? null;
        return !empty($note) ? $note : null;
    }
    
    /**
     * Prüft ob ein Hinweis-Text vorhanden ist
     * 
     * @return bool
     */
    public function hasNote(): bool
    {
        return !empty($this->data['note']);
    }
    
    /**
     * Reguläre Öffnungszeiten als Array
     * 
     * @param bool $shortLabels Kurze Wochentag-Namen verwenden
     * @return array Array mit Wochentagen und deren Daten
     */
    public function getRegular(bool $shortLabels = false): array
    {
        if (!$this->hasData()) {
            return [];
        }
        
        $result = [];
        $todayKey = $this->getTodayKey();
        $labelKey = $shortLabels ? 'weekdays_short' : 'weekdays';
        
        foreach (self::WEEKDAY_ORDER as $dayKey) {
            $dayData = $this->data['regular'][$dayKey] ?? ['status' => 'closed', 'times' => []];
            $status = $dayData['status'] ?? 'closed';
            $times = $dayData['times'] ?? [];
            $isToday = ($dayKey === $todayKey);
            
            $result[$dayKey] = [
                'key' => $dayKey,
                'label' => $this->translate($labelKey . '.' . $dayKey),
                'label_short' => $this->translate('weekdays_short.' . $dayKey),
                'label_full' => $this->translate('weekdays.' . $dayKey),
                'status' => $status,
                'status_label' => $this->getStatusLabel($status),
                'is_today' => $isToday,
                'is_open' => $status === 'open' || $status === '24h',
                'is_closed' => $status === 'closed',
                'is_24h' => $status === '24h',
                'times' => $times,
                'times_formatted' => $this->formatTimeSlots($times),
                'formatted' => $this->formatDayStatus($status, $times),
            ];
        }
        
        return $result;
    }
    
    /**
     * Reguläre Öffnungszeiten gruppiert nach gleichen Zeiten
     * 
     * Aufeinanderfolgende Tage mit identischen Zeiten werden zusammengefasst.
     * Beispiel: "Mo - Fr: 09:00 - 18:00" statt einzelner Tage.
     * 
     * @return array Array mit gruppierten Einträgen
     */
    public function getRegularGrouped(): array
    {
        if (!$this->hasData()) {
            return [];
        }
        
        $regular = $this->getRegular();
        $groups = [];
        $currentGroup = null;
        
        foreach (self::WEEKDAY_ORDER as $dayKey) {
            $day = $regular[$dayKey];
            // Signatur für Vergleich: Status + Zeiten
            $signature = $day['status'] . '|' . json_encode($day['times']);
            
            if ($currentGroup === null || $currentGroup['signature'] !== $signature) {
                // Neue Gruppe starten
                if ($currentGroup !== null) {
                    $groups[] = $this->finalizeGroup($currentGroup);
                }
                $currentGroup = [
                    'signature' => $signature,
                    'days' => [$dayKey],
                    'first_day' => $day,
                    'last_day' => $day,
                    'contains_today' => $day['is_today'],
                ];
            } else {
                // Zur aktuellen Gruppe hinzufügen
                $currentGroup['days'][] = $dayKey;
                $currentGroup['last_day'] = $day;
                if ($day['is_today']) {
                    $currentGroup['contains_today'] = true;
                }
            }
        }
        
        // Letzte Gruppe abschließen
        if ($currentGroup !== null) {
            $groups[] = $this->finalizeGroup($currentGroup);
        }
        
        return $groups;
    }
    
    /**
     * Gruppe finalisieren und Label erstellen
     * 
     * @param array $group Gruppen-Daten
     * @return array Finalisierte Gruppe
     */
    private function finalizeGroup(array $group): array
    {
        $firstDay = $group['first_day'];
        $lastDay = $group['last_day'];
        $dayCount = count($group['days']);
        
        // Label erstellen
        if ($dayCount === 1) {
            // Einzelner Tag
            $label = $firstDay['label_short'];
            $labelFull = $firstDay['label_full'];
        } elseif ($dayCount === 2) {
            // Zwei Tage: "Mo + Di" oder "Mo, Di"
            $label = $firstDay['label_short'] . ', ' . $lastDay['label_short'];
            $labelFull = $firstDay['label_full'] . ', ' . $lastDay['label_full'];
        } else {
            // Bereich: "Mo - Fr"
            $label = $firstDay['label_short'] . ' - ' . $lastDay['label_short'];
            $labelFull = $firstDay['label_full'] . ' - ' . $lastDay['label_full'];
        }
        
        return [
            'label' => $label,
            'label_full' => $labelFull,
            'days' => $group['days'],
            'day_count' => $dayCount,
            'status' => $firstDay['status'],
            'status_label' => $firstDay['status_label'],
            'is_open' => $firstDay['is_open'],
            'is_closed' => $firstDay['is_closed'],
            'is_24h' => $firstDay['is_24h'],
            'times' => $firstDay['times'],
            'times_formatted' => $firstDay['times_formatted'],
            'formatted' => $firstDay['formatted'],
            'contains_today' => $group['contains_today'],
        ];
    }
    
    /**
     * Sonderöffnungszeiten als Array
     * 
     * @param int|null $limit Maximale Anzahl (null = alle)
     * @param bool $futureOnly Nur zukünftige Termine
     * @return array
     */
    public function getSpecial(?int $limit = null, bool $futureOnly = false): array
    {
        if (!$this->hasData() || empty($this->data['special'])) {
            return [];
        }
        
        $result = [];
        $today = date('Y-m-d');
        
        foreach ($this->data['special'] as $entry) {
            $date = $entry['date'] ?? '';
            $status = $entry['status'] ?? 'closed';
            $times = $entry['times'] ?? [];
            
            // Für bewegliche Feiertage (easter+X) Datum berechnen
            $actualDate = $this->resolveDate($date);
            
            // Filter: nur zukünftige
            if ($futureOnly && $actualDate && $actualDate < $today) {
                continue;
            }
            
            $result[] = [
                'date' => $date,
                'date_resolved' => $actualDate,
                'date_formatted' => $actualDate ? $this->formatDate($actualDate) : $date,
                'name' => $entry['name'] ?? '',
                'display_name' => !empty($entry['name']) ? $entry['name'] : $this->formatDate($actualDate ?: $date),
                'status' => $status,
                'status_label' => $this->getStatusLabel($status),
                'is_holiday' => $entry['holiday'] ?? false,
                'is_open' => $status === 'open',
                'is_closed' => $status === 'closed',
                'times' => $times,
                'times_formatted' => $this->formatTimeSlots($times),
                'formatted' => $this->formatDayStatus($status, $times),
            ];
        }
        
        // Nach Datum sortieren
        usort($result, function ($a, $b) {
            return strcmp($a['date_resolved'] ?? $a['date'], $b['date_resolved'] ?? $b['date']);
        });
        
        // Limit anwenden
        if ($limit !== null && $limit > 0) {
            $result = array_slice($result, 0, $limit);
        }
        
        return $result;
    }
    
    /**
     * Heutigen Tag mit Status
     * 
     * @return array|null
     */
    public function getToday(): ?array
    {
        $regular = $this->getRegular();
        $todayKey = $this->getTodayKey();
        
        return $regular[$todayKey] ?? null;
    }
    
    /**
     * Prüft ob aktuell geöffnet ist
     * 
     * Berücksichtigt:
     * - Reguläre Öffnungszeiten des aktuellen Tages
     * - Aktuelle Uhrzeit
     * - Sonderöffnungszeiten für heute
     * 
     * @return bool
     */
    public function isOpenNow(): bool
    {
        if (!$this->hasData()) {
            return false;
        }
        
        $todayDate = date('Y-m-d');
        $currentTime = date('H:i');
        
        // Erst Sonderzeiten prüfen (haben Vorrang)
        foreach ($this->data['special'] ?? [] as $special) {
            $resolvedDate = $this->resolveDate($special['date'] ?? '');
            
            if ($resolvedDate === $todayDate) {
                if (($special['status'] ?? 'closed') === 'closed') {
                    return false;
                }
                return $this->isTimeInSlots($currentTime, $special['times'] ?? []);
            }
        }
        
        // Dann reguläre Zeiten
        $todayKey = $this->getTodayKey();
        $todayData = $this->data['regular'][$todayKey] ?? ['status' => 'closed'];
        $status = $todayData['status'] ?? 'closed';
        
        if ($status === 'closed') {
            return false;
        }
        
        if ($status === '24h') {
            return true;
        }
        
        return $this->isTimeInSlots($currentTime, $todayData['times'] ?? []);
    }
    
    /**
     * Aktuellen Status als Array (für Anzeige "Jetzt geöffnet/geschlossen")
     * 
     * @return array
     */
    public function getCurrentStatus(): array
    {
        $isOpen = $this->isOpenNow();
        $today = $this->getToday();
        $currentTime = date('H:i');
        
        $result = [
            'is_open' => $isOpen,
            'label' => $isOpen 
                ? $this->translate('labels.we_are_open') 
                : $this->translate('labels.we_are_closed'),
            'today' => $today,
            'next_change' => null,
            'next_change_label' => null,
        ];
        
        // Nächste Statusänderung berechnen
        if ($today && $today['status'] === 'open') {
            foreach ($today['times'] as $slot) {
                if ($isOpen && $currentTime >= $slot['open'] && $currentTime < $slot['close']) {
                    $result['next_change'] = $slot['close'];
                    $result['next_change_label'] = $this->translate('labels.closes_at') . ' ' . $slot['close'];
                    if ($this->translate('labels.time_suffix')) {
                        $result['next_change_label'] .= ' ' . $this->translate('labels.time_suffix');
                    }
                    break;
                } elseif (!$isOpen && $currentTime < $slot['open']) {
                    $result['next_change'] = $slot['open'];
                    $result['next_change_label'] = $this->translate('labels.opens_at') . ' ' . $slot['open'];
                    if ($this->translate('labels.time_suffix')) {
                        $result['next_change_label'] .= ' ' . $this->translate('labels.time_suffix');
                    }
                    break;
                }
            }
        }
        
        return $result;
    }
    
    /**
     * Rohdaten abrufen
     * 
     * @return array
     */
    public function getRawData(): array
    {
        return $this->data;
    }
    
    // ==================== Private Helper Methods ====================
    
    /**
     * Aktuellen Wochentag-Key ermitteln
     */
    private function getTodayKey(): string
    {
        return strtolower(date('l'));
    }
    
    /**
     * Status-Label abrufen
     */
    private function getStatusLabel(string $status): string
    {
        return match ($status) {
            'closed' => $this->translate('status.closed'),
            '24h' => $this->translate('status.open_24h'),
            'open' => $this->translate('status.open'),
            default => $status,
        };
    }
    
    /**
     * Zeitslots formatieren
     * 
     * @param array $times Array von ['open' => 'HH:MM', 'close' => 'HH:MM']
     * @return string Formatierte Zeiten (z.B. "09:00–12:00, 14:00–18:00 Uhr")
     */
    private function formatTimeSlots(array $times): string
    {
        if (empty($times)) {
            return '';
        }
        
        $formatted = [];
        foreach ($times as $slot) {
            $formatted[] = ($slot['open'] ?? '') . '–' . ($slot['close'] ?? '');
        }
        
        $result = implode(', ', $formatted);
        $suffix = $this->translate('labels.time_suffix');
        
        return $suffix ? $result . ' ' . $suffix : $result;
    }
    
    /**
     * Tagesstatus formatieren
     */
    private function formatDayStatus(string $status, array $times): string
    {
        return match ($status) {
            'closed' => $this->translate('status.closed'),
            '24h' => $this->translate('status.open_24h'),
            'open' => $this->formatTimeSlots($times),
            default => $status,
        };
    }
    
    /**
     * Datum formatieren
     */
    private function formatDate(?string $date): string
    {
        if (empty($date)) {
            return '';
        }
        
        // Festes Datum (MM-DD)
        if (preg_match('/^(\d{2})-(\d{2})$/', $date, $m)) {
            return $m[2] . '.' . $m[1] . '.';
        }
        
        // ISO-Datum (YYYY-MM-DD)
        if (preg_match('/^(\d{4})-(\d{2})-(\d{2})$/', $date, $m)) {
            return $m[3] . '.' . $m[2] . '.';
        }
        
        return $date;
    }
    
    /**
     * Bewegliches Datum auflösen (easter+X etc.)
     */
    private function resolveDate(string $date): ?string
    {
        if (empty($date)) {
            return null;
        }
        
        // Bereits ein vollständiges Datum
        if (preg_match('/^\d{4}-\d{2}-\d{2}$/', $date)) {
            return $date;
        }
        
        // Festes Datum (MM-DD) für aktuelles Jahr
        if (preg_match('/^(\d{2})-(\d{2})$/', $date, $m)) {
            return date('Y') . '-' . $m[1] . '-' . $m[2];
        }
        
        // Easter-basiertes Datum
        if (str_starts_with($date, 'easter')) {
            $year = (int) date('Y');
            
            // Offset extrahieren (easter, easter+0, easter+1, easter-2 etc.)
            $offset = 0;
            if (preg_match('/easter([+-]\d+)/', $date, $m)) {
                $offset = (int) $m[1];
            }
            
            // easter_date() verwenden wenn verfügbar
            if (function_exists('easter_date')) {
                try {
                    $easter = easter_date($year);
                    return date('Y-m-d', $easter + ($offset * 86400));
                } catch (\Throwable $e) {
                    // Fallback bei Fehler
                }
            }
            
            // Fallback: easter_days() verwenden (benötigt auch calendar extension)
            if (function_exists('easter_days')) {
                $days = easter_days($year);
                $easterTimestamp = mktime(0, 0, 0, 3, 21 + $days + $offset, $year);
                return date('Y-m-d', $easterTimestamp);
            }
            
            // Letzter Fallback: Manuelle Berechnung (Anonymous Gregorian algorithm)
            $easterDate = $this->calculateEaster($year);
            if ($easterDate) {
                $timestamp = strtotime($easterDate . ' +' . $offset . ' days');
                return date('Y-m-d', $timestamp);
            }
        }
        
        return null;
    }
    
    /**
     * Manuelle Osterberechnung (Gregorianischer Kalender)
     * Fallback wenn keine PHP calendar extension verfügbar ist
     */
    private function calculateEaster(int $year): ?string
    {
        // Anonymous Gregorian algorithm
        $a = $year % 19;
        $b = intdiv($year, 100);
        $c = $year % 100;
        $d = intdiv($b, 4);
        $e = $b % 4;
        $f = intdiv($b + 8, 25);
        $g = intdiv($b - $f + 1, 3);
        $h = (19 * $a + $b - $d - $g + 15) % 30;
        $i = intdiv($c, 4);
        $k = $c % 4;
        $l = (32 + 2 * $e + 2 * $i - $h - $k) % 7;
        $m = intdiv($a + 11 * $h + 22 * $l, 451);
        $month = intdiv($h + $l - 7 * $m + 114, 31);
        $day = (($h + $l - 7 * $m + 114) % 31) + 1;
        
        return sprintf('%04d-%02d-%02d', $year, $month, $day);
    }
    
    /**
     * Prüft ob aktuelle Zeit in einem der Zeitslots liegt
     */
    private function isTimeInSlots(string $currentTime, array $slots): bool
    {
        foreach ($slots as $slot) {
            $open = $slot['open'] ?? '';
            $close = $slot['close'] ?? '';
            
            if ($currentTime >= $open && $currentTime < $close) {
                return true;
            }
        }
        
        return false;
    }
}

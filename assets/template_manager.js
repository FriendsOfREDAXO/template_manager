/**
 * Template Manager: Einstellung im Fenster bearbeiten (Ajax über rex_api, Design wie MediaPlace).
 *
 * Jeder Link mit data-tm-settings="tm_…" (siehe TemplateManager::getSettingsLink()) öffnet die Gruppe
 * dieses Feldes im Overlay – ohne Seitenwechsel. Ohne JavaScript führt der Link zur Einstellungsseite.
 * Optional: data-tm-domain, data-tm-template, data-tm-title, data-tm-reload (Seite nach dem Speichern neu laden).
 */
(function ($) {
    'use strict';
    if (!$) {
        return;
    }

    var overlay = null;
    var lastFocus = null;
    var current = null;

    function api(params) {
        var base = (window.rex && rex.template_manager && rex.template_manager.settings_api) || '';
        return base + (base.indexOf('?') === -1 ? '?' : '&') + $.param(params);
    }

    function build() {
        if (overlay) {
            return overlay;
        }
        overlay = $(
            '<div id="tm-settings-overlay" role="dialog" aria-modal="true" aria-labelledby="tm-so-title" hidden>' +
                '<div class="tm-so-modal">' +
                    '<div class="tm-so-header">' +
                        '<i class="tm-so-icon rex-icon fa-sliders" aria-hidden="true"></i>' +
                        '<div class="tm-so-heading"><h2 id="tm-so-title"></h2><p class="tm-so-sub"></p></div>' +
                        '<a class="tm-so-page" href="#"><i class="rex-icon fa-external-link" aria-hidden="true"></i> Alle Einstellungen</a>' +
                        '<button type="button" class="tm-so-close" aria-label="Schließen"><i class="rex-icon fa-times" aria-hidden="true"></i></button>' +
                    '</div>' +
                    '<form class="tm-so-body" id="tm-so-form" novalidate></form>' +
                    '<div class="tm-so-footer">' +
                        '<span class="tm-so-status" aria-live="polite"></span>' +
                        '<button type="button" class="tm-so-btn tm-so-cancel">Abbrechen</button>' +
                        '<button type="submit" form="tm-so-form" class="tm-so-btn tm-so-save"><i class="rex-icon rex-icon-save" aria-hidden="true"></i> Speichern</button>' +
                    '</div>' +
                '</div>' +
            '</div>'
        ).appendTo(document.body);

        overlay.on('click', function (e) { if (e.target === overlay[0]) { close(); } });
        overlay.find('.tm-so-close, .tm-so-cancel').on('click', close);
        overlay.on('keydown', function (e) {
            if (e.key === 'Escape') {
                close();
            } else if (e.key === 'Tab') {
                // Fokus im Fenster halten
                var $f = overlay.find('a[href], button:not([disabled]), input:not([type=hidden]):not([disabled]), select, textarea').filter(':visible');
                if (!$f.length) { return; }
                if (e.shiftKey && document.activeElement === $f[0]) { e.preventDefault(); $f.last().trigger('focus'); }
                else if (!e.shiftKey && document.activeElement === $f[$f.length - 1]) { e.preventDefault(); $f.first().trigger('focus'); }
            }
        });
        overlay.find('form').on('submit', function (e) {
            e.preventDefault();
            save();
        });
        return overlay;
    }

    function status(text, kind) {
        overlay.find('.tm-so-status').text(text || '').attr('data-kind', kind || '');
    }

    function open(link) {
        var $o = build();
        current = {
            key: link.getAttribute('data-tm-settings'),
            domain: link.getAttribute('data-tm-domain') || '',
            template: link.getAttribute('data-tm-template') || '',
            reload: link.hasAttribute('data-tm-reload'),
            saved: false
        };
        lastFocus = link;
        $o.find('#tm-so-title').text(link.getAttribute('data-tm-title') || link.textContent.trim());
        $o.find('.tm-so-sub').text('');
        $o.find('.tm-so-page').attr('href', link.getAttribute('href'));
        $o.find('form').html('<p class="tm-so-loading"><i class="rex-icon fa-spinner fa-spin" aria-hidden="true"></i> Lädt …</p>');
        $o.find('.tm-so-save').prop('disabled', true);
        status('');
        $o.prop('hidden', false);
        $('body').addClass('tm-so-open');
        $o.find('.tm-so-close').trigger('focus');

        $.getJSON(api({ key: current.key, domain_id: current.domain, template_id: current.template }))
            .done(function (data) {
                $o.find('#tm-so-title').text(data.title || current.key);
                $o.find('.tm-so-sub').text(data.subtitle || '');
                $o.find('.tm-so-icon').attr('class', 'tm-so-icon rex-icon ' + (data.icon ? data.icon.replace(/^rex-icon\s*/, '') : 'fa-sliders'));
                if (data.page_url) { $o.find('.tm-so-page').attr('href', data.page_url); }
                var $form = $o.find('form').html(data.html);
                // Widgets (Medienpool, Linkmap, Selectpicker …) wie bei nachgeladenen Inhalten initialisieren
                $(document).trigger('rex:ready', [$form]);
                $o.find('.tm-so-save').prop('disabled', false);
                var $focus = $form.find('.tm-field--focus');
                if ($focus.length) {
                    $focus[0].scrollIntoView({ block: 'center' });
                    $focus.find('input:not([type=hidden]), select, textarea').filter(':visible').first().trigger('focus');
                }
            })
            .fail(function (xhr) {
                var msg = (xhr.responseJSON && xhr.responseJSON.error) || 'Die Einstellung konnte nicht geladen werden.';
                $o.find('form').html('<p class="tm-so-error">' + $('<span>').text(msg).html() + '</p>');
            });
    }

    function save() {
        var $o = overlay;
        var data = new FormData($o.find('form')[0]);
        var $btn = $o.find('.tm-so-save').prop('disabled', true);
        status('Speichert …');
        $.ajax({
            url: api({ key: current.key, domain_id: current.domain, template_id: current.template }),
            method: 'POST',
            data: data,
            processData: false,
            contentType: false,
            dataType: 'json'
        }).done(function (res) {
            current.saved = true;
            status(res.message || 'Gespeichert', 'success');
            setTimeout(close, 700);
        }).fail(function (xhr) {
            status((xhr.responseJSON && xhr.responseJSON.error) || 'Speichern fehlgeschlagen', 'error');
            $btn.prop('disabled', false);
        });
    }

    function close() {
        if (!overlay || overlay.prop('hidden')) {
            return;
        }
        overlay.prop('hidden', true);
        overlay.find('form').empty();
        $('body').removeClass('tm-so-open');
        if (current && current.saved && current.reload) {
            window.location.reload();
            return;
        }
        if (lastFocus) { lastFocus.focus(); }
    }

    $(document).on('click', '[data-tm-settings]', function (e) {
        if (e.ctrlKey || e.metaKey || e.shiftKey || e.button === 1 || !(window.rex && rex.template_manager)) {
            return; // neuer Tab / ohne API: normale Einstellungsseite
        }
        e.preventDefault();
        open(this);
    });
})(window.jQuery);

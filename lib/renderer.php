<?php

namespace TipsForBitrix;

use Bitrix\Main\Page\Asset;
use Bitrix\Main\Web\Json;

class Renderer
{
    public static function onProlog()
    {
        if (!Manager::canManageNotes()) {
            return;
        }

        global $APPLICATION;

        if (!is_object($APPLICATION)) {
            return;
        }

        $area = Manager::getCurrentArea();

        if ($area !== 'admin') {
            return;
        }

        if (self::shouldSkipPage()) {
            return;
        }

        $url = Manager::getCurrentUrl($area);
        $note = Manager::getNote($area, $url);

        $config = array(
            'area' => $area,
            'url' => $url,
            'noteText' => $note ? (string) $note['NOTE_TEXT'] : '',
            'noteId' => $note ? (int) $note['ID'] : 0,
            'noteStatus' => $note ? (string) $note['STATUS'] : 'default',
            'noteColor' => $note ? (string) $note['COLOR'] : 'sand',
            'statusMap' => Manager::getStatusMap(),
            'colorPresets' => Manager::getColorPresets(),
            'ajaxUrl' => '/bitrix/admin/reineke_tipsforbitrix_ajax.php',
            'notesListUrl' => '/bitrix/admin/reineke_tipsforbitrix_notes.php?lang=' . urlencode(defined('LANGUAGE_ID') ? LANGUAGE_ID : 'ru'),
            'sessid' => bitrix_sessid(),
            'isAdminPage' => $area === 'admin',
        );

        Asset::getInstance()->addString(self::getStyle(), true);
        Asset::getInstance()->addString(
            '<script>window.TipsForBitrixNoteConfig = ' . Json::encode($config) . ';</script>' . self::getScript(),
            true
        );
    }

    protected static function shouldSkipPage()
    {
        $path = isset($_SERVER['PHP_SELF']) ? (string) $_SERVER['PHP_SELF'] : '';
        $skippedPages = array(
            '/bitrix/admin/reineke_tipsforbitrix_notes.php',
        );

        return in_array($path, $skippedPages, true);
    }

    protected static function getStyle()
    {
        return <<<'HTML'
<style>
.tfb-note-root{box-sizing:border-box}
.tfb-note-root--panel{display:inline-block;vertical-align:top;position:relative;height:100%}
.tfb-note-card{--tfb-accent:#D3A84F;--tfb-accent-soft:rgba(211,168,79,.12);--tfb-accent-glow:rgba(211,168,79,.20);--tfb-accent-border:rgba(211,168,79,.24);--tfb-control-width:170px;position:relative;box-sizing:border-box;font:14px/1.5 "Segoe UI",-apple-system,BlinkMacSystemFont,"Helvetica Neue",Arial,sans-serif;color:#251d13;background:radial-gradient(circle at 14% 20%,var(--tfb-accent-glow),transparent 34%),radial-gradient(circle at 100% 100%,var(--tfb-accent-soft),transparent 42%),linear-gradient(115deg,rgba(255,255,255,.98) 0%,rgba(250,251,252,.95) 34%,var(--tfb-accent-soft) 100%);border:1px solid var(--tfb-accent-border);border-radius:18px;box-shadow:0 18px 45px rgba(40,34,24,.10),inset 0 1px 0 rgba(255,255,255,.82);overflow:hidden}
.tfb-note-card::before{content:"";position:absolute;inset:0;background:radial-gradient(circle at top left,rgba(255,255,255,.62),transparent 32%),linear-gradient(180deg,rgba(255,255,255,.18),transparent 40%);pointer-events:none}
.tfb-note-card::after{content:"";position:absolute;left:0;top:18px;bottom:18px;width:0;border-radius:0 999px 999px 0;background:transparent;box-shadow:none;transition:width .18s ease,background .18s ease}
.tfb-note-card *{box-sizing:border-box;position:relative;z-index:1}
.tfb-note-card--status-important{border-color:rgba(219,101,86,.28);box-shadow:0 18px 45px rgba(40,34,24,.10),0 0 0 1px rgba(219,101,86,.06),inset 0 1px 0 rgba(255,255,255,.82)}
.tfb-note-card--status-important::after{width:6px;background:linear-gradient(180deg,#e56c5d,#c84b3e);box-shadow:0 0 18px rgba(200,75,62,.28)}
.tfb-note-card--status-future{border-color:rgba(211,168,79,.30);box-shadow:0 18px 45px rgba(40,34,24,.10),0 0 0 1px rgba(211,168,79,.05),inset 0 1px 0 rgba(255,255,255,.82)}
.tfb-note-card--status-future::after{width:6px;background:linear-gradient(180deg,#f0c661,#d3a84f);box-shadow:0 0 18px rgba(211,168,79,.24)}
.tfb-note-card--admin{margin:14px 0 20px;padding:18px}
.tfb-note-card--public{max-width:460px;width:100%;padding:18px}
.tfb-note-card--public-float{position:fixed;right:20px;bottom:20px;z-index:1200;width:calc(100% - 40px)}
.tfb-note-card--panel{position:absolute;top:100%;left:0;z-index:1250;min-width:340px;max-width:380px;margin:10px 0 0}
.tfb-note-card--panel.tfb-note-card--compact{position:static;display:inline-block;min-width:0;max-width:none;margin:0}
.tfb-note-card--compact{width:auto;max-width:none;padding:12px 14px;background:rgba(255,255,255,.98)}
.tfb-note-card__head{display:flex;align-items:flex-start;justify-content:space-between;gap:12px;margin:0 0 10px}
.tfb-note-card__head-main{min-width:0;flex:1 1 auto}
.tfb-note-card__title-row{display:flex;align-items:self-start;gap:10px;flex-wrap:wrap}
.tfb-note-card__title{margin:0 0 6px;font-size:15px;font-weight:700;letter-spacing:.01em}
.tfb-note-card__title-link{display:inline-flex;align-items:center;min-height:24px;padding:0 10px;border:1px solid var(--tfb-accent-border);border-radius:999px;background:var(--tfb-accent-soft);color:var(--tfb-accent);font-size:11px;font-weight:700;line-height:1;text-decoration:none;box-shadow:inset 0 1px 0 rgba(255,255,255,.88)}
.tfb-note-card__title-link:hover{text-decoration:none;background:var(--tfb-accent-soft);box-shadow:inset 0 1px 0 rgba(255,255,255,.88),0 0 0 3px rgba(255,255,255,.45)}
.tfb-note-card__meta{display:inline-flex;max-width:100%;margin:0 0 16px;padding:7px 11px;border:1px solid rgba(92,102,122,.16);border-radius:999px;background:rgba(255,255,255,.72);font:12px/1.35 Consolas,Monaco,monospace;color:#6f675b;word-break:break-all;box-shadow:inset 0 1px 0 rgba(255,255,255,.85)}
.tfb-note-card__text{margin:0 0 16px;padding:16px 18px;border:1px solid rgba(92,102,122,.12);border-radius:14px;background:rgba(255,255,255,.74);color:#2f271c;white-space:normal;box-shadow:inset 0 1px 0 rgba(255,255,255,.88)}
.tfb-note-card__text--with-fab{padding-bottom:18px}
.tfb-note-card__toolbar{margin:0 0 16px;padding:0}
.tfb-note-card__actions{display:grid;grid-template-columns:repeat(auto-fit,var(--tfb-control-width));gap:10px;justify-content:start;align-items:center}
.tfb-note-card__editor{display:none;margin-top:16px;padding:16px 0 0;border-top:1px solid rgba(92,102,122,.12);background:transparent;box-shadow:none}
.tfb-note-card__editor.is-visible{display:block}
.tfb-note-card__textarea{display:block;width:100%;min-height:156px;padding:14px 15px;border:1px solid rgba(92,102,122,.22);border-radius:12px;background:rgba(255,255,255,.94);font:14px/1.55 Consolas,Monaco,monospace;color:#2a241b;resize:vertical;outline:none;box-shadow:inset 0 1px 2px rgba(22,26,32,.04);transition:border-color .18s ease,box-shadow .18s ease}
.tfb-note-card__textarea:focus{border-color:var(--tfb-accent);box-shadow:0 0 0 3px var(--tfb-accent-soft)}
.tfb-note-card__feedback{display:inline-flex;align-items:center;min-height:34px;margin-top:12px;padding:7px 11px;border-radius:10px;background:rgba(47,94,38,.08);color:#2f5b26;font-size:12px;font-weight:600}
.tfb-note-card__feedback.is-error{background:rgba(173,43,36,.08);color:#ad2b24}
.tfb-note-card__btn,.tfb-note-card__status-option{display:inline-flex;align-items:center;justify-content:center;width:var(--tfb-control-width);min-height:38px;padding:8px 14px;border-radius:10px;font:700 13px/1.1 "Segoe UI",-apple-system,BlinkMacSystemFont,"Helvetica Neue",Arial,sans-serif;cursor:pointer;text-align:center;text-decoration:none;transition:transform .16s ease,box-shadow .16s ease,border-color .16s ease,background .16s ease,color .16s ease}
.tfb-note-card__btn:hover,.tfb-note-card__status-option:hover{transform:translateY(-1px)}
.tfb-note-card__btn{border:0;background:linear-gradient(180deg,#3f86f8,#2f72e6);color:#fff;box-shadow:0 8px 18px rgba(47,114,230,.20)}
.tfb-note-card__btn:hover{box-shadow:0 10px 20px rgba(47,114,230,.24)}
.tfb-note-card__btn--light{border:1px solid rgba(92,102,122,.18);background:rgba(255,255,255,.86);color:#2e281e;box-shadow:inset 0 1px 0 rgba(255,255,255,.85),0 6px 14px rgba(31,36,44,.06)}
.tfb-note-card__btn--danger{background:linear-gradient(180deg,#db6556,#c84b3e);box-shadow:0 8px 18px rgba(200,75,62,.20)}
.tfb-note-card__mark{display:inline-flex;align-items:center;justify-content:center;flex:0 0 auto;min-height:30px;margin:0;padding:6px 12px;border:1px solid rgba(92,102,122,.12);border-radius:999px;background:rgba(255,255,255,.78);font-size:11px;font-weight:800;letter-spacing:.08em;text-transform:uppercase;box-shadow:inset 0 1px 0 rgba(255,255,255,.88)}
.tfb-note-card__mark--important{color:#8f382d;background:linear-gradient(180deg,rgba(255,243,241,.98),rgba(255,233,229,.95));border-color:rgba(219,101,86,.28);box-shadow:inset 0 1px 0 rgba(255,255,255,.9),0 0 0 3px rgba(219,101,86,.08)}
.tfb-note-card__mark--future{color:#7b5d17;background:linear-gradient(180deg,rgba(255,250,235,.98),rgba(255,243,206,.96));border-color:rgba(211,168,79,.30);box-shadow:inset 0 1px 0 rgba(255,255,255,.9),0 0 0 3px rgba(211,168,79,.08)}
.tfb-note-card__fab{position:absolute;right:16px;bottom:16px;display:inline-flex;align-items:center;justify-content:center;width:42px;height:42px;border:0;border-radius:50%;background:var(--tfb-accent);color:#fff;cursor:pointer;box-shadow:0 10px 22px var(--tfb-accent-glow);transition:transform .16s ease,box-shadow .16s ease,filter .16s ease}
.tfb-note-card__fab:hover{transform:translateY(-1px);box-shadow:0 12px 24px var(--tfb-accent-glow);filter:saturate(1.08) brightness(.96)}
.tfb-note-card__fab svg{display:block;width:18px;height:18px;fill:currentColor}
.tfb-note-card__field{margin:0 0 16px}
.tfb-note-card__field:last-child{margin-bottom:0}
.tfb-note-card__label{display:block;margin:0 0 8px;font-size:11px;font-weight:700;letter-spacing:.08em;text-transform:uppercase;color:#655b4f}
.tfb-note-card__status-grid{display:grid;grid-template-columns:repeat(3,var(--tfb-control-width));gap:10px;justify-content:start}
.tfb-note-card__status-option{border:1px solid rgba(92,102,122,.16);background:rgba(255,255,255,.82);color:#2d261d;box-shadow:inset 0 1px 0 rgba(255,255,255,.86)}
.tfb-note-card__status-option.is-active{border-color:var(--tfb-accent);background:linear-gradient(180deg,rgba(255,255,255,.95),var(--tfb-accent-soft));box-shadow:0 0 0 3px rgba(255,255,255,.5),0 0 0 5px var(--tfb-accent-soft)}
.tfb-note-card__status-option span{display:block}
.tfb-note-card__color-row{display:flex;flex-wrap:wrap;gap:10px;align-items:center}
.tfb-note-card__swatch{position:relative;width:32px;height:32px;padding:0;border:0;border-radius:50%;cursor:pointer;box-shadow:inset 0 0 0 2px rgba(255,255,255,.95),0 6px 12px rgba(32,37,46,.10)}
.tfb-note-card__swatch:hover{transform:translateY(-1px)}
.tfb-note-card__swatch.is-active::after{content:"";position:absolute;inset:-5px;border:2px solid #2f72e6;border-radius:50%}
.tfb-note-card__color-picker-wrap{position:relative;display:inline-flex;align-items:center;justify-content:center;width:58px;height:36px;border:1px solid rgba(92,102,122,.18);border-radius:12px;background:linear-gradient(135deg,#ff7b72 0%,#f3bf4a 22%,#53d769 48%,#2f7af2 74%,#9b6dff 100%);box-shadow:inset 0 1px 0 rgba(255,255,255,.86),0 6px 12px rgba(32,37,46,.08);overflow:hidden;cursor:pointer}
.tfb-note-card__color-picker-wrap::before{content:"";position:absolute;inset:4px;border-radius:9px;background:linear-gradient(180deg,rgba(255,255,255,.38),rgba(255,255,255,.12));pointer-events:none}
.tfb-note-card__color-picker-wrap::after{content:"";position:absolute;width:18px;height:18px;border:2px solid rgba(255,255,255,.96);border-radius:50%;background:var(--tfb-picker-value,#D3A84F);box-shadow:0 3px 8px rgba(16,20,26,.18);pointer-events:none}
.tfb-note-card__color-picker{position:absolute;inset:0;width:100%;height:100%;margin:0;padding:0;border:0;opacity:0;cursor:pointer}
.tfb-note-card__color-help{margin-top:8px;font-size:11px;line-height:1.45;color:#776e63}
@media (max-width: 768px){
    .tfb-note-card--public-float{right:10px;bottom:10px;width:calc(100% - 20px)}
    .tfb-note-root--panel{display:block;height:auto}
    .tfb-note-card--panel{position:static;min-width:0;max-width:none;margin:8px 0 0}
    .tfb-note-card--admin,.tfb-note-card--public{padding:14px}
    .tfb-note-card__head{flex-direction:column;align-items:flex-start}
    .tfb-note-card__toolbar{padding:0}
    .tfb-note-card__actions,.tfb-note-card__status-grid{grid-template-columns:1fr}
    .tfb-note-card__btn,.tfb-note-card__status-option{width:100%}
    .tfb-note-card__editor{padding:14px 0 0}
    .tfb-note-card__fab{right:14px;bottom:14px}
}
</style>
HTML;
    }

    protected static function getScript()
    {
        return <<<'HTML'
<script>
(function(){
    var config = window.TipsForBitrixNoteConfig;
    if (!config || document.getElementById('tfb-note-root')) {
        return;
    }

    function escapeHtml(text) {
        return String(text || '').replace(/[&<>"']/g, function(symbol) {
            return {
                '&': '&amp;',
                '<': '&lt;',
                '>': '&gt;',
                '"': '&quot;',
                "'": '&#039;'
            }[symbol];
        });
    }

    function isSafeHref(href) {
        href = String(href || '').trim();

        if (!href) {
            return false;
        }

        return /^(https?:\/\/|\/|#|\?|mailto:|tel:)/i.test(href);
    }

    function sanitizeNode(node) {
        var allowedTags = {
            A: true,
            B: true,
            STRONG: true,
            I: true,
            EM: true,
            BR: true,
            CODE: true
        };

        var child = node.firstChild;

        while (child) {
            var next = child.nextSibling;

            if (child.nodeType === 1) {
                if (!allowedTags[child.tagName]) {
                    while (child.firstChild) {
                        node.insertBefore(child.firstChild, child);
                    }
                    node.removeChild(child);
                    child = next;
                    continue;
                }

                if (child.tagName === 'A') {
                    var href = child.getAttribute('href') || '';

                    if (!isSafeHref(href)) {
                        child.removeAttribute('href');
                    }

                    var target = child.getAttribute('target') || '';
                    if (target && target !== '_blank') {
                        child.removeAttribute('target');
                    }

                    child.setAttribute('rel', 'noopener noreferrer');
                } else {
                    var attrs = child.attributes;
                    for (var i = attrs.length - 1; i >= 0; i--) {
                        child.removeAttribute(attrs[i].name);
                    }
                }

                sanitizeNode(child);
            } else if (child.nodeType !== 3) {
                node.removeChild(child);
            }

            child = next;
        }
    }

    function noteHtml(text) {
        var prepared = String(text || '').replace(/\r\n?/g, '\n').replace(/\n/g, '<br>');
        var wrapper = document.createElement('div');
        wrapper.innerHTML = prepared;
        sanitizeNode(wrapper);
        return wrapper.innerHTML;
    }

    function hexToRgba(hex, alpha) {
        var value = String(hex || '').replace('#', '');
        if (value.length !== 6) {
            return 'rgba(211,168,79,' + alpha + ')';
        }

        var r = parseInt(value.slice(0, 2), 16);
        var g = parseInt(value.slice(2, 4), 16);
        var b = parseInt(value.slice(4, 6), 16);

        return 'rgba(' + r + ',' + g + ',' + b + ',' + alpha + ')';
    }

    function getResolvedColor(color) {
        if (config.colorPresets[color]) {
            return config.colorPresets[color].value;
        }

        if (/^#[0-9a-fA-F]{6}$/.test(String(color || ''))) {
            return String(color).toUpperCase();
        }

        return config.colorPresets.sand.value;
    }

    function cardStyle(color) {
        var accent = getResolvedColor(color);
        return '--tfb-accent:' + accent + ';--tfb-accent-soft:' + hexToRgba(accent, 0.12) + ';--tfb-accent-glow:' + hexToRgba(accent, 0.20) + ';--tfb-accent-border:' + hexToRgba(accent, 0.24) + ';';
    }

    function renderStatusOptions(selectedStatus) {
        var html = '<div class="tfb-note-card__field"><div class="tfb-note-card__label">Статус</div><div class="tfb-note-card__status-grid">';

        Object.keys(config.statusMap).forEach(function(key) {
            html += '<button type="button" class="tfb-note-card__status-option' + (selectedStatus === key ? ' is-active' : '') + '" data-role="status-option" data-value="' + escapeHtml(key) + '"><span>' + escapeHtml(config.statusMap[key]) + '</span></button>';
        });

        html += '</div></div>';
        return html;
    }

    function renderColorOptions(selectedColor) {
        var html = '<div class="tfb-note-card__field"><div class="tfb-note-card__label">Цвет</div><div class="tfb-note-card__color-row">';

        Object.keys(config.colorPresets).forEach(function(key) {
            var preset = config.colorPresets[key];
            html += '<button type="button" class="tfb-note-card__swatch' + (selectedColor === key ? ' is-active' : '') + '" data-role="color-option" data-value="' + escapeHtml(key) + '" title="' + escapeHtml(preset.label) + '" style="background:' + escapeHtml(preset.value) + ';"></button>';
        });

        html += '<label class="tfb-note-card__color-picker-wrap" style="--tfb-picker-value:' + escapeHtml(getResolvedColor(selectedColor)) + ';" title="Свой цвет">';
        html += '<input type="color" class="tfb-note-card__color-picker" data-role="color-picker" value="' + escapeHtml(getResolvedColor(selectedColor)) + '" title="Свой цвет">';
        html += '</label>';
        html += '</div><div class="tfb-note-card__color-help">Можно выбрать один из готовых цветов или поставить свой.</div></div>';

        return html;
    }

    function renderStatusMark(noteStatus) {
        if (noteStatus === 'important') {
            return '<div class="tfb-note-card__mark tfb-note-card__mark--important">Важное</div>';
        }

        if (noteStatus === 'future') {
            return '<div class="tfb-note-card__mark tfb-note-card__mark--future">Сделать в будущем</div>';
        }

        return '';
    }

    function renderEditIcon() {
        return '<svg viewBox="0 0 24 24" aria-hidden="true"><path d="M3 17.25V21h3.75L17.81 9.94l-3.75-3.75L3 17.25zm17.71-10.04a1.003 1.003 0 0 0 0-1.42l-2.5-2.5a1.003 1.003 0 0 0-1.42 0L14.84 5.24l3.75 3.75 2.12-2.08z"/></svg>';
    }

    function encodeForm(data) {
        var pairs = [];
        Object.keys(data).forEach(function(key) {
            pairs.push(encodeURIComponent(key) + '=' + encodeURIComponent(data[key]));
        });
        return pairs.join('&');
    }

    function getBrowserUrl() {
        return window.location.pathname + window.location.search;
    }

    function request(action, text, noteStatus, noteColor, requestUrl) {
        return fetch(config.ajaxUrl, {
            method: 'POST',
            credentials: 'same-origin',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded; charset=UTF-8'
            },
            body: encodeForm({
                action: action,
                area: config.area,
                url: requestUrl || config.url,
                text: text || '',
                status: noteStatus || 'default',
                color: noteColor || 'sand',
                sessid: config.sessid
            })
        }).then(function(response) {
            return response.json();
        });
    }

    var state = {
        noteText: config.noteText || '',
        noteStatus: config.noteStatus || 'default',
        noteColor: config.noteColor || 'sand',
        draftText: config.noteText || '',
        draftStatus: config.noteStatus || 'default',
        draftColor: config.noteColor || 'sand',
        currentUrl: config.url || getBrowserUrl(),
        editing: false,
        loading: false,
        loadingNote: false,
        feedbackMessage: '',
        feedbackError: false
    };

    var root = document.createElement('span');
    root.id = 'tfb-note-root';
    root.className = 'tfb-note-root';
    var publicPanelContainer = null;
    var urlWatcherTimer = 0;
    var lastBrowserUrl = getBrowserUrl();
    var noteRequestToken = 0;

    function setFeedback(text, isError) {
        state.feedbackMessage = text || '';
        state.feedbackError = !!isError;
        render();
    }

    function syncDraftColorVisualState() {
        var card = root.querySelector('.tfb-note-card');
        var pickerWrap = root.querySelector('.tfb-note-card__color-picker-wrap');
        var picker = root.querySelector('[data-role="color-picker"]');
        var colorButtons = root.querySelectorAll('[data-role="color-option"]');
        var resolvedColor = getResolvedColor(state.draftColor);

        if (card && state.editing) {
            card.setAttribute('style', cardStyle(state.draftColor));
        }

        if (pickerWrap) {
            pickerWrap.style.setProperty('--tfb-picker-value', resolvedColor);
        }

        if (picker) {
            picker.value = resolvedColor;
        }

        colorButtons.forEach(function(button) {
            var isActive = (button.getAttribute('data-value') || '') === state.draftColor;
            button.classList.toggle('is-active', isActive);
        });
    }

    function syncDraftFromEditor() {
        var textarea = root.querySelector('[data-role="textarea"]');
        if (textarea) {
            state.draftText = textarea.value;
        }
    }

    function startEditing() {
        state.editing = true;
        state.feedbackMessage = '';
        state.draftText = state.noteText || '';
        state.draftStatus = state.noteStatus || 'default';
        state.draftColor = state.noteColor || 'sand';
        render();

        var field = root.querySelector('[data-role="textarea"]');
        if (field) {
            field.focus();
            field.setSelectionRange(field.value.length, field.value.length);
        }
    }

    function render() {
        var compactPublic = !state.editing && !state.noteText && config.area === 'public';
        var emptyState = !state.editing && !state.noteText;
        var visualStatus = state.editing ? state.draftStatus : state.noteStatus;
        var visualColor = state.editing ? state.draftColor : state.noteColor;
        var isCompact = compactPublic || emptyState;
        var publicPlacementClass = config.area === 'public' ? (publicPanelContainer ? ' tfb-note-card--panel' : ' tfb-note-card--public-float') : '';
        var statusClass = (visualStatus === 'important' || visualStatus === 'future') ? ' tfb-note-card--status-' + visualStatus : '';
        var classes = 'tfb-note-card tfb-note-card--' + config.area + publicPlacementClass + (isCompact ? ' tfb-note-card--compact' : '') + statusClass;
        var html = '';

        if (compactPublic || emptyState) {
            html += '<div class="' + classes + '" style="' + cardStyle(visualColor) + '">';
            html += '<div class="tfb-note-card__actions">';
            html += '<button type="button" class="tfb-note-card__btn" data-role="edit">+ Добавить заметку</button>';
            html += '</div>';
            html += '</div>';
            root.innerHTML = html;
            bind();
            return;
        }

        html += '<div class="' + classes + '" style="' + cardStyle(visualColor) + '">';
        html += '<div class="tfb-note-card__head"><div class="tfb-note-card__head-main">';
        html += '<div class="tfb-note-card__title-row"><div class="tfb-note-card__title">Заметка для этой страницы</div><a class="tfb-note-card__title-link" href="' + escapeHtml(config.notesListUrl) + '">Список заметок</a></div>';
        if (state.editing) {
            html += '<div class="tfb-note-card__meta">' + escapeHtml(state.currentUrl) + '</div>';
        }
        html += '</div>' + renderStatusMark(visualStatus) + '</div>';

        if (state.noteText && !state.editing) {
            html += '<div class="tfb-note-card__text tfb-note-card__text--with-fab">' + noteHtml(state.noteText) + '</div>';
            html += '<button type="button" class="tfb-note-card__fab" data-role="edit" title="Редактировать">' + renderEditIcon() + '</button>';
        }

        if (!state.noteText && !state.editing) {
            html += '<div class="tfb-note-card__toolbar"><div class="tfb-note-card__actions">';
            html += '<button type="button" class="tfb-note-card__btn" data-role="edit">+ Добавить</button>';
            html += '</div></div>';
        } else if (state.editing) {
            html += '<div class="tfb-note-card__toolbar"><div class="tfb-note-card__actions">';
            html += '<button type="button" class="tfb-note-card__btn" data-role="save">' + (state.loading ? 'Сохраняю...' : 'Сохранить') + '</button>';
            html += '<button type="button" class="tfb-note-card__btn tfb-note-card__btn--light" data-role="cancel">Отмена</button>';
            if (state.noteText) {
                html += '<button type="button" class="tfb-note-card__btn tfb-note-card__btn--danger" data-role="delete">Удалить</button>';
            }
            html += '</div></div>';
        }

        html += '<div class="tfb-note-card__editor' + (state.editing ? ' is-visible' : '') + '">';
        html += renderStatusOptions(state.draftStatus);
        html += renderColorOptions(state.draftColor);
        html += '<textarea class="tfb-note-card__textarea" data-role="textarea">' + escapeHtml(state.draftText) + '</textarea>';
        html += '</div>';

        if (state.feedbackMessage) {
            html += '<div class="tfb-note-card__feedback' + (state.feedbackError ? ' is-error' : '') + '">' + escapeHtml(state.feedbackMessage) + '</div>';
        }

        html += '</div>';
        root.innerHTML = html;
        bind();
    }

    function bind() {
        var textarea = root.querySelector('[data-role="textarea"]');
        var editButton = root.querySelector('[data-role="edit"]');
        var cancelButton = root.querySelector('[data-role="cancel"]');
        var saveButton = root.querySelector('[data-role="save"]');
        var deleteButton = root.querySelector('[data-role="delete"]');
        var statusButtons = root.querySelectorAll('[data-role="status-option"]');
        var colorButtons = root.querySelectorAll('[data-role="color-option"]');
        var colorPicker = root.querySelector('[data-role="color-picker"]');

        if (editButton) {
            editButton.addEventListener('click', startEditing);
        }

        if (cancelButton) {
            cancelButton.addEventListener('click', function() {
                syncDraftFromEditor();
                state.editing = false;
                state.feedbackMessage = '';
                render();
            });
        }

        statusButtons.forEach(function(button) {
            button.addEventListener('click', function() {
                syncDraftFromEditor();
                state.draftStatus = button.getAttribute('data-value') || 'default';
                render();
            });
        });

        colorButtons.forEach(function(button) {
            button.addEventListener('click', function() {
                syncDraftFromEditor();
                state.draftColor = button.getAttribute('data-value') || 'sand';
                render();
            });
        });

        if (colorPicker) {
            colorPicker.addEventListener('input', function() {
                syncDraftFromEditor();
                state.draftColor = colorPicker.value || '#D3A84F';
                syncDraftColorVisualState();
            });
        }

        if (saveButton && textarea) {
            saveButton.addEventListener('click', function() {
                if (state.loading) {
                    return;
                }

                state.loading = true;
                setFeedback('', false);

                state.draftText = textarea.value;
                request('save', state.draftText, state.draftStatus, state.draftColor, getBrowserUrl()).then(function(response) {
                    state.loading = false;

                    if (!response || !response.success) {
                        setFeedback((response && response.message) ? response.message : 'Не удалось сохранить заметку.', true);
                        return;
                    }

                    state.currentUrl = response.url || getBrowserUrl();
                    config.url = state.currentUrl;
                    state.noteText = response.noteText || '';
                    state.noteStatus = response.noteStatus || 'default';
                    state.noteColor = response.noteColor || 'sand';
                    state.draftText = state.noteText;
                    state.draftStatus = state.noteStatus;
                    state.draftColor = state.noteColor;
                    state.editing = false;
                    setFeedback(state.noteText ? 'Заметка сохранена.' : 'Заметка удалена.');
                }).catch(function() {
                    state.loading = false;
                    setFeedback('Ошибка при сохранении заметки.', true);
                });
            });
        }

        if (deleteButton) {
            deleteButton.addEventListener('click', function() {
                if (state.loading || !window.confirm('Удалить заметку для этой страницы?')) {
                    return;
                }

                state.loading = true;
                setFeedback('', false);

                request('delete', '', state.draftStatus, state.draftColor, getBrowserUrl()).then(function(response) {
                    state.loading = false;

                    if (!response || !response.success) {
                        setFeedback((response && response.message) ? response.message : 'Не удалось удалить заметку.', true);
                        return;
                    }

                    state.currentUrl = response.url || getBrowserUrl();
                    config.url = state.currentUrl;
                    state.noteText = '';
                    state.noteStatus = 'default';
                    state.noteColor = 'sand';
                    state.draftText = '';
                    state.draftStatus = 'default';
                    state.draftColor = 'sand';
                    state.editing = false;
                    setFeedback('Заметка удалена.');
                }).catch(function() {
                    state.loading = false;
                    setFeedback('Ошибка при удалении заметки.', true);
                });
            });
        }
    }

    function applyLoadedNote(response, requestedUrl) {
        state.currentUrl = (response && response.url) ? response.url : requestedUrl;
        config.url = state.currentUrl;
        state.noteText = (response && response.noteText) ? response.noteText : '';
        state.noteStatus = (response && response.noteStatus) ? response.noteStatus : 'default';
        state.noteColor = (response && response.noteColor) ? response.noteColor : 'sand';
        state.draftText = state.noteText;
        state.draftStatus = state.noteStatus;
        state.draftColor = state.noteColor;
        state.editing = false;
        state.feedbackMessage = '';
        state.feedbackError = false;
        render();
    }

    function loadNoteForCurrentUrl(force) {
        var browserUrl = getBrowserUrl();
        var requestToken = 0;

        if (!force && browserUrl === lastBrowserUrl) {
            return;
        }

        lastBrowserUrl = browserUrl;
        noteRequestToken++;
        requestToken = noteRequestToken;
        state.loadingNote = true;

        request('get', '', state.noteStatus, state.noteColor, browserUrl).then(function(response) {
            if (requestToken !== noteRequestToken) {
                return;
            }

            state.loadingNote = false;

            if (!response || !response.success) {
                return;
            }

            applyLoadedNote(response, browserUrl);
        }).catch(function() {
            state.loadingNote = false;
        });
    }

    function watchUrlChanges() {
        if (urlWatcherTimer) {
            return;
        }

        if (window.history && typeof window.history.pushState === 'function') {
            var originalPushState = window.history.pushState;
            window.history.pushState = function() {
                var result = originalPushState.apply(this, arguments);
                loadNoteForCurrentUrl();
                return result;
            };
        }

        if (window.history && typeof window.history.replaceState === 'function') {
            var originalReplaceState = window.history.replaceState;
            window.history.replaceState = function() {
                var result = originalReplaceState.apply(this, arguments);
                loadNoteForCurrentUrl();
                return result;
            };
        }

        window.addEventListener('popstate', function() {
            loadNoteForCurrentUrl();
        });

        urlWatcherTimer = window.setInterval(function() {
            loadNoteForCurrentUrl();
        }, 500);
    }

    function mount() {
        if (config.area === 'admin') {
            var title = document.querySelector('#adm-workarea');
            
            if (title) {
                title.prepend(root); 
            } else {
                document.body.insertBefore(root, document.body.firstChild);
            }
        } else {
            publicPanelContainer = document.getElementById('bx-panel-buttons-inner');

            if (publicPanelContainer) {
                root.className = 'tfb-note-root tfb-note-root--panel';
                publicPanelContainer.appendChild(root);
            } else {
                document.body.appendChild(root);
            }
        }

        render();
        watchUrlChanges();
        loadNoteForCurrentUrl(true);
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', mount);
    } else {
        mount();
    }
})();
</script>
HTML;
    }
}

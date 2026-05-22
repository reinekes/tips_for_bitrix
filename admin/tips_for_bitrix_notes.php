<?php

use Bitrix\Main\Loader;
use Bitrix\Main\Localization\Loc;
use Bitrix\Main\Type\DateTime;
use TipsForBitrix\NoteTable;
use TipsForBitrix\Manager;

require_once $_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/main/include/prolog_admin_before.php';

Loc::loadMessages(__FILE__);

global $APPLICATION;
global $USER;

if (!$USER->IsAdmin()) {
    $APPLICATION->AuthForm(Loc::getMessage('TFB_NOTES_ACCESS_DENIED'));
}

if (!Loader::includeModule('reineke.tipsforbitrix')) {
    require_once $_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/main/include/prolog_admin_after.php';
    echo '<div class="adm-info-message-wrap"><div class="adm-info-message">' . htmlspecialcharsbx(Loc::getMessage('TFB_NOTES_MODULE_NOT_INCLUDED')) . '</div></div>';
    require_once $_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/main/include/epilog_admin.php';
    return;
}

Manager::ensureSchema();

$lang = defined('LANGUAGE_ID') ? LANGUAGE_ID : 'ru';
$listPageUrl = 'reineke_tipsforbitrix_notes.php?lang=' . urlencode($lang);
$statusMap = Manager::getStatusMap();
$colorPresets = Manager::getColorPresets();
$areaLabels = array(
    'admin' => Loc::getMessage('TFB_NOTES_AREA_ADMIN'),
    'public' => Loc::getMessage('TFB_NOTES_AREA_PUBLIC'),
);
$editNote = null;
$editError = '';
$isUpdated = isset($_GET['updated']) && $_GET['updated'] === 'Y';

if (isset($_GET['action'], $_GET['ID']) && $_GET['action'] === 'delete' && check_bitrix_sessid()) {
    NoteTable::delete((int) $_GET['ID']);
    LocalRedirect($APPLICATION->GetCurPageParam('', array('action', 'ID', 'sessid')));
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['tfb_save_note']) && check_bitrix_sessid()) {
    $editId = (int) $_POST['ID'];
    $existingNote = NoteTable::getByPrimary($editId)->fetch();

    if (!$existingNote) {
        $editError = Loc::getMessage('TFB_NOTES_NOTE_NOT_FOUND');
    } else {
        $noteText = trim((string) $_POST['NOTE_TEXT']);
        $noteStatus = Manager::normalizeStatus(isset($_POST['STATUS']) ? (string) $_POST['STATUS'] : 'default');
        $colorPreset = isset($_POST['COLOR_PRESET']) ? (string) $_POST['COLOR_PRESET'] : 'sand';
        $colorCustom = isset($_POST['COLOR_CUSTOM']) ? (string) $_POST['COLOR_CUSTOM'] : '#D3A84F';
        $noteColor = ($colorPreset === '__custom__') ? $colorCustom : $colorPreset;
        $noteColor = Manager::normalizeColor($noteColor);

        if ($noteText === '') {
            $editError = Loc::getMessage('TFB_NOTES_TEXT_REQUIRED');
        } else {
            $result = NoteTable::update(
                $editId,
                array(
                    'NOTE_TEXT' => $noteText,
                    'STATUS' => $noteStatus,
                    'COLOR' => $noteColor,
                    'TIMESTAMP_X' => new DateTime(),
                )
            );

            if ($result->isSuccess()) {
                LocalRedirect($APPLICATION->GetCurPageParam('updated=Y', array('action', 'ID', 'updated', 'sessid')));
            }

            $editError = implode("\n", $result->getErrorMessages());
        }

        $editNote = $existingNote;
        $editNote['NOTE_TEXT'] = $noteText;
        $editNote['STATUS'] = $noteStatus;
        $editNote['COLOR'] = $noteColor;
    }
}

if (!$editNote && isset($_GET['action'], $_GET['ID']) && $_GET['action'] === 'edit') {
    $editNote = NoteTable::getByPrimary((int) $_GET['ID'])->fetch();

    if (!$editNote) {
        $editError = Loc::getMessage('TFB_NOTES_NOTE_NOT_FOUND');
    }
}

$notes = NoteTable::getList(
    array(
        'order' => array(
            'TIMESTAMP_X' => 'DESC',
            'ID' => 'DESC',
        ),
    )
)->fetchAll();

$APPLICATION->SetTitle(Loc::getMessage('TFB_NOTES_TITLE'));

require_once $_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/main/include/prolog_admin_after.php';
?>
<style>
.tfb-note-list-summary{display:flex;flex-wrap:wrap;gap:12px;margin:0 0 18px}
.tfb-note-list-summary__item{min-width:160px;padding:12px 14px;border:1px solid #d7e3ea;border-radius:12px;background:#fff;box-shadow:0 1px 2px rgba(31,36,44,.04)}
.tfb-note-list-summary__value{display:block;font-size:22px;font-weight:700;line-height:1.1;color:#1f2b38}
.tfb-note-list-summary__label{display:block;margin-top:4px;font-size:12px;color:#6b7683}
.tfb-note-list{width:100%;table-layout:fixed}
.tfb-note-list .adm-list-table-cell{vertical-align:top}
.tfb-note-list__badge{display:inline-flex;align-items:center;min-height:24px;padding:0 10px;border-radius:999px;font-size:12px;font-weight:600;line-height:1.2;white-space:nowrap}
.tfb-note-list__badge--area-admin{background:#eef5ff;color:#2f72e6}
.tfb-note-list__badge--area-public{background:#eef9f2;color:#2f8a57}
.tfb-note-list__badge--status-default{background:#f3f5f7;color:#4f5d6b}
.tfb-note-list__badge--status-important{background:#fff0ee;color:#c84b3e}
.tfb-note-list__badge--status-future{background:#fff6df;color:#9a6a12}
.tfb-note-list__color{display:inline-flex;align-items:center;gap:8px}
.tfb-note-list__swatch{display:inline-block;width:16px;height:16px;border-radius:50%;box-shadow:inset 0 0 0 2px rgba(255,255,255,.9),0 0 0 1px rgba(92,102,122,.18)}
.tfb-note-list__url,.tfb-note-list__text{display:block;overflow-wrap:anywhere;word-break:break-word}
.tfb-note-list__url{color:#2f72e6;text-decoration:none}
.tfb-note-list__url:hover{text-decoration:underline}
.tfb-note-list__text{line-height:1.45;color:#27313c}
.tfb-note-list__date{white-space:nowrap;line-height:1.35}
.tfb-note-list__actions a{white-space:nowrap}
.tfb-note-edit{margin:0 0 18px;padding:18px;border:1px solid #d7e3ea;border-radius:12px;background:#fff;box-shadow:0 1px 2px rgba(31,36,44,.04)}
.tfb-note-edit__title{margin:0 0 14px;font-size:18px;font-weight:700;color:#1f2b38}
.tfb-note-edit__meta{display:grid;grid-template-columns:140px 1fr;gap:8px 14px;margin:0 0 16px}
.tfb-note-edit__meta-label{color:#6b7683}
.tfb-note-edit__meta-value{color:#1f2b38}
.tfb-note-edit__meta-value a{color:#2f72e6;text-decoration:none}
.tfb-note-edit__meta-value a:hover{text-decoration:underline}
.tfb-note-edit__field{margin:0 0 16px}
.tfb-note-edit__label{display:block;margin:0 0 6px;font-size:13px;font-weight:700;color:#27313c}
.tfb-note-edit__textarea{display:block;width:100%;min-height:180px;padding:12px 14px;border:1px solid #c9d5e0;border-radius:10px;background:#fff;font:14px/1.5 Consolas,Monaco,monospace;color:#1f2b38;resize:vertical}
.tfb-note-edit__select,.tfb-note-edit__color{height:38px;padding:0 12px;border:1px solid #c9d5e0;border-radius:10px;background:#fff;color:#1f2b38}
.tfb-note-edit__row{display:flex;flex-wrap:wrap;gap:12px;align-items:end}
.tfb-note-edit__actions{display:flex;gap:10px;align-items:center}
</style>
<div class="adm-info-message-wrap">
    <div class="adm-info-message">
        <?php echo htmlspecialcharsbx(Loc::getMessage('TFB_NOTES_INFO')); ?>
    </div>
</div>

<?php if ($isUpdated): ?>
    <?php echo CAdminMessage::ShowNote(Loc::getMessage('TFB_NOTES_UPDATED')); ?>
<?php endif; ?>

<?php if ($editError !== ''): ?>
    <?php echo CAdminMessage::ShowMessage($editError); ?>
<?php endif; ?>

<?php if ($editNote): ?>
    <?php
    $editColor = (string) ($editNote['COLOR'] ?: 'sand');
    $editColorPreset = isset($colorPresets[$editColor]) ? $editColor : '__custom__';
    $editColorCustom = isset($colorPresets[$editColor]) ? $colorPresets[$editColor]['value'] : Manager::resolveColorValue($editColor);
    ?>
    <div class="tfb-note-edit">
        <h2 class="tfb-note-edit__title"><?php echo htmlspecialcharsbx(Loc::getMessage('TFB_NOTES_EDIT_TITLE', array('#ID#' => (int) $editNote['ID']))); ?></h2>
        <div class="tfb-note-edit__meta">
            <div class="tfb-note-edit__meta-label"><?php echo htmlspecialcharsbx(Loc::getMessage('TFB_NOTES_AREA_LABEL')); ?></div>
            <div class="tfb-note-edit__meta-value"><?php echo htmlspecialcharsbx($areaLabels[$editNote['AREA'] === 'admin' ? 'admin' : 'public']); ?></div>
            <div class="tfb-note-edit__meta-label"><?php echo htmlspecialcharsbx(Loc::getMessage('TFB_NOTES_PAGE_LABEL')); ?></div>
            <div class="tfb-note-edit__meta-value">
                <a href="<?php echo htmlspecialcharsbx((string) $editNote['PAGE_URL']); ?>" target="_blank"><?php echo htmlspecialcharsbx((string) $editNote['PAGE_URL']); ?></a>
            </div>
        </div>
        <form method="post" action="<?php echo htmlspecialcharsbx($listPageUrl); ?>">
            <?php echo bitrix_sessid_post(); ?>
            <input type="hidden" name="ID" value="<?php echo (int) $editNote['ID']; ?>">
            <div class="tfb-note-edit__row">
                <div class="tfb-note-edit__field">
                    <label class="tfb-note-edit__label" for="tfb-status"><?php echo htmlspecialcharsbx(Loc::getMessage('TFB_NOTES_STATUS_LABEL')); ?></label>
                    <select class="tfb-note-edit__select" id="tfb-status" name="STATUS">
                        <?php foreach ($statusMap as $statusKey => $statusLabel): ?>
                            <option value="<?php echo htmlspecialcharsbx($statusKey); ?>"<?php echo ((string) $editNote['STATUS'] === (string) $statusKey ? ' selected' : ''); ?>>
                                <?php echo htmlspecialcharsbx($statusLabel); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="tfb-note-edit__field">
                    <label class="tfb-note-edit__label" for="tfb-color-preset"><?php echo htmlspecialcharsbx(Loc::getMessage('TFB_NOTES_COLOR_LABEL')); ?></label>
                    <select class="tfb-note-edit__select" id="tfb-color-preset" name="COLOR_PRESET">
                        <?php foreach ($colorPresets as $presetKey => $preset): ?>
                            <option value="<?php echo htmlspecialcharsbx($presetKey); ?>" data-color="<?php echo htmlspecialcharsbx($preset['value']); ?>"<?php echo ($editColorPreset === $presetKey ? ' selected' : ''); ?>>
                                <?php echo htmlspecialcharsbx($preset['label']); ?>
                            </option>
                        <?php endforeach; ?>
                        <option value="__custom__"<?php echo ($editColorPreset === '__custom__' ? ' selected' : ''); ?>><?php echo htmlspecialcharsbx(Loc::getMessage('TFB_NOTES_CUSTOM_COLOR_OPTION')); ?></option>
                    </select>
                </div>
                <div class="tfb-note-edit__field">
                    <label class="tfb-note-edit__label" for="tfb-color-custom"><?php echo htmlspecialcharsbx(Loc::getMessage('TFB_NOTES_CUSTOM_COLOR_LABEL')); ?></label>
                    <input class="tfb-note-edit__color" id="tfb-color-custom" type="color" name="COLOR_CUSTOM" value="<?php echo htmlspecialcharsbx($editColorCustom); ?>">
                </div>
            </div>
            <div class="tfb-note-edit__field">
                <label class="tfb-note-edit__label" for="tfb-note-text"><?php echo htmlspecialcharsbx(Loc::getMessage('TFB_NOTES_TEXT_LABEL')); ?></label>
                <textarea class="tfb-note-edit__textarea" id="tfb-note-text" name="NOTE_TEXT"><?php echo htmlspecialcharsbx((string) $editNote['NOTE_TEXT']); ?></textarea>
            </div>
            <div class="tfb-note-edit__actions">
                <input class="adm-btn-save" type="submit" name="tfb_save_note" value="<?php echo htmlspecialcharsbx(Loc::getMessage('TFB_NOTES_SAVE_BUTTON')); ?>">
                <a class="adm-btn" href="<?php echo htmlspecialcharsbx($listPageUrl); ?>"><?php echo htmlspecialcharsbx(Loc::getMessage('TFB_NOTES_CANCEL_BUTTON')); ?></a>
            </div>
        </form>
        <script>
        (function() {
            var preset = document.getElementById('tfb-color-preset');
            var custom = document.getElementById('tfb-color-custom');

            if (!preset || !custom) {
                return;
            }

            preset.addEventListener('change', function() {
                var option = preset.options[preset.selectedIndex];
                var presetColor = option ? option.getAttribute('data-color') : '';

                if (preset.value !== '__custom__' && presetColor) {
                    custom.value = presetColor;
                }
            });

            custom.addEventListener('input', function() {
                preset.value = '__custom__';
            });
        })();
        </script>
    </div>
<?php endif; ?>

<div class="tfb-note-list-summary">
    <div class="tfb-note-list-summary__item">
        <span class="tfb-note-list-summary__value"><?php echo count($notes); ?></span>
        <span class="tfb-note-list-summary__label"><?php echo htmlspecialcharsbx(Loc::getMessage('TFB_NOTES_TOTAL_LABEL')); ?></span>
    </div>
</div>

<table class="adm-list-table tfb-note-list">
    <colgroup>
        <col style="width:56px;">
        <col style="width:116px;">
        <col style="width:126px;">
        <col style="width:108px;">
        <col style="width:40%;">
        <col style="width:38%;">
        <col style="width:120px;">
        <col style="width:88px;">
    </colgroup>
    <thead>
        <tr class="adm-list-table-header">
            <td class="adm-list-table-cell"><?php echo htmlspecialcharsbx(Loc::getMessage('TFB_NOTES_HEADER_ID')); ?></td>
            <td class="adm-list-table-cell"><?php echo htmlspecialcharsbx(Loc::getMessage('TFB_NOTES_HEADER_AREA')); ?></td>
            <td class="adm-list-table-cell"><?php echo htmlspecialcharsbx(Loc::getMessage('TFB_NOTES_HEADER_STATUS')); ?></td>
            <td class="adm-list-table-cell"><?php echo htmlspecialcharsbx(Loc::getMessage('TFB_NOTES_HEADER_COLOR')); ?></td>
            <td class="adm-list-table-cell"><?php echo htmlspecialcharsbx(Loc::getMessage('TFB_NOTES_HEADER_URL')); ?></td>
            <td class="adm-list-table-cell"><?php echo htmlspecialcharsbx(Loc::getMessage('TFB_NOTES_HEADER_TEXT')); ?></td>
            <td class="adm-list-table-cell"><?php echo htmlspecialcharsbx(Loc::getMessage('TFB_NOTES_HEADER_UPDATED')); ?></td>
            <td class="adm-list-table-cell"><?php echo htmlspecialcharsbx(Loc::getMessage('TFB_NOTES_HEADER_ACTIONS')); ?></td>
        </tr>
    </thead>
    <tbody>
    <?php if (empty($notes)): ?>
        <tr class="adm-list-table-row">
            <td class="adm-list-table-cell" colspan="8"><?php echo htmlspecialcharsbx(Loc::getMessage('TFB_NOTES_EMPTY')); ?></td>
        </tr>
    <?php else: ?>
        <?php foreach ($notes as $note): ?>
            <?php
            $preview = mb_substr(trim((string) $note['NOTE_TEXT']), 0, 180);
            if (mb_strlen((string) $note['NOTE_TEXT']) > 180) {
                $preview .= '...';
            }

            $pageUrl = (string) $note['PAGE_URL'];
            $statusKey = (string) ($note['STATUS'] ?: 'default');
            $areaLabel = $areaLabels[$note['AREA'] === 'admin' ? 'admin' : 'public'];
            $areaClass = ($note['AREA'] === 'admin') ? 'admin' : 'public';
            $statusLabel = isset($statusMap[$statusKey]) ? $statusMap[$statusKey] : $statusKey;
            ?>
            <tr class="adm-list-table-row">
                <td class="adm-list-table-cell"><?php echo (int) $note['ID']; ?></td>
                <td class="adm-list-table-cell">
                    <span class="tfb-note-list__badge tfb-note-list__badge--area-<?php echo htmlspecialcharsbx($areaClass); ?>">
                        <?php echo htmlspecialcharsbx($areaLabel); ?>
                    </span>
                </td>
                <td class="adm-list-table-cell">
                    <span class="tfb-note-list__badge tfb-note-list__badge--status-<?php echo htmlspecialcharsbx($statusKey); ?>">
                        <?php echo htmlspecialcharsbx($statusLabel); ?>
                    </span>
                </td>
                <td class="adm-list-table-cell">
                    <span class="tfb-note-list__color">
                        <span class="tfb-note-list__swatch" style="background:<?php echo htmlspecialcharsbx(Manager::resolveColorValue((string) ($note['COLOR'] ?: 'sand'))); ?>;"></span>
                        <span><?php echo htmlspecialcharsbx((string) ($note['COLOR'] ?: 'sand')); ?></span>
                    </span>
                </td>
                <td class="adm-list-table-cell">
                    <a class="tfb-note-list__url" href="<?php echo htmlspecialcharsbx($pageUrl); ?>" target="_blank"><?php echo htmlspecialcharsbx($pageUrl); ?></a>
                </td>
                <td class="adm-list-table-cell"><span class="tfb-note-list__text"><?php echo htmlspecialcharsbx($preview); ?></span></td>
                <td class="adm-list-table-cell"><span class="tfb-note-list__date"><?php echo htmlspecialcharsbx((string) $note['TIMESTAMP_X']); ?></span></td>
                <td class="adm-list-table-cell tfb-note-list__actions">
                    <a href="<?php echo htmlspecialcharsbx($pageUrl); ?>" target="_blank"><?php echo htmlspecialcharsbx(Loc::getMessage('TFB_NOTES_ACTION_OPEN')); ?></a>
                    |
                    <a href="<?php echo htmlspecialcharsbx($APPLICATION->GetCurPageParam('action=edit&ID=' . (int) $note['ID'], array('action', 'ID', 'updated'))); ?>"><?php echo htmlspecialcharsbx(Loc::getMessage('TFB_NOTES_ACTION_EDIT')); ?></a>
                    |
                    <a
                        href="<?php echo htmlspecialcharsbx($APPLICATION->GetCurPageParam('action=delete&ID=' . (int) $note['ID'] . '&' . bitrix_sessid_get(), array('action', 'ID', 'sessid'))); ?>"
                        onclick="return confirm('<?php echo CUtil::JSEscape(Loc::getMessage('TFB_NOTES_DELETE_CONFIRM')); ?>');"
                    ><?php echo htmlspecialcharsbx(Loc::getMessage('TFB_NOTES_ACTION_DELETE')); ?></a>
                </td>
            </tr>
        <?php endforeach; ?>
    <?php endif; ?>
    </tbody>
</table>

<?php
require_once $_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/main/include/epilog_admin.php';

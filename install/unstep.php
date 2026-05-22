<?php

use Bitrix\Main\Localization\Loc;

if (!check_bitrix_sessid()) {
    return;
}

Loc::loadMessages(__FILE__);

global $APPLICATION;

$step = isset($_REQUEST['step']) ? (int) $_REQUEST['step'] : 1;
$lang = defined('LANGUAGE_ID') ? LANGUAGE_ID : 'ru';

if ($step < 2) {
    ?>
    <form action="<?php echo htmlspecialcharsbx($APPLICATION->GetCurPage()); ?>">
        <?php echo bitrix_sessid_post(); ?>
        <input type="hidden" name="lang" value="<?php echo htmlspecialcharsbx($lang); ?>">
        <input type="hidden" name="id" value="reineke.tipsforbitrix">
        <input type="hidden" name="uninstall" value="Y">
        <input type="hidden" name="step" value="2">

        <?php echo BeginNote(); ?>
        <p><?php echo htmlspecialcharsbx(Loc::getMessage('TFB_UNINSTALL_CONFIRM')); ?></p>
        <label>
            <input type="checkbox" name="savedata" value="Y" checked>
            <?php echo htmlspecialcharsbx(Loc::getMessage('TFB_UNINSTALL_SAVE_DATA')); ?>
        </label>
        <?php echo EndNote(); ?>

        <input class="adm-btn-save" type="submit" value="<?php echo htmlspecialcharsbx(Loc::getMessage('TFB_UNINSTALL_BUTTON')); ?>">
    </form>
    <?php

    return;
}

echo CAdminMessage::ShowNote(Loc::getMessage('TFB_UNINSTALL_SUCCESS'));

if (isset($_REQUEST['savedata']) && $_REQUEST['savedata'] === 'Y') {
    echo BeginNote();
    echo '<p>' . htmlspecialcharsbx(Loc::getMessage('TFB_UNINSTALL_DATA_SAVED')) . '</p>';
    echo EndNote();
}

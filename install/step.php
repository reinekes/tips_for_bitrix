<?php

use Bitrix\Main\Localization\Loc;

if (!check_bitrix_sessid()) {
    return;
}

Loc::loadMessages(__FILE__);

$lang = defined('LANGUAGE_ID') ? LANGUAGE_ID : 'ru';
$modulesUrl = '/bitrix/admin/partner_modules.php?lang=' . urlencode($lang);
$listUrl = '/bitrix/admin/reineke_tipsforbitrix_notes.php?lang=' . urlencode($lang);
?>
<?php echo BeginNote(); ?>
<p><?php echo htmlspecialcharsbx(Loc::getMessage('TFB_INSTALL_SUCCESS')); ?></p>
<p><a href="<?php echo htmlspecialcharsbx($modulesUrl); ?>"><?php echo htmlspecialcharsbx(Loc::getMessage('TFB_INSTALL_LINK_MODULES')); ?></a></p>
<p><a href="<?php echo htmlspecialcharsbx($listUrl); ?>"><?php echo htmlspecialcharsbx(Loc::getMessage('TFB_INSTALL_LINK_LIST')); ?></a></p>
<?php echo EndNote(); ?>

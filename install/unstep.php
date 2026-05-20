<?php

use Bitrix\Main\Localization\Loc;

if (!check_bitrix_sessid()) {
    return;
}

Loc::loadMessages(__FILE__);

echo CAdminMessage::ShowNote(Loc::getMessage('TFB_UNINSTALL_SUCCESS'));

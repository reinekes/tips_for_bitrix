<?php

use Bitrix\Main\Localization\Loc;

Loc::loadMessages(__FILE__);

if (!$USER->IsAdmin()) {
    return false;
}

return array(
    'parent_menu' => 'global_menu_services',
    'section' => 'reineke_tipsforbitrix',
    'sort' => 900,
    'text' => Loc::getMessage('TFB_MENU_TITLE'),
    'title' => Loc::getMessage('TFB_MENU_TITLE'),
    'icon' => 'default_menu_icon',
    'page_icon' => 'default_menu_icon',
    'items_id' => 'menu_reineke_tipsforbitrix',
    'items' => array(
        array(
            'text' => Loc::getMessage('TFB_MENU_NOTES'),
            'url' => 'reineke_tipsforbitrix_notes.php?lang=' . LANGUAGE_ID,
            'more_url' => array('reineke_tipsforbitrix_notes.php'),
            'title' => Loc::getMessage('TFB_MENU_NOTES'),
        ),
    ),
);

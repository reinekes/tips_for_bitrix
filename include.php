<?php

use Bitrix\Main\Loader;

Loader::registerAutoLoadClasses(
    'reineke.tipsforbitrix',
    array(
        'TipsForBitrix\\NoteTable' => 'lib/notetable.php',
        'TipsForBitrix\\Manager' => 'lib/manager.php',
        'TipsForBitrix\\Renderer' => 'lib/renderer.php',
    )
);

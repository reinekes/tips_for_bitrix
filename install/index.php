<?php

use Bitrix\Main\Application;
use Bitrix\Main\EventManager;
use Bitrix\Main\Localization\Loc;
use Bitrix\Main\ModuleManager;

Loc::loadMessages(__FILE__);

class reineke_tipsforbitrix extends CModule
{
    public $MODULE_ID = 'reineke.tipsforbitrix';
    public $MODULE_VERSION;
    public $MODULE_VERSION_DATE;
    public $MODULE_NAME;
    public $MODULE_DESCRIPTION;
    public $PARTNER_NAME;
    public $PARTNER_URI;
    public $MODULE_GROUP_RIGHTS = 'Y';

    public function __construct()
    {
        $arModuleVersion = array();

        include __DIR__ . '/version.php';

        if (is_array($arModuleVersion) && isset($arModuleVersion['VERSION'])) {
            $this->MODULE_VERSION = $arModuleVersion['VERSION'];
            $this->MODULE_VERSION_DATE = $arModuleVersion['VERSION_DATE'];
        }

        $this->MODULE_NAME = Loc::getMessage('TFB_MODULE_NAME');
        $this->MODULE_DESCRIPTION = Loc::getMessage('TFB_MODULE_DESCRIPTION');
        $this->PARTNER_NAME = Loc::getMessage('TFB_PARTNER_NAME');
        $this->PARTNER_URI = Loc::getMessage('TFB_PARTNER_URI');
    }

    public function DoInstall()
    {
        $this->installDB();
        $this->installFiles();

        ModuleManager::registerModule($this->MODULE_ID);
        $this->installEvents();

        global $APPLICATION;

        if (is_object($APPLICATION)) {
            $APPLICATION->IncludeAdminFile(
                Loc::getMessage('TFB_INSTALL_TITLE'),
                __DIR__ . '/step.php'
            );
        }
    }

    public function DoUninstall()
    {
        global $APPLICATION;
        $step = isset($_REQUEST['step']) ? (int) $_REQUEST['step'] : 1;

        if ($step < 2) {
            if (is_object($APPLICATION)) {
                $APPLICATION->IncludeAdminFile(
                    Loc::getMessage('TFB_UNINSTALL_TITLE'),
                    __DIR__ . '/unstep.php'
                );
            }

            return;
        }

        $saveData = isset($_REQUEST['savedata']) && $_REQUEST['savedata'] === 'Y';

        $this->unInstallEvents();
        $this->unInstallFiles();
        $this->unInstallDB(!$saveData);
        ModuleManager::unRegisterModule($this->MODULE_ID);

        if (is_object($APPLICATION)) {
            $APPLICATION->IncludeAdminFile(
                Loc::getMessage('TFB_UNINSTALL_TITLE'),
                __DIR__ . '/unstep.php'
            );
        }
    }

    public function installDB()
    {
        $connection = Application::getConnection();
        $tableName = 'b_tips_for_bitrix_note';

        if ($connection->isTableExists($tableName)) {
            $columnsToAdd = array(
                'STATUS' => "ALTER TABLE {$tableName} ADD STATUS varchar(32) NOT NULL DEFAULT 'default' AFTER NOTE_TEXT",
                'COLOR' => "ALTER TABLE {$tableName} ADD COLOR varchar(32) NOT NULL DEFAULT 'sand' AFTER STATUS",
            );

            foreach ($columnsToAdd as $columnName => $sql) {
                $column = $connection
                    ->query("SHOW COLUMNS FROM {$tableName} LIKE '" . $connection->getSqlHelper()->forSql($columnName) . "'")
                    ->fetch();

                if (!$column) {
                    $connection->queryExecute($sql);
                }
            }

            return;
        }

        $connection->queryExecute(
            "CREATE TABLE {$tableName} (
                ID int(11) NOT NULL AUTO_INCREMENT,
                ACTIVE char(1) NOT NULL DEFAULT 'Y',
                AREA varchar(10) NOT NULL,
                PAGE_URL varchar(255) NOT NULL,
                NOTE_TEXT longtext NOT NULL,
                STATUS varchar(32) NOT NULL DEFAULT 'default',
                COLOR varchar(32) NOT NULL DEFAULT 'sand',
                SORT int(11) NOT NULL DEFAULT 500,
                CREATED_BY int(11) NULL,
                TIMESTAMP_X datetime NOT NULL,
                DATE_CREATE datetime NOT NULL,
                PRIMARY KEY (ID),
                UNIQUE KEY ux_tips_for_bitrix_note_page (AREA, PAGE_URL),
                KEY ix_tips_for_bitrix_note_area (AREA),
                KEY ix_tips_for_bitrix_note_created_by (CREATED_BY)
            )"
        );
    }

    public function unInstallDB($dropData = true)
    {
        if (!$dropData) {
            return;
        }

        $connection = Application::getConnection();

        if ($connection->isTableExists('b_tips_for_bitrix_note')) {
            $connection->dropTable('b_tips_for_bitrix_note');
        }
    }

    public function installFiles()
    {
        CopyDirFiles(
            __DIR__ . '/admin',
            $_SERVER['DOCUMENT_ROOT'] . '/bitrix/admin',
            true,
            true
        );
    }

    public function unInstallFiles()
    {
        DeleteDirFiles(
            __DIR__ . '/admin',
            $_SERVER['DOCUMENT_ROOT'] . '/bitrix/admin'
        );
    }

    public function installEvents()
    {
        $eventManager = EventManager::getInstance();

        $eventManager->registerEventHandler(
            'main',
            'OnProlog',
            $this->MODULE_ID,
            '\\TipsForBitrix\\Renderer',
            'onProlog'
        );
    }

    public function unInstallEvents()
    {
        $eventManager = EventManager::getInstance();

        $eventManager->unRegisterEventHandler(
            'main',
            'OnProlog',
            $this->MODULE_ID,
            '\\TipsForBitrix\\Renderer',
            'onProlog'
        );
    }

    public function GetModuleRightList()
    {
        return array(
            'reference_id' => array('D', 'W'),
            'reference' => array(
                '[D] ' . Loc::getMessage('TFB_RIGHT_DENY'),
                '[W] ' . Loc::getMessage('TFB_RIGHT_WRITE'),
            ),
        );
    }
}

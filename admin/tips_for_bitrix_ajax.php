<?php

use Bitrix\Main\Loader;
use Bitrix\Main\Localization\Loc;
use Bitrix\Main\Web\Json;
use TipsForBitrix\Manager;

require_once $_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/main/include/prolog_before.php';

Loc::loadMessages(__FILE__);

global $USER;

header('Content-Type: application/json; charset=' . LANG_CHARSET);

$result = array(
    'success' => false,
);

try {
    if (!Loader::includeModule('reineke.tipsforbitrix')) {
        throw new RuntimeException(Loc::getMessage('TFB_AJAX_MODULE_NOT_INCLUDED'));
    }

    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        throw new RuntimeException(Loc::getMessage('TFB_AJAX_BAD_REQUEST_METHOD'));
    }

    if (!Manager::canManageNotes()) {
        throw new RuntimeException(Loc::getMessage('TFB_AJAX_ACCESS_DENIED'));
    }

    if (!check_bitrix_sessid()) {
        throw new RuntimeException(Loc::getMessage('TFB_AJAX_SESSION_EXPIRED'));
    }

    $action = isset($_POST['action']) ? (string) $_POST['action'] : '';
    $area = isset($_POST['area']) ? (string) $_POST['area'] : 'public';
    $url = isset($_POST['url']) ? (string) $_POST['url'] : '/';
    $text = isset($_POST['text']) ? (string) $_POST['text'] : '';
    $noteStatus = isset($_POST['status']) ? (string) $_POST['status'] : 'default';
    $noteColor = isset($_POST['color']) ? (string) $_POST['color'] : 'sand';
    $userId = (int) $USER->GetID();

    if ($action === 'get') {
        $note = Manager::getNote($area, $url);

        $result['success'] = true;
        $result['url'] = Manager::normalizeUrl($url, $area);
        $result['noteText'] = $note ? (string) $note['NOTE_TEXT'] : '';
        $result['noteStatus'] = $note ? (string) $note['STATUS'] : 'default';
        $result['noteColor'] = $note ? (string) $note['COLOR'] : 'sand';
    } elseif ($action === 'save') {
        $note = Manager::saveNote($area, $url, $text, $userId, $noteStatus, $noteColor);

        $result['success'] = true;
        $result['url'] = Manager::normalizeUrl($url, $area);
        $result['noteText'] = $note ? (string) $note['NOTE_TEXT'] : '';
        $result['noteStatus'] = $note ? (string) $note['STATUS'] : 'default';
        $result['noteColor'] = $note ? (string) $note['COLOR'] : 'sand';
    } elseif ($action === 'delete') {
        Manager::deleteNote($area, $url);

        $result['success'] = true;
        $result['url'] = Manager::normalizeUrl($url, $area);
        $result['noteText'] = '';
        $result['noteStatus'] = 'default';
        $result['noteColor'] = 'sand';
    } else {
        throw new RuntimeException(Loc::getMessage('TFB_AJAX_UNKNOWN_ACTION'));
    }
} catch (Throwable $exception) {
    $result['message'] = $exception->getMessage();
}

echo Json::encode($result);
die();

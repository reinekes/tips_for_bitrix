<?php

$moduleAdminFile = null;
$moduleAdminCandidates = array(
    $_SERVER['DOCUMENT_ROOT'] . '/local/modules/reineke.tipsforbitrix/admin/tips_for_bitrix_ajax.php',
    $_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/reineke.tipsforbitrix/admin/tips_for_bitrix_ajax.php',
);

foreach ($moduleAdminCandidates as $candidate) {
    if (is_file($candidate)) {
        $moduleAdminFile = $candidate;
        break;
    }
}

if ($moduleAdminFile === null) {
    http_response_code(500);
    echo 'Не найден AJAX-файл модуля reineke.tipsforbitrix.';
    return;
}

require_once $moduleAdminFile;

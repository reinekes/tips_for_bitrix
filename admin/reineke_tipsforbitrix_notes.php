<?php

$moduleAdminFile = null;
$moduleAdminCandidates = array(
    $_SERVER['DOCUMENT_ROOT'] . '/local/modules/reineke.tipsforbitrix/admin/tips_for_bitrix_notes.php',
    $_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/reineke.tipsforbitrix/admin/tips_for_bitrix_notes.php',
);

foreach ($moduleAdminCandidates as $candidate) {
    if (is_file($candidate)) {
        $moduleAdminFile = $candidate;
        break;
    }
}

if ($moduleAdminFile === null) {
    http_response_code(500);
    echo 'Module admin page file for reineke.tipsforbitrix was not found.';
    return;
}

require_once $moduleAdminFile;

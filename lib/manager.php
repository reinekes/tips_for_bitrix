<?php

namespace TipsForBitrix;

use Bitrix\Main\Application;
use Bitrix\Main\Type\DateTime;

class Manager
{
    protected static $schemaChecked = false;
    protected static $ignoredQueryParams = array(
        'lang',
        'sessid',
        'bxajaxid',
        'clear_cache',
        'clear_cache_session',
        'IFRAME',
        'IFRAME_TYPE',
        'internal',
        'grid_action',
    );
    protected static $ignoredQueryParamPatterns = array(
        '/^PAGEN_\d+$/i',
        '/^SIZEN_\d+$/i',
        '/^PSIZEN_\d+$/i',
    );
    protected static $defaultQueryParamValues = array(
        'path' => array('/'),
        'show_perms_for' => array('0'),
    );
    protected static $statusMap = array(
        'default' => 'Просто заметка',
        'important' => 'ВАЖНОЕ',
        'future' => 'Сделать в будущем',
    );
    protected static $colorPresets = array(
        'sand' => array('label' => 'Песочный', 'value' => '#D3A84F'),
        'sky' => array('label' => 'Синий', 'value' => '#2F7AF2'),
        'mint' => array('label' => 'Мятный', 'value' => '#2F9E73'),
        'rose' => array('label' => 'Розовый', 'value' => '#D45A7A'),
        'violet' => array('label' => 'Сиреневый', 'value' => '#7B61C9'),
        'graphite' => array('label' => 'Графит', 'value' => '#5C667A'),
    );

    public static function canManageNotes()
    {
        global $USER;

        return is_object($USER) && $USER->IsAuthorized() && $USER->IsAdmin();
    }

    public static function ensureSchema()
    {
        if (self::$schemaChecked) {
            return;
        }

        self::$schemaChecked = true;

        $connection = Application::getConnection();
        $tableName = NoteTable::getTableName();

        if (!$connection->isTableExists($tableName)) {
            return;
        }

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
    }

    public static function getCurrentArea()
    {
        return (defined('ADMIN_SECTION') && ADMIN_SECTION === true) ? 'admin' : 'public';
    }

    public static function getCurrentUrl($area = null)
    {
        if ($area === null) {
            $area = self::getCurrentArea();
        }

        $requestUri = isset($_SERVER['REQUEST_URI']) ? (string) $_SERVER['REQUEST_URI'] : '/';

        return self::normalizeUrl($requestUri, $area);
    }

    public static function normalizeUrl($url, $area = 'public')
    {
        $path = (string) parse_url((string) $url, PHP_URL_PATH);
        $query = (string) parse_url((string) $url, PHP_URL_QUERY);
        $path = trim($path);

        if ($path === '') {
            $path = '/';
        }

        $path = '/' . ltrim($path, '/');

        if ($area !== 'admin' && substr($path, -10) === '/index.php') {
            $path = substr($path, 0, -10);
        }

        if ($path !== '/') {
            $path = rtrim($path, '/');
        }

        $path = ($path === '' ? '/' : $path);
        $normalizedQuery = self::normalizeQueryString($query, $area);

        if ($normalizedQuery !== '') {
            $path .= '?' . $normalizedQuery;
        }

        return $path;
    }

    public static function getNote($area, $url)
    {
        self::ensureSchema();

        $area = ($area === 'admin') ? 'admin' : 'public';
        $normalizedUrl = self::normalizeUrl($url, $area);
        $note = NoteTable::getList(
            array(
                'filter' => array(
                    '=ACTIVE' => 'Y',
                    '=AREA' => $area,
                    '=PAGE_URL' => $normalizedUrl,
                ),
                'limit' => 1,
            )
        )->fetch();

        if ($note) {
            return $note;
        }

        $normalizedPath = self::extractPathFromUrl($normalizedUrl);
        $notes = NoteTable::getList(
            array(
                'filter' => array(
                    '=ACTIVE' => 'Y',
                    '=AREA' => $area,
                ),
                'order' => array(
                    'TIMESTAMP_X' => 'DESC',
                    'ID' => 'DESC',
                ),
            )
        )->fetchAll();

        foreach ($notes as $candidate) {
            $candidateUrl = (string) $candidate['PAGE_URL'];

            if (self::extractPathFromUrl($candidateUrl) !== $normalizedPath) {
                continue;
            }

            if (self::normalizeUrl($candidateUrl, $area) === $normalizedUrl) {
                return $candidate;
            }
        }

        return null;
    }

    public static function getCurrentNote()
    {
        $area = self::getCurrentArea();
        $url = self::getCurrentUrl($area);

        return self::getNote($area, $url);
    }

    public static function saveNote($area, $url, $text, $userId = null, $status = 'default', $color = 'sand')
    {
        self::ensureSchema();

        $area = ($area === 'admin') ? 'admin' : 'public';
        $url = self::normalizeUrl($url, $area);
        $text = trim((string) $text);
        $status = self::normalizeStatus($status);
        $color = self::normalizeColor($color);

        if ($text === '') {
            self::deleteNote($area, $url);

            return null;
        }

        $existing = self::getNote($area, $url);
        $now = new DateTime();
        $fields = array(
            'ACTIVE' => 'Y',
            'AREA' => $area,
            'PAGE_URL' => $url,
            'NOTE_TEXT' => $text,
            'STATUS' => $status,
            'COLOR' => $color,
            'TIMESTAMP_X' => $now,
        );

        if ((int) $userId > 0) {
            $fields['CREATED_BY'] = (int) $userId;
        }

        if ($existing) {
            $result = NoteTable::update((int) $existing['ID'], $fields);
        } else {
            $fields['DATE_CREATE'] = $now;
            $result = NoteTable::add($fields);
        }

        if (!$result->isSuccess()) {
            throw new \RuntimeException(implode("\n", $result->getErrorMessages()));
        }

        return self::getNote($area, $url);
    }

    public static function deleteNote($area, $url)
    {
        self::ensureSchema();

        $note = self::getNote($area, $url);

        if (!$note) {
            return true;
        }

        $result = NoteTable::delete((int) $note['ID']);

        if (!$result->isSuccess()) {
            throw new \RuntimeException(implode("\n", $result->getErrorMessages()));
        }

        return true;
    }

    public static function prepareTextForHtml($text)
    {
        return nl2br(htmlspecialcharsbx((string) $text));
    }

    public static function getStatusMap()
    {
        return self::$statusMap;
    }

    public static function getColorPresets()
    {
        return self::$colorPresets;
    }

    public static function normalizeStatus($status)
    {
        $status = trim((string) $status);

        return isset(self::$statusMap[$status]) ? $status : 'default';
    }

    public static function normalizeColor($color)
    {
        $color = trim((string) $color);

        if (isset(self::$colorPresets[$color])) {
            return $color;
        }

        if (preg_match('/^#[0-9a-fA-F]{6}$/', $color)) {
            return strtoupper($color);
        }

        return 'sand';
    }

    public static function resolveColorValue($color)
    {
        $color = self::normalizeColor($color);

        if (isset(self::$colorPresets[$color])) {
            return self::$colorPresets[$color]['value'];
        }

        return $color;
    }

    protected static function normalizeQueryString($query, $area = 'public')
    {
        if (trim((string) $query) === '') {
            return '';
        }

        $params = array();
        parse_str((string) $query, $params);
        $params = self::filterQueryParams($params, $area);

        if (empty($params)) {
            return '';
        }

        self::sortQueryParams($params);

        return http_build_query($params, '', '&', PHP_QUERY_RFC3986);
    }

    protected static function filterQueryParams(array $params, $area = 'public')
    {
        foreach ($params as $key => $value) {
            $key = (string) $key;

            if (self::shouldIgnoreQueryParam($key)) {
                unset($params[$key]);
                continue;
            }

            if (is_array($value)) {
                $value = self::filterQueryParams($value, $area);

                if (empty($value)) {
                    unset($params[$key]);
                    continue;
                }

                $params[$key] = $value;
                continue;
            }

            $value = trim((string) $value);

            if (self::shouldIgnoreQueryParamValue($key, $value, $area)) {
                unset($params[$key]);
                continue;
            }

            $params[$key] = $value;
        }

        return $params;
    }

    protected static function sortQueryParams(array &$params)
    {
        ksort($params);

        foreach ($params as &$value) {
            if (is_array($value)) {
                self::sortQueryParams($value);
            }
        }
        unset($value);
    }

    protected static function shouldIgnoreQueryParam($key)
    {
        if (in_array($key, self::$ignoredQueryParams, true)) {
            return true;
        }

        foreach (self::$ignoredQueryParamPatterns as $pattern) {
            if (preg_match($pattern, $key)) {
                return true;
            }
        }

        return false;
    }

    protected static function shouldIgnoreQueryParamValue($key, $value, $area = 'public')
    {
        if ($value === '') {
            return true;
        }

        if ($area !== 'admin') {
            return false;
        }

        if (!isset(self::$defaultQueryParamValues[$key])) {
            return false;
        }

        return in_array($value, self::$defaultQueryParamValues[$key], true);
    }

    protected static function extractPathFromUrl($url)
    {
        $path = (string) parse_url((string) $url, PHP_URL_PATH);

        if ($path === '') {
            return '/';
        }

        return '/' . ltrim($path, '/');
    }
}

<?php
// Read-only page. Never enable NOT_CHECK_PERMISSIONS for web requests.
if (PHP_SAPI === 'cli') {
    fwrite(STDERR, "Open this page through an authenticated Bitrix browser session.\n");
    exit(1);
}
header('Cache-Control: private, no-store, max-age=0');
header('X-Content-Type-Options: nosniff');
header('X-Frame-Options: SAMEORIGIN');
header('Referrer-Policy: same-origin');
define('NO_KEEP_STATISTIC', true);
define('NO_AGENT_CHECK', true);
define('NO_AGENT_STATISTIC', true);
require_once rtrim($_SERVER['DOCUMENT_ROOT'], '/').'/bitrix/modules/main/include/prolog_before.php';
header('Cache-Control: private, no-store, max-age=0');
global $USER;
if (!is_object($USER) || !$USER->IsAuthorized() || (int)$USER->GetID() !== 5) {
    http_response_code(403);
    header('Content-Type: text/plain; charset=UTF-8');
    exit('Доступ разрешён только Оксане Шлегель (пользователь 5).');
}
define('BAVARIA_DASHBOARD_ALLOWED', true);

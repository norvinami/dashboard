<?php
// Installation metadata only. This entry never loads dashboard data.
if (PHP_SAPI === 'cli') { exit(1); }
header('Cache-Control: private, no-store, max-age=0');
header('X-Content-Type-Options: nosniff');
header('X-Frame-Options: SAMEORIGIN');
define('NO_KEEP_STATISTIC',true);
define('NO_AGENT_CHECK',true);
define('NO_AGENT_STATISTIC',true);
require_once rtrim($_SERVER['DOCUMENT_ROOT'],'/').'/bitrix/modules/main/include/prolog_before.php';
global $USER;
if (!is_object($USER) || !$USER->IsAuthorized() || !$USER->IsAdmin()) {
    http_response_code(403);
    exit('Устанавливать приложение может только администратор Bitrix.');
}
header('Cache-Control: private, no-store, max-age=0');
header('Content-Type: text/html; charset=UTF-8');
?>
<!DOCTYPE html>
<html lang="ru">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<title>Установка дашборда Бавария</title>
<script src="https://api.bitrix24.com/api/v1/"></script>
<style>body{font:15px/1.5 system-ui,sans-serif;padding:24px;color:#0f172a}button{padding:10px 18px;border:0;border-radius:8px;background:#1668f2;color:#fff;font:inherit}button:disabled{opacity:.5}</style>
</head>
<body>
<h1>Дашборд Бавария</h1>
<p>Просмотр показателей CRM. Доступ к дашборду разрешён только Оксане Шлегель, пользователь 5.</p>
<p id="status">Ожидание запуска из Bitrix…</p>
<button id="finish" disabled>Завершить установку</button>
<script>
(function(){
 var status=document.getElementById('status'),button=document.getElementById('finish');
 if(window.parent===window){status.textContent='Откройте эту страницу как обработчик установки локального приложения в Bitrix.';return}
 if(!window.BX24){status.textContent='Не удалось загрузить SDK Bitrix. Обновите страницу установки.';return}
 BX24.init(function(){status.textContent='Приложение готово к завершению установки.';button.disabled=false;BX24.fitWindow()});
 button.addEventListener('click',function(){button.disabled=true;status.textContent='Завершение установки…';BX24.installFinish()});
})();
</script>
</body>
</html>

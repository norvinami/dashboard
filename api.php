<?php
require __DIR__.'/bootstrap.php';
header('Content-Type: application/json; charset=UTF-8');
function dashboardFail(string $message, int $code = 500): void {
    http_response_code($code);
    echo json_encode(['error' => $message], JSON_UNESCAPED_UNICODE | JSON_INVALID_UTF8_SUBSTITUTE);
    exit;
}
if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    dashboardFail('Разрешено только чтение данных.', 405);
}
try {
    if (!\Bitrix\Main\Loader::includeModule('crm')) {
        dashboardFail('Модуль CRM недоступен.');
    }
    $today = new DateTimeImmutable('today');
    $start = $today->modify('first day of this month')->modify('-7 months');
    $categories = [['id' => 0, 'name' => 'Основная воронка']];
    foreach (\Bitrix\Crm\Category\DealCategory::getAll(false) as $category) {
        $categories[] = ['id' => (int)$category['ID'], 'name' => (string)$category['NAME']];
    }
    $sources = [];
    $statuses = CCrmStatus::GetList(['SORT' => 'ASC'], ['ENTITY_ID' => 'SOURCE']);
    while ($row = $statuses->Fetch()) {
        $sources[(string)$row['STATUS_ID']] = (string)$row['NAME'];
    }
    $stages = [];
    foreach ($categories as $category) {
        $stages[$category['id']] = \Bitrix\Crm\Category\DealCategory::getStageList($category['id']);
    }
    // CRM permissions are checked as logged-in user 5; no elevated service identity.
    $result = CCrmDeal::GetListEx(
        ['ID' => 'ASC'],
        ['>=DATE_CREATE' => $start->format('d.m.Y').' 00:00:00', 'CHECK_PERMISSIONS' => 'Y'],
        false,
        ['nTopCount' => 5001],
        ['ID', 'TITLE', 'CATEGORY_ID', 'STAGE_ID', 'STAGE_SEMANTIC_ID', 'SOURCE_ID',
         'ASSIGNED_BY_ID', 'DATE_CREATE', 'OPPORTUNITY', 'CURRENCY_ID']
    );
    if (!is_object($result)) {
        dashboardFail('Не удалось прочитать сделки CRM.');
    }
    $deals = [];
    $employeeIds = [5 => true];
    while ($row = $result->Fetch()) {
        $category = (int)$row['CATEGORY_ID'];
        $stage = (string)$row['STAGE_ID'];
        $semantic = (string)$row['STAGE_SEMANTIC_ID'];
        if (!in_array($semantic, ['P', 'S', 'F'], true)) { dashboardFail('Не удалось определить семантику стадии сделки.'); }
        $timestamp = MakeTimeStamp($row['DATE_CREATE']);
        if (!$timestamp) {
            dashboardFail('Не удалось определить дату создания сделки.');
        }
        if ($category !== 7 && (string)$row['CURRENCY_ID'] !== 'KZT') {
            dashboardFail('Есть успешные сделки в валюте, отличной от KZT. Сначала необходимо согласовать пересчёт сумм.', 422);
        }
        $employeeIds[(int)$row['ASSIGNED_BY_ID']] = true;
        $deals[] = [
            'id' => (int)$row['ID'], 'title' => (string)$row['TITLE'], 'category' => $category,
            'stageId' => $stage, 'stageName' => (string)($stages[$category][$stage] ?? $stage),
            'semantic' => $semantic, 'source' => (string)$row['SOURCE_ID'],
            'manager' => (int)$row['ASSIGNED_BY_ID'], 'date' => date('Y-m-d', $timestamp),
            'amount' => (float)$row['OPPORTUNITY'], 'currency' => (string)$row['CURRENCY_ID']
        ];
        if (count($deals) > 5000) {
            dashboardFail('За 8 месяцев найдено более 5000 доступных сделок. Нужна серверная агрегация; неполные цифры не показываем.', 422);
        }
    }
    $tasks = [];
    if (!\Bitrix\Main\Loader::includeModule('tasks')) {
        dashboardFail('Модуль задач недоступен.');
    }
    $taskResult = CTasks::GetList(
        ['ID' => 'ASC'], ['CHECK_PERMISSIONS' => 'Y'],
        ['ID', 'TITLE', 'RESPONSIBLE_ID', 'DEADLINE', 'STATUS'],
        ['USER_ID' => 5, 'CHECK_PERMISSIONS' => 'Y', 'NAV_PARAMS' => ['nTopCount' => 2001]]
    );
    if (!is_object($taskResult)) {
        dashboardFail('Не удалось прочитать задачи.');
    }
    while ($row = $taskResult->Fetch()) {
        $employeeIds[(int)$row['RESPONSIBLE_ID']] = true;
        $deadline = $row['DEADLINE'] ? MakeTimeStamp($row['DEADLINE']) : false;
        $done = (int)$row['STATUS'] === 5;
        $tasks[] = ['id' => (int)$row['ID'], 'title' => (string)$row['TITLE'],
            'manager' => (int)$row['RESPONSIBLE_ID'], 'due' => $deadline ? date('Y-m-d', $deadline) : '',
            'done' => $done, 'overdue' => !$done && $deadline && $deadline < time()];
        if (count($tasks) > 2000) {
            dashboardFail('Найдено более 2000 доступных задач. Нужна серверная агрегация; неполные цифры не показываем.', 422);
        }
    }
    $employees = [];
    foreach (array_keys($employeeIds) as $id) {
        $row = $id > 0 ? CUser::GetByID($id)->Fetch() : false;
        $name = $row ? trim($row['LAST_NAME'].' '.$row['NAME']) : 'Не назначен';
        $employees[] = ['id' => $id, 'name' => $name ?: 'Сотрудник '.$id,
            'role' => $row ? (string)$row['WORK_POSITION'] : ''];
    }
    echo json_encode(['today' => $today->format('Y-m-d'), 'from' => $start->format('Y-m-d'),
        'categories' => $categories, 'sources' => $sources, 'deals' => $deals,
        'tasks' => $tasks, 'employees' => $employees],
        JSON_UNESCAPED_UNICODE | JSON_INVALID_UTF8_SUBSTITUTE | JSON_THROW_ON_ERROR);
} catch (Throwable $error) {
    error_log('Bavaria dashboard: '.get_class($error).' at '.$error->getFile().':'.$error->getLine());
    dashboardFail('Ошибка чтения Bitrix. Подробности доступны администратору в журнале PHP.');
}

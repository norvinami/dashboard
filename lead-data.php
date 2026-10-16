<?php
if (!defined('BAVARIA_DASHBOARD_ALLOWED')) { http_response_code(403); exit; }
function dashboardNormalizeLabel(string $value): string {
    return mb_strtolower(trim(preg_replace('/\s+/u', ' ', $value)));
}
/** Resolve the real enum field by its configured Russian label; never guess UF IDs. */
function dashboardLeadReasonField(): array {
    global $USER_FIELD_MANAGER;
    if (!is_object($USER_FIELD_MANAGER)) { throw new DomainException('Не удалось прочитать настройку поля «Причина обращения».'); }
    $candidates = [];
    foreach ($USER_FIELD_MANAGER->GetUserFields('CRM_LEAD', 0, 'ru') as $field) {
        $label = $field['EDIT_FORM_LABEL'] ?? '';
        if (is_array($label)) { $label = $label['ru'] ?? ''; }
        if (dashboardNormalizeLabel((string)$label) === 'причина обращения' && ($field['USER_TYPE_ID'] ?? '') === 'enumeration') {
            $candidates[] = $field;
        }
    }
    if (count($candidates) !== 1) {
        throw new DomainException('Нужно проверить поле-список «Причина обращения» в лидах: найдено '.count($candidates).'. Остальные блоки работают независимо.');
    }
    $field = $candidates[0];
    $directions = [];
    $enums = CUserFieldEnum::GetList(['SORT' => 'ASC'], ['USER_FIELD_ID' => (int)$field['ID']]);
    while ($enum = $enums->Fetch()) {
        $label = dashboardNormalizeLabel((string)$enum['VALUE']);
        if ($label === 'покупка автомобиля') { $directions[(string)$enum['ID']] = 'sales'; }
        if ($label === 'сервис') { $directions[(string)$enum['ID']] = 'service'; }
    }
    if (!in_array('sales', $directions, true) || !in_array('service', $directions, true)) {
        throw new DomainException('В поле «Причина обращения» не найдены оба значения: «Покупка автомобиля» и «Сервис».');
    }
    return ['code' => (string)$field['FIELD_NAME'], 'directions' => $directions];
}
function dashboardCrmRows(string $class, array $filter, array $select): Generator {
    $after = 0;
    while (true) {
        $result = $class::GetListEx(['ID' => 'ASC'], array_merge($filter, ['>ID' => $after, 'CHECK_PERMISSIONS' => 'Y']), false, ['nTopCount' => 500], $select);
        if (!is_object($result)) { throw new RuntimeException('CRM read failed'); }
        $count = 0;
        while ($row = $result->Fetch()) {
            $id = (int)$row['ID'];
            if ($id <= $after) { throw new RuntimeException('CRM cursor did not advance'); }
            $after = $id;
            $count++;
            yield $row;
        }
        if ($count < 500) { return; }
    }
}
function dashboardEmptyLeadCounts(): array {
    return ['total' => 0, 'qualified' => 0, 'failed' => 0, 'open' => 0, 'unconfirmed' => 0];
}
function dashboardLeadBatch(array $batch, array $field, array &$stats): void {
    $convertedIds = [];
    foreach ($batch as $lead) {
        if (($lead['STATUS_SEMANTIC_ID'] ?? '') === 'S') { $convertedIds[] = (int)$lead['ID']; }
    }
    $linked = [];
    if ($convertedIds) {
        // No deal-date condition: conversion belongs to the cohort of original leads.
        foreach (dashboardCrmRows('CCrmDeal', ['LEAD_ID' => $convertedIds], ['ID', 'LEAD_ID', 'CATEGORY_ID']) as $deal) {
            if (!isset($deal['LEAD_ID'], $deal['CATEGORY_ID']) || !in_array((int)$deal['LEAD_ID'], $convertedIds, true)) {
                throw new RuntimeException('Incomplete or unrelated conversion link');
            }
            $linked[(int)$deal['LEAD_ID']][(int)$deal['CATEGORY_ID']] = true;
        }
    }
    foreach ($batch as $lead) {
        $reasons = $lead[$field['code']] ?? [];
        if (!is_array($reasons)) { $reasons = [$reasons]; }
        $directions = [];
        foreach ($reasons as $reason) {
            if (is_scalar($reason) && isset($field['directions'][(string)$reason])) {
                $directions[$field['directions'][(string)$reason]] = true;
            }
        }
        if (count($directions) !== 1) { $stats['unassigned']++; continue; }
        $direction = array_key_first($directions);
        $semantic = (string)($lead['STATUS_SEMANTIC_ID'] ?? '');
        if (!in_array($semantic, ['P', 'S', 'F'], true)) { throw new DomainException('Не удалось определить результат одного из лидов.'); }
        $counts = &$stats['directions'][$direction];
        $counts['total']++;
        if ($semantic === 'P') { $counts['open']++; }
        elseif ($semantic === 'F') { $counts['failed']++; }
        elseif (!empty($linked[(int)$lead['ID']][$direction === 'sales' ? 0 : 7])) { $counts['qualified']++; }
        else { $counts['unconfirmed']++; }
        unset($counts);
    }
}
function dashboardReadLeads(DateTimeImmutable $from, DateTimeImmutable $to): array {
    $field = dashboardLeadReasonField();
    $stats = ['directions' => ['sales' => dashboardEmptyLeadCounts(), 'service' => dashboardEmptyLeadCounts()], 'unassigned' => 0];
    $batch = [];
    foreach (dashboardCrmRows('CCrmLead', ['>=DATE_CREATE' => $from->format('d.m.Y').' 00:00:00', '<=DATE_CREATE' => $to->format('d.m.Y').' 23:59:59'], ['ID', 'STATUS_SEMANTIC_ID', $field['code']]) as $lead) {
        $batch[] = $lead;
        if (count($batch) === 500) { dashboardLeadBatch($batch, $field, $stats); $batch = []; }
    }
    if ($batch) { dashboardLeadBatch($batch, $field, $stats); }
    return $stats;
}

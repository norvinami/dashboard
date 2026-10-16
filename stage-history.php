<?php
if (!defined('BAVARIA_DASHBOARD_ALLOWED')) { http_response_code(403); exit; }
/** Read history ONLY for deal IDs already returned by CRM with CHECK_PERMISSIONS=Y. */
function dashboardDealHistory(array $authorizedDeals): array {
    if (!$authorizedDeals) { return []; }
    $class = \Bitrix\Crm\History\Entity\DealStageHistoryTable::class;
    if (!class_exists($class)) {
        throw new DomainException('История этапов недоступна в этой версии Bitrix. Конверсию между этапами пока нельзя подтвердить.');
    }
    $fields = $class::getEntity()->getFields();
    foreach (['ID', 'OWNER_ID', 'CATEGORY_ID', 'STAGE_ID'] as $required) {
        if (!isset($fields[$required])) { throw new DomainException('Структура истории этапов требует проверки.'); }
    }
    $timeField = isset($fields['CREATED_TIME']) ? 'CREATED_TIME' : (isset($fields['CREATED_DATE']) ? 'CREATED_DATE' : null);
    if ($timeField === null) { throw new DomainException('Не найдена дата события в истории этапов.'); }
    $categories = [];
    foreach ($authorizedDeals as $deal) { $categories[(int)$deal['id']] = (int)$deal['category']; }
    $visits = [];
    foreach (array_chunk(array_keys($categories), 500) as $ids) {
        $filter = ['@OWNER_ID' => $ids];
        if (isset($fields['OWNER_TYPE_ID'])) { $filter['=OWNER_TYPE_ID'] = 2; }
        $result = $class::getList(['select' => ['ID','OWNER_ID','CATEGORY_ID','STAGE_ID',$timeField],
            'filter' => $filter, 'order' => [$timeField => 'ASC', 'ID' => 'ASC']]);
        $position = 0;
        while ($row = $result->fetch()) {
            if (!isset($row['OWNER_ID'], $row['CATEGORY_ID'], $row['STAGE_ID'], $row[$timeField])) {
                throw new RuntimeException('Incomplete stage history row');
            }
            $id = (int)$row['OWNER_ID'];
            if (!isset($categories[$id]) || !in_array($id, $ids, true)) {
                throw new RuntimeException('History returned an unauthorized deal');
            }
            if ((int)$row['CATEGORY_ID'] !== $categories[$id]) { continue; }
            $stage = (string)$row['STAGE_ID'];
            if ($stage === '' || empty($row[$timeField])) { throw new RuntimeException('Incomplete stage history'); }
            $position++;
            if (!isset($visits[$id][$stage])) { $visits[$id][$stage] = ['first' => $position, 'last' => $position]; }
            else { $visits[$id][$stage]['last'] = $position; }
        }
    }
    return $visits;
}

<?php
if (!defined('BAVARIA_DASHBOARD_ALLOWED')) { http_response_code(403); exit; }
function dashboardEmptyActivityCounts(): array {
    return ['overdue'=>0,'today'=>0,'tomorrow'=>0,'days3'=>0,'days7'=>0,'without'=>0,'active'=>0,'activities'=>0];
}
function dashboardActivityBucket($deadline, DateTimeImmutable $today): ?string {
    if (!$deadline) { return null; }
    $timestamp = MakeTimeStamp($deadline);
    if (!$timestamp) { return null; }
    $date = (new DateTimeImmutable('@'.$timestamp))->setTimezone($today->getTimezone())->setTime(0,0);
    $days = (int)$today->diff($date)->format('%r%a');
    if ($days < 0) { return 'overdue'; }
    if ($days === 0) { return 'today'; }
    if ($days === 1) { return 'tomorrow'; }
    if ($days <= 3) { return 'days3'; }
    if ($days <= 7) { return 'days7'; }
    return null;
}
/** CRM activity report, independent of the task module and date cohort. */
function dashboardReadActivities(?array $categories, DateTimeImmutable $today): array {
    $report = ['rows'=>[], 'totals'=>dashboardEmptyActivityCounts(), 'asOf'=>$today->format('Y-m-d')];
    if ($categories === []) { return $report; }
    $filter = ['STAGE_SEMANTIC_ID'=>'P'];
    if ($categories !== null) { $filter['CATEGORY_ID']=$categories; }
    $deals = [];
    foreach (dashboardCrmRows('CCrmDeal', $filter, ['ID','ASSIGNED_BY_ID','CATEGORY_ID']) as $deal) {
        $id = (int)$deal['ID'];
        $deals[$id] = (int)$deal['ASSIGNED_BY_ID'];
    }
    if (!$deals) { return $report; }
    $bindingClass = \Bitrix\Crm\ActivityBindingTable::class;
    if (!class_exists($bindingClass) || !class_exists('CCrmActivity')) {
        throw new DomainException('Не удалось подключить CRM-дела в этой версии Bitrix.');
    }
    $bindings = [];
    foreach (array_chunk(array_keys($deals),500) as $ids) {
        $result=$bindingClass::getList(['select'=>['ACTIVITY_ID','OWNER_ID'], 'filter'=>['=OWNER_TYPE_ID'=>2,'@OWNER_ID'=>$ids]]);
        while ($row=$result->fetch()) {
            $owner=(int)$row['OWNER_ID'];$activity=(int)$row['ACTIVITY_ID'];
            if (!isset($deals[$owner]) || !in_array($owner,$ids,true) || $activity<=0) { throw new RuntimeException('Invalid activity binding'); }
            $bindings[$activity][$owner]=true;
        }
    }
    $byEmployee=[];$hasActivity=[];
    $ensure=static function(int $employee) use (&$byEmployee): void {
        if (!isset($byEmployee[$employee])) { $byEmployee[$employee]=dashboardEmptyActivityCounts(); }
    };
    foreach ($deals as $manager) { $ensure($manager);$byEmployee[$manager]['active']++; }
    $seen=[];
    foreach (array_chunk(array_keys($bindings),500) as $ids) {
        $result=CCrmActivity::GetList(['ID'=>'ASC'], ['ID'=>$ids,'COMPLETED'=>'N','CHECK_PERMISSIONS'=>'Y'], false, false, ['ID','RESPONSIBLE_ID','DEADLINE','COMPLETED']);
        if (!is_object($result)) { throw new RuntimeException('Activity read failed'); }
        while ($activity=$result->Fetch()) {
            $id=(int)$activity['ID'];
            if (!isset($bindings[$id]) || !in_array($id,$ids,true) || ($activity['COMPLETED']??'')!=='N') { throw new RuntimeException('Unexpected activity result'); }
            if (isset($seen[$id])) { continue; }$seen[$id]=true;
            $manager=(int)$activity['RESPONSIBLE_ID'];$ensure($manager);
            $byEmployee[$manager]['activities']++;
            $bucket=dashboardActivityBucket($activity['DEADLINE']??null,$today);
            if ($bucket!==null) { $byEmployee[$manager][$bucket]++; }
            foreach ($bindings[$id] as $dealId=>$unused) { $hasActivity[$dealId]=true; }
        }
    }
    foreach ($deals as $id=>$manager) { if (!isset($hasActivity[$id])) { $byEmployee[$manager]['without']++; } }
    foreach ($byEmployee as $id=>$counts) {
        $user=$id>0?CUser::GetByID($id)->Fetch():false;
        $name=$user?trim($user['LAST_NAME'].' '.$user['NAME']):'Не назначен';
        $report['rows'][]=['id'=>$id,'name'=>$name?:'Сотрудник '.$id,'counts'=>$counts];
        foreach ($counts as $key=>$value) { $report['totals'][$key]+=$value; }
    }
    usort($report['rows'],static fn($a,$b)=>strnatcasecmp($a['name'],$b['name']));
    return $report;
}

<?php
if (!defined('BAVARIA_DASHBOARD_ALLOWED')) { http_response_code(403); exit; }
require_once __DIR__.'/read-access.php';
function dashboardDealActivityBucket(?int $timestamp, bool $hasActivity, DateTimeImmutable $today): string {
    if (!$hasActivity) { return 'without'; }
    if ($timestamp===null) { return 'unscheduled'; }
    $date=(new DateTimeImmutable('@'.$timestamp))->setTimezone($today->getTimezone())->setTime(0,0);
    $today=$today->setTime(0,0);
    if ($date<$today) { return 'overdue'; }
    if ($date==$today) { return 'today'; }
    $weekEnd=$today->modify('+'.(7-(int)$today->format('N')).' days');
    if ($date<=$weekEnd) { return 'week'; }
    if ($date<=$today->modify('last day of this month')) { return 'month'; }
    return 'later';
}
/** Input is the permission-checked deal cohort, never arbitrary client-supplied IDs. */
function dashboardDealActivities(array $deals, DateTimeImmutable $today): array {
    $rows=[];
    foreach ($deals as $deal) {
        $id=(int)$deal['id'];
        $rows[$id]=['id'=>$id,'title'=>$deal['title'],'category'=>$deal['category'],'semantic'=>$deal['semantic'],'deferred'=>$deal['deferred'], 'deadline'=>null,'hasActivity'=>false];
    }
    if (!$rows) { return ['rows'=>[], 'asOf'=>$today->format('Y-m-d')]; }
    $bindingClass=\Bitrix\Crm\ActivityBindingTable::class;
    if (!class_exists($bindingClass)||!class_exists('CCrmActivity')) { throw new DomainException('CRM-дела недоступны в этой версии Битрикса.'); }
    $bindings=[];
    foreach (array_chunk(array_keys($rows),500) as $ids) {
        $result=$bindingClass::getList(['select'=>['ACTIVITY_ID','OWNER_ID'],'filter'=>['=OWNER_TYPE_ID'=>2,'@OWNER_ID'=>$ids]]);
        while ($binding=$result->fetch()) {
            $owner=(int)$binding['OWNER_ID'];$activity=(int)$binding['ACTIVITY_ID'];
            if (!isset($rows[$owner])||!in_array($owner,$ids,true)||$activity<=0) { throw new RuntimeException('Invalid CRM binding'); }
            $bindings[$activity][$owner]=true;
        }
    }
    $seen=[];
    foreach (array_chunk(array_keys($bindings),500) as $ids) {
        $result=CCrmActivity::GetList(['ID'=>'ASC'],['ID'=>$ids,'COMPLETED'=>'N','CHECK_PERMISSIONS'=>'Y'],false,false,['ID','DEADLINE','COMPLETED'],dashboardCrmReadOptions());
        if (!is_object($result)) { throw new RuntimeException('CRM activity read failed'); }
        while ($activity=$result->Fetch()) {
            $id=(int)$activity['ID'];
            if (!isset($bindings[$id])||!in_array($id,$ids,true)||($activity['COMPLETED']??'')!=='N') { throw new RuntimeException('Unexpected CRM activity'); }
            if (isset($seen[$id])) { continue; }$seen[$id]=true;
            $timestamp=empty($activity['DEADLINE'])?null:MakeTimeStamp($activity['DEADLINE']);
            $timestamp=$timestamp?:null;
            foreach ($bindings[$id] as $owner=>$unused) {
                $rows[$owner]['hasActivity']=true;
                if ($timestamp!==null&&($rows[$owner]['deadline']===null||$timestamp<$rows[$owner]['deadline'])) { $rows[$owner]['deadline']=$timestamp; }
            }
        }
    }
    foreach ($rows as &$row) {
        $row['bucket']=dashboardDealActivityBucket($row['deadline'],$row['hasActivity'],$today);
        $row['deadline']=$row['deadline']===null?null:date('Y-m-d',$row['deadline']);
        unset($row['hasActivity']);
    }
    unset($row);
    return ['rows'=>array_values($rows),'asOf'=>$today->format('Y-m-d')];
}

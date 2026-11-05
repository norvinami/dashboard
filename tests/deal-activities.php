<?php
namespace {
    if (PHP_SAPI!=='cli') { http_response_code(403); exit; }
    define('BAVARIA_DASHBOARD_ALLOWED',true);
    require __DIR__.'/crm-read-fixture.php';
    date_default_timezone_set('Asia/Qyzylorda');
    function MakeTimeStamp($value) { return strtotime($value); }
    function checkDealActivity($value,$message) { if (!$value) { throw new \RuntimeException($message); } }
    class DealActivityResult {
        public function __construct(private array $rows) {}
        public function Fetch() { return array_shift($this->rows)?:false; }
    }
    class CCrmActivity {
        public static array $rows=[];
        public static function GetList($order,$filter,$group,$nav,$select,$options=[]) {
            verifyDashboardReadOptions($options);
            checkDealActivity($filter['CHECK_PERMISSIONS']==='Y'&&$filter['COMPLETED']==='N','Activity permissions enforced');
            return new DealActivityResult(array_values(array_filter(self::$rows,static fn($row)=>in_array($row['ID'],$filter['ID'],true)&&$row['COMPLETED']==='N'&&$row['ACCESS'])));
        }
    }
}
namespace Bitrix\Crm {
    class ActivityBindingTable {
        public static array $rows=[];
        public static int $queries=0;
        public static function getList($params) {
            self::$queries++;
            \checkDealActivity($params['filter']['=OWNER_TYPE_ID']===2&&count($params['filter']['@OWNER_ID'])<=500,'Scoped deal binding batches');
            $rows=array_values(array_filter(self::$rows,static fn($row)=>in_array($row['OWNER_ID'],$params['filter']['@OWNER_ID'],true)));
            return new class($rows) { public function __construct(private array $rows){} public function fetch(){return array_shift($this->rows)?:false;} };
        }
    }
}
namespace {
    require dirname(__DIR__).'/deal-activities.php';
    $today=new \DateTimeImmutable('2026-11-05');
    foreach (['2026-11-04'=>'overdue','2026-11-05'=>'today','2026-11-06'=>'week','2026-11-08'=>'week','2026-11-09'=>'month','2026-11-30'=>'month','2026-12-01'=>'later'] as $date=>$expected) {
        checkDealActivity(dashboardDealActivityBucket(strtotime($date),true,$today)===$expected,'Calendar bucket '.$date);
    }
    checkDealActivity(dashboardDealActivityBucket(strtotime('2026-12-01'),true,new \DateTimeImmutable('2026-11-30'))==='week','Week crosses month boundary');
    checkDealActivity(dashboardDealActivityBucket(strtotime('2026-11-30'),true,new \DateTimeImmutable('2026-11-29'))==='month','Sunday ends calendar week');
    checkDealActivity(dashboardDealActivityBucket(null,true,$today)==='unscheduled','Open activity without deadline is not no activity');
    $deals=[];
    for($id=1;$id<=505;$id++) { $deals[]=['id'=>$id,'title'=>'Deal '.$id,'category'=>0,'semantic'=>$id%3===0?'S':($id%3===1?'P':'F'),'deferred'=>$id===1]; }
    foreach ([[101,1],[101,2],[102,1],[103,3],[104,4],[105,5],[106,6],[107,7],[108,8]] as [$activity,$owner]) { \Bitrix\Crm\ActivityBindingTable::$rows[]=['ACTIVITY_ID'=>$activity,'OWNER_ID'=>$owner]; }
    foreach ([[101,'2026-11-07','N',true],[102,'2026-11-04','N',true],[103,'2026-11-05','Y',true],[104,'2026-11-05','N',false],[105,null,'N',true],[106,'2026-12-10','N',true],[107,'2026-11-05','N',true],[108,'2026-11-20','N',true]] as [$id,$deadline,$completed,$access]) { CCrmActivity::$rows[]=['ID'=>$id,'DEADLINE'=>$deadline,'COMPLETED'=>$completed,'ACCESS'=>$access]; }
    $report=dashboardDealActivities($deals,$today);
    $rows=array_column($report['rows'],null,'id');
    checkDealActivity($rows[1]['semantic']==='P'&&$rows[2]['semantic']==='F'&&$rows[3]['semantic']==='S','Deal semantics retained for open filter');
    checkDealActivity(count($rows)===505&&\Bitrix\Crm\ActivityBindingTable::$queries===2,'All unique deals and batches');
    checkDealActivity($rows[1]['bucket']==='overdue'&&$rows[2]['bucket']==='week','Nearest deadline per deal; shared activity supports multiple deals');
    checkDealActivity($rows[3]['bucket']==='without'&&$rows[4]['bucket']==='without','Completed and unreadable excluded');
    checkDealActivity($rows[5]['bucket']==='unscheduled'&&$rows[6]['bucket']==='later'&&$rows[7]['bucket']==='today'&&$rows[8]['bucket']==='month','Every status retained');
    checkDealActivity(dashboardDealActivities([],$today)['rows']===[],'Empty permitted cohort');
    echo "PASS: calendar week/month boundaries, nearest open activity, unique deals, bindings scoped in batches, permissions, completed/unreadable, no deadline and later dates. Mock Bitrix.\n";
}

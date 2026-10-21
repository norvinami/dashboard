<?php
namespace {
    if (PHP_SAPI !== 'cli') { http_response_code(403); exit; }
    define('BAVARIA_DASHBOARD_ALLOWED',true);
    date_default_timezone_set('Asia/Qyzylorda');
    function MakeTimeStamp($value) { return strtotime($value); }
    function checkActivity($value,$message) { if (!$value) { throw new \RuntimeException($message); } }
    final class ActivityTestResult {
        private array $rows;
        public function __construct(array $rows){$this->rows=$rows;}
        public function Fetch(){return array_shift($this->rows)?:false;}
    }
    final class CCrmDeal {
        public static array $rows=[];
        public static function GetListEx($order,$filter,$group,$nav,$select){
            checkActivity($filter['CHECK_PERMISSIONS']==='Y'&&$filter['STAGE_SEMANTIC_ID']==='P','Only permitted active deals');
            checkActivity(!isset($filter['>=DATE_CREATE']),'Snapshot includes old active deals');
            $rows=array_filter(self::$rows,static fn($r)=>$r['ID']>$filter['>ID']&&$r['STAGE_SEMANTIC_ID']==='P'&&(!isset($filter['CATEGORY_ID'])||in_array($r['CATEGORY_ID'],$filter['CATEGORY_ID'],true)));
            return new ActivityTestResult(array_slice(array_values($rows),0,$nav['nTopCount']));
        }
    }
    final class CCrmActivity {
        public static array $rows=[];
        public static function GetList($order,$filter,$group,$nav,$select){
            checkActivity($filter['CHECK_PERMISSIONS']==='Y'&&$filter['COMPLETED']==='N','Only permitted incomplete activities');
            return new ActivityTestResult(array_values(array_filter(self::$rows,static fn($r)=>in_array($r['ID'],$filter['ID'],true)&&$r['COMPLETED']==='N'&&$r['ACCESS'])));
        }
    }
    final class CUser { public static function GetByID($id){return new ActivityTestResult([['LAST_NAME'=>'User','NAME'=>(string)$id]]);} }
}
namespace Bitrix\Crm {
    final class ActivityBindingTable {
        public static array $rows=[];
        public static function getList($params){
            \checkActivity($params['filter']['=OWNER_TYPE_ID']===2,'Only deal bindings');
            return new \ActivityTestResult(array_values(array_filter(self::$rows,static fn($r)=>in_array($r['OWNER_ID'],$params['filter']['@OWNER_ID'],true))));
        }
    }
}
namespace {
    require dirname(__DIR__).'/lead-data.php';
    require dirname(__DIR__).'/activity-data.php';
    $today=new \DateTimeImmutable('2026-10-21');
    foreach([[1,5,0,'P'],[2,5,0,'P'],[3,6,7,'P'],[4,6,0,'P'],[5,5,0,'S']] as [$id,$manager,$category,$status]){
        CCrmDeal::$rows[]=['ID'=>$id,'ASSIGNED_BY_ID'=>$manager,'CATEGORY_ID'=>$category,'STAGE_SEMANTIC_ID'=>$status];
    }
    foreach([[1,5,-1,1],[2,5,0,1],[3,6,1,3],[4,6,2,3],[5,6,3,3],[6,6,4,1],[7,5,7,1],[8,5,8,1],[9,5,null,1],[10,6,0,4],[11,6,0,4],[12,5,0,5]] as [$id,$manager,$days,$deal]){
        CCrmActivity::$rows[]=['ID'=>$id,'RESPONSIBLE_ID'=>$manager,'DEADLINE'=>$days===null?'':$today->modify(($days>=0?'+':'').$days.' days')->format('Y-m-d').' 23:59:59','COMPLETED'=>$id===10?'Y':'N','ACCESS'=>$id!==11];
        \Bitrix\Crm\ActivityBindingTable::$rows[]=['ACTIVITY_ID'=>$id,'OWNER_ID'=>$deal];
    }
    \Bitrix\Crm\ActivityBindingTable::$rows[]=['ACTIVITY_ID'=>1,'OWNER_ID'=>2];
    $report=dashboardReadActivities(null,$today);
    checkActivity($report['totals']===['overdue'=>1,'today'=>1,'tomorrow'=>1,'days3'=>2,'days7'=>2,'without'=>1,'active'=>4,'activities'=>9],'Complete snapshot, distinct deadlines, deduped bindings, without accessible open activity');
    $rows=array_column($report['rows'],null,'id');
    checkActivity($rows[5]['counts']['active']===2&&$rows[5]['counts']['activities']===5,'Deal and activity responsibilities');
    checkActivity($rows[6]['counts']['active']===2&&$rows[6]['counts']['activities']===4&&$rows[6]['counts']['without']===1,'Other responsible and unreadable activity');
    $service=dashboardReadActivities([7],$today);
    checkActivity($service['totals']['active']===1&&$service['totals']['activities']===3,'Selected pipelines');
    checkActivity(dashboardReadActivities([],$today)['totals']['active']===0,'Empty selection');
    checkActivity(dashboardReadActivities([999],$today)['rows']===[],'No matching deals');
    checkActivity(dashboardActivityBucket('2026-10-21 00:01:00',$today)==='today','Today is a calendar bucket');
    checkActivity(dashboardActivityBucket('', $today)===null,'No deadline');
    echo "PASS: CRM activity snapshot, calendar buckets, deduped multi-deal bindings, completed/unreadable exclusion, pipeline filter, responsibilities and totals. Mock Bitrix.\n";
}

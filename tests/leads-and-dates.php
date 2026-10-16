<?php
if (PHP_SAPI !== 'cli') { http_response_code(403); exit; }
define('BAVARIA_DASHBOARD_ALLOWED', true);
function checkLead($condition, $message) { if (!$condition) { throw new RuntimeException($message); } }
require dirname(__DIR__).'/date-range.php';
require dirname(__DIR__).'/lead-data.php';
$range = dashboardDateRange(['from'=>'2024-02-28','to'=>'2024-03-01'], new DateTimeImmutable('2026-10-16'));
checkLead($range['previousFrom']->format('Y-m-d')==='2024-02-25' && $range['previousTo']->format('Y-m-d')==='2024-02-27', 'Inclusive range across leap day');
$single = dashboardDateRange(['from'=>'2026-10-16','to'=>'2026-10-16'], new DateTimeImmutable());
checkLead($single['previousFrom']->format('Y-m-d')==='2026-10-15', 'Single-day previous period');
$default = dashboardDateRange([], new DateTimeImmutable('2026-10-16'));
checkLead($default['from']->format('Y-m-d')==='2026-10-01' && $default['to']->format('Y-m-d')==='2026-10-16', 'Default month through today');
foreach ([['from'=>'2026-02-30'], ['from'=>'2026-10-17','to'=>'2026-10-16'], ['from'=>['2026-10-01']], ['to'=>'not-a-date']] as $invalid) {
    $rejected=false;
    try { dashboardDateRange($invalid, new DateTimeImmutable('2026-10-16')); } catch (InvalidArgumentException $error) { $rejected=true; }
    checkLead($rejected, 'Invalid range rejected');
}
final class LeadTestResult {
    private array $rows;
    public function __construct(array $rows) { $this->rows=$rows; }
    public function Fetch() { return array_shift($this->rows) ?: false; }
}
$USER_FIELD_MANAGER = new class {
    public bool $ambiguous = false;
    public function GetUserFields($entity, $id, $language) {
        checkLead($entity==='CRM_LEAD', 'Resolve lead field');
        $fields=[['ID'=>12,'FIELD_NAME'=>'UF_CRM_REASON','USER_TYPE_ID'=>'enumeration','EDIT_FORM_LABEL'=>'  Причина обращения  ']];
        if ($this->ambiguous) { $fields[]=$fields[0]; }
        return $fields;
    }
};
final class CUserFieldEnum {
    public static function GetList($order, $filter) { return new LeadTestResult([['ID'=>101,'VALUE'=>'Покупка автомобиля'],['ID'=>102,'VALUE'=>'Сервис']]); }
}
final class CCrmLead {
    public static array $rows=[];
    public static int $queries=0;
    public static function GetListEx($order, $filter, $group, $nav, $select) {
        self::$queries++;
        checkLead($filter['CHECK_PERMISSIONS']==='Y', 'Lead permissions enforced');
        $rows=array_filter(self::$rows, static fn($r)=>$r['ID']>$filter['>ID'] && strtotime($r['DATE_CREATE'])>=strtotime($filter['>=DATE_CREATE']) && strtotime($r['DATE_CREATE'])<=strtotime($filter['<=DATE_CREATE']));
        return new LeadTestResult(array_slice(array_values($rows),0,$nav['nTopCount']));
    }
}
final class CCrmDeal {
    public static array $rows=[];
    public static function GetListEx($order, $filter, $group, $nav, $select) {
        checkLead($filter['CHECK_PERMISSIONS']==='Y', 'Linked deal permissions enforced');
        checkLead(!isset($filter['>=DATE_CREATE']) && !isset($filter['<=DATE_CREATE']), 'Conversions may happen outside lead cohort dates');
        $rows=array_filter(self::$rows, static fn($r)=>$r['ID']>$filter['>ID'] && in_array($r['LEAD_ID'],$filter['LEAD_ID'],true));
        return new LeadTestResult(array_slice(array_values($rows),0,$nav['nTopCount']));
    }
}
foreach ([[1,101,'P'],[2,101,'F'],[3,101,'S'],[4,101,'S'],[5,101,'S'],[6,102,'S'],[7,102,'S'],[8,102,'F'],[9,102,'P'],[10,'','S'],[11,[101,102],'P'],[12,101,'S']] as [$id,$reason,$semantic]) {
    CCrmLead::$rows[]=['ID'=>$id,'UF_CRM_REASON'=>$reason,'STATUS_SEMANTIC_ID'=>$semantic,'DATE_CREATE'=>'2026-10-16 23:59:59'];
}
CCrmLead::$rows[]=['ID'=>13,'UF_CRM_REASON'=>101,'STATUS_SEMANTIC_ID'=>'P','DATE_CREATE'=>'2026-10-17 00:00:00'];
for($id=100;$id<620;$id++){ CCrmLead::$rows[]=['ID'=>$id,'UF_CRM_REASON'=>101,'STATUS_SEMANTIC_ID'=>'P','DATE_CREATE'=>'2026-10-01 00:00:00']; }
foreach ([[201,3,0],[202,3,0],[203,4,7],[204,6,7],[205,7,0],[206,10,7],[207,12,5]] as [$id,$lead,$category]) {
    CCrmDeal::$rows[]=['ID'=>$id,'LEAD_ID'=>$lead,'CATEGORY_ID'=>$category,'DATE_CREATE'=>'2026-11-01'];
}
for($id=300;$id<900;$id++){ CCrmDeal::$rows[]=['ID'=>$id,'LEAD_ID'=>3,'CATEGORY_ID'=>0]; }
$stats=dashboardReadLeads(new DateTimeImmutable('2026-10-01'),new DateTimeImmutable('2026-10-16'));
checkLead($stats['directions']['sales']===['total'=>526,'qualified'=>1,'failed'=>1,'open'=>521,'unconfirmed'=>3], 'Sales cohort, unique converted lead, mismatched categories');
checkLead($stats['directions']['service']===['total'=>4,'qualified'=>1,'failed'=>1,'open'=>1,'unconfirmed'=>1], 'Service cohort separated from sales');
checkLead($stats['unassigned']===2 && CCrmLead::$queries===2, 'Missing/ambiguous reasons excluded and lead cursor advances');
$USER_FIELD_MANAGER->ambiguous=true;
$rejected=false;
try { dashboardLeadReasonField(); } catch (DomainException $error) { $rejected=true; }
checkLead($rejected, 'Ambiguous field configuration never guessed');
echo "PASS: date bounds, leap days, invalid inputs; lead reasons; 500-row batches; one lead counted once; matching category; pending/rejected/unconfirmed separated; permissions; late conversion. Mock Bitrix.\n";

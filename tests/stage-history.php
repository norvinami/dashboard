<?php
namespace {
    if (PHP_SAPI !== 'cli') { http_response_code(403); exit; }
    define('BAVARIA_DASHBOARD_ALLOWED', true);
    function checkHistory($condition, $message) { if (!$condition) { throw new \RuntimeException($message); } }
    final class StageHistoryResult {
        private array $rows;
        public function __construct(array $rows) { $this->rows = $rows; }
        public function fetch() { return array_shift($this->rows) ?: false; }
    }
}
namespace Bitrix\Crm\History\Entity {
    final class DealStageHistoryTable {
        public static array $rows = [];
        public static array $queries = [];
        public static bool $invalidOwner = false;
        public static bool $missingDateField = false;
        public static function getEntity() {
            return new class {
                public function getFields() {
                    $fields = array_fill_keys(['ID','OWNER_ID','OWNER_TYPE_ID','CATEGORY_ID','STAGE_ID'], new \stdClass());
                    if (!DealStageHistoryTable::$missingDateField) { $fields['CREATED_TIME'] = new \stdClass(); }
                    return $fields;
                }
            };
        }
        public static function getList($params) {
            self::$queries[] = $params;
            \checkHistory(count($params['filter']['@OWNER_ID']) <= 500, 'Bounded authorized ID batch');
            \checkHistory($params['filter']['=OWNER_TYPE_ID'] === 2, 'Deal entity type constrained');
            $rows = array_values(array_filter(self::$rows, static fn($r) => in_array($r['OWNER_ID'], $params['filter']['@OWNER_ID'], true)));
            usort($rows, static fn($a,$b) => [$a['CREATED_TIME'],$a['ID']] <=> [$b['CREATED_TIME'],$b['ID']]);
            if (self::$invalidOwner) { $rows[] = ['ID'=>99999,'OWNER_ID'=>99999,'CATEGORY_ID'=>0,'STAGE_ID'=>'NEW','CREATED_TIME'=>'2026-10-01']; }
            return new \StageHistoryResult($rows);
        }
    }
}
namespace {
    require dirname(__DIR__).'/stage-history.php';
    use Bitrix\Crm\History\Entity\DealStageHistoryTable as History;
    foreach ([[1,1,'NEW',1,0],[2,1,'1',2,0],[3,1,'NEW',3,0],[4,1,'1',4,0],[5,1,'WON',5,0],[6,2,'1',1,0],[7,2,'NEW',2,0],[8,3,'1',1,0],[9,3,'NEW',2,0],[10,3,'1',3,0],[11,4,'NEW',1,7],[12,999,'NEW',1,0]] as [$id,$owner,$stage,$time,$category]) {
        History::$rows[] = ['ID'=>$id,'OWNER_ID'=>$owner,'CATEGORY_ID'=>$category,'STAGE_ID'=>$stage,'CREATED_TIME'=>'2026-10-01 00:00:0'.$time];
    }
    $authorized=[];
    for($id=1;$id<=501;$id++){ $authorized[]=['id'=>$id,'category'=>0]; }
    $history=dashboardDealHistory($authorized);
    checkHistory(count(History::$queries)===2, 'Authorized owner batches');
    checkHistory(count($history)===3 && !isset($history[4],$history[999]), 'No foreign pipeline or unauthorized owner data');
    checkHistory($history[1]['NEW']['first']<$history[1]['1']['last'], 'Forward visits and returns retained');
    checkHistory($history[2]['NEW']['first']>$history[2]['1']['last'], 'Earlier target stage is not a forward conversion');
    checkHistory($history[3]['NEW']['first']<$history[3]['1']['last'], 'Later repeat visit can complete conversion');
    checkHistory(dashboardDealHistory([])===[], 'Empty authorized cohort');
    History::$invalidOwner=true;
    $rejected=false;
    try { dashboardDealHistory([['id'=>1,'category'=>0]]); } catch (\RuntimeException $error) { $rejected=true; }
    checkHistory($rejected,'Unexpected owner rejected');
    History::$invalidOwner=false;History::$missingDateField=true;
    $rejected=false;
    try { dashboardDealHistory([['id'=>1,'category'=>0]]); } catch (\DomainException $error) { $rejected=true; }
    checkHistory($rejected,'Unsupported schema is not silently interpreted');
    echo "PASS: stage history scoped to authorized deals and current categories; chronological visits and loops; empty cohort; unsupported schema; 500-ID batches. Mock Bitrix ORM.\n";
}

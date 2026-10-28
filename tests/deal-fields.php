<?php
if (PHP_SAPI !== 'cli') { http_response_code(403); exit; }
define('BAVARIA_DASHBOARD_ALLOWED',true);
require dirname(__DIR__).'/lead-data.php';
require dirname(__DIR__).'/deal-fields.php';
function verifyField($condition,$message) { if (!$condition) { throw new RuntimeException($message); } }
$USER_FIELD_MANAGER=new class {
    public bool $duplicate=false;
    public function GetUserFields($entity,$id,$lang) {
        verifyField($entity==='CRM_DEAL'&&$lang==='ru','Deal metadata scope');
        $fields=[];
        foreach ([[1,'UF_MODEL','Модель','enumeration'],[2,'UF_REASON','Причина обращения','string'],[3,'UF_CANCEL','Запись не состоялась по причине','string'],[4,'UF_STATE','Статус Сделки','enumeration']] as [$id,$code,$label,$type]) {
            $fields[]=['ID'=>$id,'FIELD_NAME'=>$code,'EDIT_FORM_LABEL'=>$label,'USER_TYPE_ID'=>$type];
        }
        if ($this->duplicate) { $fields[]=$fields[0]; }
        return $fields;
    }
};
class CUserFieldEnum {
    public static bool $missing=false;
    public static function GetList($order,$filter) {
        $rows=$filter['USER_FIELD_ID']===1?[['ID'=>11,'VALUE'=>'BMW X5']]:[['ID'=>41,'VALUE'=>self::$missing?'Иное':'Отложенные'],['ID'=>42,'VALUE'=>'Активные']];
        return new class($rows) { public function __construct(private array $rows) {} public function Fetch(){return array_shift($this->rows)?:false;} };
    }
}
$fields=dashboardDealFields();
verifyField(dashboardDealFieldValue(['UF_MODEL'=>11],$fields['model'])==='BMW X5','Enum is decoded');
verifyField(dashboardDealFieldValue(['UF_REASON'=>'<причина>'],$fields['reason'])==='<причина>','Raw data left to UI escaping');
verifyField(dashboardDealFieldValue([],$fields['cancelReason'])==='—','Empty field is not invented');
verifyField(dashboardDealIsDeferred(['UF_STATE'=>41],$fields['deferred'])===true,'Explicit field value counts');
verifyField(dashboardDealIsDeferred(['UF_STATE'=>[42,41]],$fields['deferred'])===true,'Multiple enum values count once');
verifyField(dashboardDealIsDeferred(['UF_STATE'=>42,'STAGE_ID'=>'12'],$fields['deferred'])===false,'Stage never substituted for field');
$USER_FIELD_MANAGER->duplicate=true;
verifyField(isset(dashboardDealFields()['model']['error']),'Duplicate field not guessed');
CUserFieldEnum::$missing=true;
verifyField(dashboardDealIsDeferred([],dashboardDealFields()['deferred'])===null,'Missing option is unknown not false');
echo "PASS: deal field metadata, enum decoding, missing/ambiguous fields, explicit deferred option, no stage substitution. Mock Bitrix.\n";

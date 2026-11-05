<?php
// Test-only Bitrix replacement, never deployed. Run through PHP Web SAPI fixture runner.
namespace { if (!defined('BAVARIA_TEST_BITRIX')) { http_response_code(403); exit; } }
namespace Bitrix\Main { class Loader { static function includeModule($name){return true;} } }
namespace Bitrix\Crm\Category {
    class DealCategory {
        static function getAll($all){return [['ID'=>7,'NAME'=>'Сервис']];}
        static function getStageList($id){return $id===0?['NEW'=>'Новая сделка','WON'=>'Успешно','LOSE'=>'Провал']:['C7:NEW'=>'Заявка','C7:WON'=>'Запись','C7:LOSE'=>'Провал'];}
    }
}
namespace Bitrix\Crm {
    class ActivityBindingTable {static function getList($args){return new class{function fetch(){return false;}};}}
}
namespace {
    $testViewer=(int)($_GET['viewer']??405);
    $_SESSION=['SESS_AUTH'=>['USER_ID'=>$testViewer,'GROUPS'=>[2]],'testToken'=>'unchanged'];
    $_COOKIE=['testSession'=>'original'];$testReads=[];
    class SharedApiUser {
        function GetID(){return (int)($_GET['viewer']??405);}
        function IsAuthorized(){return empty($_GET['anonymous']);}
        function Authorize(){throw new \RuntimeException('Never authorize as owner');}
    }
    $USER=new SharedApiUser();
    register_shutdown_function(static function(){global $USER,$testReads;file_put_contents('/audit.json',json_encode(['viewer'=>$USER->GetID(),'session'=>$_SESSION,'cookies'=>$_COOKIE,'reads'=>$testReads]));});
    class SharedApiRows {
        function __construct(private array $rows){}
        function Fetch(){return array_shift($this->rows)?:false;}
    }
    class CUser {
        static function GetByID($id){return new SharedApiRows([['ID'=>$id,'ACTIVE'=>$id===5&&!empty($_GET['disabledOwner'])?'N':'Y','LAST_NAME'=>$id===5?'Шлегель':'Сотрудник','NAME'=>$id===5?'Оксана':'405','WORK_POSITION'=>'']]);}
    }
    class CCrmPerms {
        function __construct(private int $id){}
        static function GetUserPermissions($id){return new self($id);}
        function GetUserID(){return $this->id;}
    }
    function sharedApiRead($kind,$filter,$options){
        global $testReads;
        if(($filter['CHECK_PERMISSIONS']??'')!=='Y'||!isset($options['PERMS'])||$options['PERMS']->GetUserID()!==5){throw new \RuntimeException('Wrong shared permission source for '.$kind);}
        $testReads[]=$kind.':5';
    }
    function MakeTimeStamp($date){return strtotime($date);}
    class CCrmDeal {
        static function GetListEx($order,$filter,$group,$nav,$select,$options=[]){
            sharedApiRead('deal',$filter,$options);
            if(($filter['>ID']??0)>=20||(isset($filter['CATEGORY_ID'])&&!in_array(0,(array)$filter['CATEGORY_ID'],true))){return new SharedApiRows([]);}
            return new SharedApiRows([['ID'=>20,'TITLE'=>'Owner-only deal','LEAD_ID'=>10,'CATEGORY_ID'=>0,'STAGE_ID'=>'NEW','STAGE_SEMANTIC_ID'=>'P','SOURCE_ID'=>'WEB','ASSIGNED_BY_ID'=>5,'DATE_CREATE'=>'02.11.2026 12:00:00','OPPORTUNITY'=>100,'CURRENCY_ID'=>'KZT','UF_MODEL'=>'BMW','UF_CANCEL'=>'','UF_STATE'=>201]]);
        }
    }
    class CCrmLead {
        static function GetListEx($order,$filter,$group,$nav,$select,$options=[]){
            sharedApiRead('lead',$filter,$options);
            return new SharedApiRows(($filter['>ID']??0)>=10?[]:[['ID'=>10,'UF_REASON'=>101,'STATUS_SEMANTIC_ID'=>'S','DATE_CREATE'=>'02.11.2026 12:00:00']]);
        }
    }
    class CCrmActivity {
        static function GetList($order,$filter,$group,$nav,$select,$options=[]){sharedApiRead('activity',$filter,$options);return new SharedApiRows([]);}
    }
    class CCrmStatus {
        static function GetList($order,$filter){return new SharedApiRows($filter['ENTITY_ID']==='SOURCE'?[['STATUS_ID'=>'WEB','NAME'=>'Сайт']]:[['STATUS_ID'=>'NEW','NAME'=>'Новая','SEMANTICS'=>'P'],['STATUS_ID'=>'WON','NAME'=>'Успешно','SEMANTICS'=>'S'],['STATUS_ID'=>'LOSE','NAME'=>'Провал','SEMANTICS'=>'F']]);}
    }
    class CTasks {
        static function GetList($order,$filter,$select,$params){
            global $testReads;
            if(($params['USER_ID']??0)!==5||$params['CHECK_PERMISSIONS']!=='Y'||$filter['CHECK_PERMISSIONS']!=='Y'){throw new \RuntimeException('Wrong task source');}
            $testReads[]='task:5';
            return new SharedApiRows($filter['>ID']>=30?[]:[['ID'=>30,'TITLE'=>'Owner-only task','RESPONSIBLE_ID'=>5,'DEADLINE'=>'','STATUS'=>2]]);
        }
    }
    $USER_FIELD_MANAGER=new class{
        function GetUserFields($entity,$id,$lang){
            if($entity==='CRM_LEAD'){return [['ID'=>12,'FIELD_NAME'=>'UF_REASON','EDIT_FORM_LABEL'=>'Причина обращения','USER_TYPE_ID'=>'enumeration']];}
            return [['ID'=>20,'FIELD_NAME'=>'UF_MODEL','EDIT_FORM_LABEL'=>'Марка авто сервис','USER_TYPE_ID'=>'string'],['ID'=>21,'FIELD_NAME'=>'UF_CANCEL','EDIT_FORM_LABEL'=>'Запись на сервис не состоялась по причине','USER_TYPE_ID'=>'string'],['ID'=>22,'FIELD_NAME'=>'UF_STATE','EDIT_FORM_LABEL'=>'СТАТУС СДЕЛКИ','USER_TYPE_ID'=>'enumeration']];
        }
    };
    class CUserFieldEnum {
        static function GetList($order,$filter){return new SharedApiRows($filter['USER_FIELD_ID']===12?[['ID'=>101,'VALUE'=>'Покупка нового автомобиля'],['ID'=>102,'VALUE'=>'Сервис']]:[['ID'=>201,'VALUE'=>'Отложил покупку']]);}
    }
}

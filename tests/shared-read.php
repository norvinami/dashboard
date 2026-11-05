<?php
if (PHP_SAPI!=='cli') { http_response_code(403); exit; }
define('BAVARIA_DASHBOARD_ALLOWED',true);
require __DIR__.'/crm-read-fixture.php';
require dirname(__DIR__).'/read-access.php';
class SharedUser {
    public function __construct(private int $id,private bool $authorized=true) {}
    public function GetID(){return $this->id;}
    public function IsAuthorized(){return $this->authorized;}
    public function Authorize(){throw new RuntimeException('Must never log in as source');}
}
class CUser {
    public static array $rows=[5=>['ACTIVE'=>'Y','LAST_NAME'=>'Шлегель','NAME'=>'Оксана'],405=>['ACTIVE'=>'Y','LAST_NAME'=>'Сотрудник','NAME'=>'405']];
    public static function GetByID($id){$row=self::$rows[$id]??false;return new class($row){public function __construct(private $row){}public function Fetch(){return $this->row;}};}
}
function sharedCheck($condition,$message){if(!$condition){throw new RuntimeException($message);}}
$_SESSION=['SESS_AUTH'=>['USER_ID'=>405,'GROUPS'=>[2,7]],'token'=>'keep'];$_COOKIE=['PHPSESSID'=>'viewer-session'];
$_GET=['dataUserId'=>'1','USER_ID'=>'1','viewer'=>'5'];
$session=$_SESSION;$cookies=$_COOKIE;
$USER=new SharedUser(405);$viewer=$USER;
$shared=dashboardDataIdentity();
sharedCheck($shared['currentUserId']===405&&$shared['dataUserId']===5&&$shared['sharedReadOnly']===true,'Viewer and source distinct');
sharedCheck($shared['viewerName']==='Сотрудник 405'&&$shared['dataUserName']==='Шлегель Оксана','Separate profile and source names');
verifyDashboardReadOptions(dashboardCrmReadOptions());
sharedCheck($USER===$viewer&&$_SESSION===$session&&$_COOKIE===$cookies,'Identity, session and cookies never changed');
// Native caller rights can be empty; fixed owner options must still yield the owner's data.
class SharedResult { public function __construct(private array $rows){} public function Fetch(){return array_shift($this->rows)?:false;} }
$USER_FIELD_MANAGER=new class {function GetUserFields($entity,$id,$lang){return [['ID'=>12,'FIELD_NAME'=>'UF_REASON','USER_TYPE_ID'=>'enumeration','EDIT_FORM_LABEL'=>'Причина обращения']];}};
class CUserFieldEnum {static function GetList($order,$filter){return new SharedResult([['ID'=>101,'VALUE'=>'Покупка нового автомобиля'],['ID'=>102,'VALUE'=>'Сервис']]);}}
class CCrmLead {
    static function GetListEx($order,$filter,$group,$nav,$select,$options=[]){
        global $USER;
        $permissionId=isset($options['PERMS'])?$options['PERMS']->GetUserID():$USER->GetID();
        if($permissionId!==5){return new SharedResult([]);}
        sharedCheck($filter['CHECK_PERMISSIONS']==='Y','Lead read check retained');
        return new SharedResult(($filter['>ID']??0)<10?[['ID'=>10,'UF_REASON'=>101,'STATUS_SEMANTIC_ID'=>'S']]:[]);
    }
}
class CCrmDeal {
    static function GetListEx($order,$filter,$group,$nav,$select,$options=[]){
        verifyDashboardReadOptions($options);sharedCheck($filter['CHECK_PERMISSIONS']==='Y','Deal read check retained');
        return new SharedResult($filter['>ID']<20?[['ID'=>20,'LEAD_ID'=>10,'CATEGORY_ID'=>0]]:[]);
    }
}
require dirname(__DIR__).'/lead-data.php';
$from=new DateTimeImmutable('2026-11-01');$to=new DateTimeImmutable('2026-11-05');
$sharedLeads=dashboardReadLeads($from,$to);
sharedCheck($sharedLeads['directions']['reason_101']['qualified']===1,'405 receives converted owner lead despite no own lead access');
$USER=new SharedUser(5);sharedCheck(dashboardReadLeads($from,$to)===$sharedLeads,'Both viewers receive identical lead cohort');$USER=$viewer;
sharedCheck($USER===$viewer&&$_SESSION===$session&&$_COOKIE===$cookies,'Real reader calls do not change viewer session');
$USER=new SharedUser(5);$own=dashboardDataIdentity();
sharedCheck($own['dataUserId']===$shared['dataUserId']&&!$own['sharedReadOnly'],'Both viewers read same fixed source');
foreach ([new SharedUser(406),new SharedUser(1),new SharedUser(405,false)] as $invalid){
    $USER=$invalid;$denied=false;try{dashboardDataIdentity();}catch(DomainException $e){$denied=true;}sharedCheck($denied,'No additional viewer allowed');
}
$USER=new SharedUser(405);CUser::$rows[5]['ACTIVE']='N';
$denied=false;try{dashboardDataIdentity();}catch(DomainException $e){$denied=true;}sharedCheck($denied,'Disabled owner fails closed');
unset(CUser::$rows[5]);$denied=false;try{dashboardDataIdentity();}catch(DomainException $e){$denied=true;}sharedCheck($denied,'Missing owner fails closed');
CCrmPerms::$override=1;$denied=false;try{dashboardCrmReadOptions();}catch(DomainException $e){$denied=true;}sharedCheck($denied,'Incorrect permission identity fails closed');
sharedCheck($_SESSION===$session&&$_COOKIE===$cookies,'Errors do not affect viewer session');
echo "PASS: shared viewer 405 and owner 5, both same fixed read identity, no request overrides, viewer profile retained, no login/session/cookie changes, forbidden viewers, disabled/missing owner and bad permission object fail closed. Mock Bitrix.\n";

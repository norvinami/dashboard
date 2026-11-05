<?php
if (!defined('BAVARIA_DASHBOARD_ALLOWED')) { http_response_code(403); exit; }
/** Fixed, server-owned read source. Never accept this ID from a request. */
function dashboardDataUserId(): int { return 5; }
/** Explicit CRM read permissions; never impersonate a login or disable permission checks. */
function dashboardCrmReadOptions(): array {
    if (!is_callable(['CCrmPerms','GetUserPermissions'])) { throw new DomainException('Не удалось подключить права источника данных дашборда.'); }
    $permissions=CCrmPerms::GetUserPermissions(dashboardDataUserId());
    if (!is_object($permissions)||!is_callable([$permissions,'GetUserID'])||(int)$permissions->GetUserID()!==dashboardDataUserId()) {
        throw new DomainException('Не удалось определить права источника данных дашборда.');
    }
    return ['PERMS'=>$permissions];
}
function dashboardDataIdentity(): array {
    global $USER;
    $viewer=is_object($USER)&&(bool)$USER->IsAuthorized()?(int)$USER->GetID():0;
    if (!in_array($viewer,[5,405],true)) { throw new DomainException('У вас нет доступа к дашборду.'); }
    $owner=CUser::GetByID(dashboardDataUserId())->Fetch();
    if (!$owner||($owner['ACTIVE']??'')!=='Y') { throw new DomainException('Источник данных дашборда (пользователь 5) недоступен.'); }
    $profile=$viewer===dashboardDataUserId()?$owner:CUser::GetByID($viewer)->Fetch();
    $name=$profile?trim(($profile['LAST_NAME']??'').' '.($profile['NAME']??'')):'';
    $ownerName=trim(($owner['LAST_NAME']??'').' '.($owner['NAME']??''));
    return ['currentUserId'=>$viewer,'viewerName'=>$name?:'Сотрудник '.$viewer,
        'dataUserId'=>dashboardDataUserId(),'dataUserName'=>$ownerName?:'Оксана Шлегель',
        'sharedReadOnly'=>$viewer!==dashboardDataUserId()];
}
